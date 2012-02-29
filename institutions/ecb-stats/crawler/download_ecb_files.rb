#!/usr/bin/env ruby
# encoding: UTF-8

require "nokogiri"
require "open-uri"
require "set"
require "pp"
require "csv"

require "ld_slugger"

ECB_BASE = "http://sdw.ecb.europa.eu/"
ECB_START = "browse.do?node=1478"
# there are some filter values which we should exclude, because they are just 
# multiple selections of other filters. We won't get any new series for them.
# instead, we will again get way too many series.
FILTER_EXCLUDE = [
  "", # All
  "*EU", # all EU countries
  "*MU", # all MU countries
  "*OM", # all EU excluding MU
]
$max_depth = 0
$multiple_ds = FALSE
TEASER = FALSE

def output_data(data, csv, path="")
  
  if (data.class == Array)
    data.each do |series|
      series.insert(0, path)
      begin
        csv << series
      rescue NoMethodError
        puts "Error - can't append #{series} to csv"
      end
    end
  elsif (data.class == Hash)
    data.keys.each do |key|
      path = "#{path}/#{key}"
      output_data(data[key], csv, path)
    end
  end
  
end

def print_level(level)
  (2..level).each do |index|
    print"  "
  end
end

def get_filters(page)
  filters = []

  filter_elements = page.xpath("//select[starts-with(@id, 'fl')]")
  filter_elements.each do |filter_element| 
    filter = []
    filter_element.xpath("option/@value").each do |value| 
      name = filter_element['name']
      filter << "#{name}=#{value.content}" unless (FILTER_EXCLUDE.include?(value.content))
    end
    filters << filter
  end
  
  return filters
end

def process_page(page_link, level=2, already_filtered=0,dataset="")
  
  series = nil

  node = page_link.match(/node=(.*)/)[1]

  downloading = TRUE
  while (downloading)
    print_level(level)
    puts "loading #{page_link} ..."
    begin
      page = Nokogiri::HTML(open(page_link))
      downloading = FALSE
    rescue SystemCallError, Timeout::Error, SocketError, OpenURI::HTTPError => e
      print_level(level)
      puts "Error: #{e} Trying again..."
    end
  end
  
  series_links = page.xpath("//table[@class='tablestats']//td//a[starts-with(@href, 'quickview')]")
  # <div id="exportOptions" class="exportOptions" ...> <a href="export.do?node=2120778&exportType=sdmx">XML (SDMX-ML)</a> ... </div>
  exports = page.css("div.exportOptions a")
  # there could be multiple datasets
  datasets = page.xpath("//input[@name='DATASET']/@value")
  
  if (!$multiple_ds && datasets.length > 1)
    print_level(level)
    puts "There are multiple datasets!"
    datasets.each do |dataset_id|
      id = dataset_id.content
      print_level(level)
      puts "Doing dataset #{id} ..."
      page_link = "#{ECB_BASE}browse.do?DATASET=#{id}&node=#{node}"
      $multiple_ds = TRUE
      series = process_page(page_link, level, already_filtered, "&DATASET=#{id}")
    end
    $multiple_ds = FALSE
  elsif (page.to_s.match("selection exceeds the maximum"))
    # this page has data series, but too many - we need to restrict the selection somehow
    series = []
    # we iterate through countries and frequencies, hopefully restricing the number of series enough
    filters = get_filters(page)
    
    filters.sort! {|x,y| x.length <=> y.length }
    
    # find the largest filter:
    largest = filters.last
    print_level(level)
    puts "too many data series, applying largest filter (#{largest.length})"
    
    count = already_filtered
    $max_depth = already_filtered
    while (count > 0)
      # we have already filtered this page and there are still too many series.
      # we need to take the second largest filter as well
      filters.pop
      second_largest = filters.last
      print_level(level)
      puts "still too many series, applying next largest filter (#{second_largest.length})"
      filter_product = largest.product(second_largest)
      print_level(level)
      puts "using cartesian product of both filters"      
      largest = filter_product.map { |x| x.join("&")}
      count -= 1
    end
    
    # the following is cool, but gives way to many results. combinatorial explosion...
    # filter_product = filters[0].product(*filters[1..filters.length]) # make the cartesian product of all filter arrays
    
    count = 0
    if TEASER 
      largest = largest[0..2]
    end
    largest.each do |selection|
      print_level(level)
      puts "#{count} of #{largest.length}"
      link = "#{ECB_BASE}browseSelection.do?#{selection}&node=#{node}#{dataset}"
      new_series = process_page(link, level+1, already_filtered+1)
      series.concat(new_series) if new_series
      if $max_depth > already_filtered
        $max_depth -= 1
        break
      end
      # at this point I need to know whether I directly got a set of dataseries, or if I had to apply more filters
      count += 1
    end
    series = series.uniq
  elsif (page.to_s.match(/Selected Series[.\n\s]*?\(0\)/))
    # this is a page with no available series
    # we got here by choosing a combination of filters in the previous set that gives an empty results
    print_level(level)
    puts "nothing here..."
  elsif (series_links.length > 0)
    # we have a number of data series
    series = []
    print_level(level)
    puts "we have #{series_links.length} data series"
    series_links.each do |series_link|
      key = series_link['href'].match(/SERIES_KEY=(.*)/)[1]
      if (key)
        title = series_link.content.strip
        series_columns = series_link.parent.parent.css("td")
        from = series_columns[4].content.strip.to_i
        to = series_columns[5].content.strip.to_i
        series << [key, title, from, to]
      end
    end
  elsif (exports.length > 0)
    # if we don't have data series but an export button, we can download something
    series = []
    exports.each do |link|
      href = link['href']
      if (href.match("exportType=sdmx"))
        print_level(level)
        puts "found #{href}"
        series << [href]
        break
      end
    end
  else
    # in all other cases, there is no data on this page yet, and we need to go a level deeper
    series = {}
    page.css("a.sdw1border.sdwbg#{level}").each do |link| 
      href = link['href']
      # browse.do?node=2120778
      text = link.content.ld_slug
      print_level(level)
      puts "parsing #{href} (#{text})"
      link = "#{ECB_BASE}#{href}"
      series[text] = process_page(link, level+1)
    end
  end

  return series

end

# warehouse start page
# root is at http://sdw.ecb.europa.eu/browse.do?node=1478
ecb_start_page = "http://sdw.ecb.europa.eu/browse.do?node=1478"
if ARGV[0]
  ecb_start_page = ARGV[0]
end
level = 2
if ARGV[1]
  level = ARGV[1]
end
  

# iterate down the hierarchy of "economic concepts"
puts 'iterate down the hierarchy of "economic concepts"'
all_series = process_page(ecb_start_page, level, 0, "")

# puts "there are #{ALL_SERIES.length} series alltogether"
#

out_name = "series_list"
if TEASER
  out_name = "series_list_teaser"
end

puts "Writing into CSV file"
CSV.open("output/" + out_name + ".csv", "wb") do |csv|
  output_data(all_series, csv)
end


