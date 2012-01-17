#!/usr/bin/env ruby 

require "rubygems"
require "watir-webdriver"

def handle_page(html)
  # scan the result for links to media assets
  links = html.scan(/\"AssetDetail\.aspx\?g=(.*?)\"/)
  
  links.each do |link|
    if link[0] == @last_recorded
      puts "we have seen #{link} already, stopping here ..."
      return FALSE
    end
    @new_asset_file.puts link
  end
  
  return TRUE
  
end

asset_types = {
  "video" => "ctl00_PageContent_chkVideo" ,
  "audio" => "ctl00_PageContent_chkAudio" , 
  "photo" => "ctl00_PageContent_chkPhoto"
}

unless (ARGV.count == 1)
  abort("You need to specify an asset type (#{asset_types.keys.join(", ")})!")
end

type = ARGV[0]
unless (asset_types.keys.index(type))
  abort("Illegal asset type. Allowed are: #{asset_types.keys.join(", ")}")
end

# find the latest asset_file and get the id in its first line:
asset_files = []
Dir.entries("intermediate/#{type}").each do |entry|
  if (asset_file = entry.match(/media_assets_(\d{4})-(\d{2})-(\d{2})/))
    asset_files << entry unless "#{asset_file[1]}-#{asset_file[2]}-#{asset_file[3]}" == Date.today.iso8601
  end
end

if (asset_files.count > 0)
  asset_files.sort! {|x,y| y <=> x}
  @last_recorded = `head -1 intermediate/#{type}/#{asset_files[0]}`.strip
  puts "lastest asset file: #{asset_files[0]} ..."
else
  puts "no previous asset file found"
  @last_recorded = nil
end
puts "last recorded asset: #{@last_recorded} ..."

@new_asset_file = open("intermediate/#{type}/media_assets_#{Date.today.iso8601}.txt", 'w')

# get a browser instance
puts "Starting browser..."
http = Selenium::WebDriver::Remote::Http::Default.new
http.timeout = 360  # trying to prevent annyoing time-out error
b = Watir::Browser.new(:chrome, :http_client => http)

# go to the bloody site
puts "Opening page..."
b.goto 'http://audiovisual.europarl.europa.eu/Search.aspx'

# specify a search parameter. We want everything, so "*"
puts "setting search string..."
b.text_field(:id => 'ctl00_PageContent_txtsearchfield').set("*")

# we want only videos, so check the corresponding checkbox
puts "restrict search to #{type} files..."
b.checkbox(:id => asset_types[type]).set(TRUE)

# click "search"
begin 
  puts "start search..."
  b.a(:class => 'pagesearch').click

  rescue Exception => e
    puts "Exception!"
end


# how many pages do we have?
num_pages = b.span(:id => 'ctl00_PageContent_lblNumberOfPages').text.to_i

(1..num_pages).each do |index|
  # select a page
  puts "select next page: #{index}"
  b.select_list(:id => 'ctl00_PageContent_ddlPages').select_value(index.to_s)
  break unless handle_page(b.html)
end

@new_asset_file.close

# puts "concatenating asset files..."
# # finally concatenate the new assets with the old:
# `cat intermediate/new_media_assets.tmp intermediate/media_assets.txt > intermediate/out.tmp ; mv intermediate/out.tmp intermediate/media_assets.txt`
# 
