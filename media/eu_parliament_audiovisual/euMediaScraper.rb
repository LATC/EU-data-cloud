#!/usr/bin/env ruby 

require 'rubygems'
require 'nokogiri'
require 'pp'
require 'open-uri'
require 'uri'
require 'cgi'
require 'date'
require 'csv'


# in videos

CLASSIFICATION = "Classifications"
DATE = "Event Date"
DURATION = "Duration"
REFERENCE = "Reference"
DESCRIPTION = "Background"
PROD_FORMAT = "Production format"
FORMAT = "Format"
COPYRIGHT = "Copyright"
FILENAME = "Filename"

# in audio

FILETYPE = "File Type"
BITRATE = "Bit Rate"
SHORT_DESC = "Short description"

# in photos

# CLASSIFICATION
# DATE
CAPTION = "Caption"
HEADLINE = "Headline"
IPTC_CR = "IPTC Copyright"
USAGE_TERMS = "Right Usage Terms"
# REFERENCE
SIZE = "Size"
COUNTRY_CODE = "ISO Country Code"
CITY = "City"
COUNTRY = "Country"
STATE = "State / Province"
RESOLUTION = "Resolution"
# Filename

class EUMediaScraper
  
  attr_reader :media_objects
  
  def initialize(asset_file_name, asset_csv_file_name, topic_csv_file_name, type)
    @asset_file_name = asset_file_name
    @asset_csv_file_name = asset_csv_file_name
    @topic_csv_file_name = topic_csv_file_name
    @type = type
  end
  
  def run
    
    media_class = TYPES[@type]

    asset_csv = CSV.open(@asset_csv_file_name, "w")
    topic_csv = CSV.open(@topic_csv_file_name, "w")
    asset_csv << media_class.header
    topic_csv << [ "uri", "classification"]
  
    asset_ids = []
    open(@asset_file_name, "r").each do |line| asset_ids << line end
    asset_count = %x{wc -l "#{@asset_file_name}"}.to_i
    count = 1
    max = asset_count-1
    # max = 35
    asset_ids = asset_ids[count-1..max]

    asset_ids.each do |asset_id|
      asset_page = "http://audiovisual.europarl.europa.eu/AssetDetail.aspx?g=#{asset_id.strip}"
      puts "scraping #{asset_page} (#{count} of #{asset_count})..."
      if (asset = media_class.check_and_create(asset_page))
        # need to force UTF-8 here because I would sometimes get incompatible encoding errors 
        # asset_csv << asset.to_a.map do |x| (x ? x.force_encoding("UTF-8") : nil) end
        asset_csv << asset.to_a
        if (asset.classifications)
          asset.classifications.each do |classification|
            topic_csv << [asset.uri, classification[0], classification[1]]
          end
        end
      end
      count += 1
    end
  
    asset_csv.close
    topic_csv.close
  
  end # run()
  
end # class EUMediaScraper

class MediaObject

  attr_reader :uri, :title , :classifications

  def initialize(uri)
    @uri = uri
    @doc = Nokogiri::HTML(open(@uri))
    scrape_object
  end

  # will return nil if the page seems to be empty (no <div id="ctl00_PageContent_assetPlayer_AssetDetailView">)
  def scrape_object
    
    if (@doc.search("//div[@id='ctl00_PageContent_assetPlayer_AssetDetailView']").count == 0)
      puts "This page seems to be empty!"
      return nil
    end
    
    # get the title

    @title = @doc.xpath('//div[contains(@class, "roundedheader") and contains(@align, "center")]').first.inner_text
    
    # get the keys from the table
    entries = @doc.search("td[@class='spec']")
    entries.each do |key_node| 
      key = key_node.inner_text.strip
      # the values are the next <td> element
      value_node = key_node.next_element
      value = value_node.inner_text.strip
      
      case key
        when CLASSIFICATION
          classifications = []
          categories = value_node.search("a")
          categories.each do |category|
            link = "http://audiovisual.europarl.europa.eu#{category['href']}"
            label = category.inner_text.gsub("\n", ' ').squeeze(' ').force_encoding("UTF-8")
            classifications << [link, label]
          end
          # @classifications = Marshal.dump(classifications)
          @classifications = classifications
        when DATE 
          @event_date = Date._parse(value)
        when DURATION
          @duration = value
        when REFERENCE
          @reference = value
        when DESCRIPTION
          @description = value
        when CAPTION
          @description = value
        when HEADLINE
          @headline = value
        when PROD_FORMAT
          @production_format = value
        when FORMAT
          @format = value
        when COPYRIGHT
          @copyright = value
        when IPTC_CR
          @copyright = value
        when USAGE_TERMS
          @usage_terms = value
        when FILENAME
          @filename = value
        when SIZE
          @size = value
        when RESOLUTION
          @resolution = value
        when COUNTRY_CODE
          @country_code = value
        when COUNTRY
          @country = value
        when CITY
          @city = value
        when STATE
          @state = value
        when FILETYPE
          @file_type = value
        when BITRATE
          @bit_rate = value
        when SHORT_DESC
          @description = value
      end

    end

  end
  
  def convert_date
    @event_date ? date_string = "#{@event_date[:year]}-#{@event_date[:mon].to_s.rjust(2, "0")}-#{@event_date[:mday].to_s.rjust(2, "0")}" : date_string = nil
    return date_string
  end
  
end

class Video < MediaObject

  # will return nil if the page seems to be empty
  def self.check_and_create(uri)
    object = Video.new(uri)
    if (object.title)
      return object
    else
      return nil
    end
  end

  def to_a
    date_string = convert_date
    return [@uri, 
      @title, 
      date_string, 
      @duration, 
      @reference, 
      @description, 
      @production_format, 
      @format, 
      @copyright, 
      @filename]
  end

  def self.header
    [ "uri", 
      "title", 
      "event_date", 
      "duration", 
      "reference", 
      "description", 
      "prod_format", 
      "format", 
      "copyright", 
      "filename" ]
  end


end

class Audio < MediaObject

  # will return nil if the page seems to be empty
  def self.check_and_create(uri)
    object = Audio.new(uri)
    if (object.title)
      return object
    else
      return nil
    end
  end

  def to_a
    date_string = convert_date
    return [@uri, 
      @title, 
      date_string, 
      @duration, 
      @description, 
      @file_type,
      @bit_rate,
      @filename]
  end

  def self.header
    [ "uri", 
      "title", 
      "event_date", 
      "duration", 
      "description", 
      "file_type", 
      "bit_rate", 
      "filename" ]
  end

end

class Image < MediaObject

  # will return nil if the page seems to be empty
  def self.check_and_create(uri)
    object = Image.new(uri)
    if (object.title)
      return object
    else
      return nil
    end
  end

  def to_a
    date_string = convert_date
    return [@uri, 
      @title, 
      @image,
      date_string, 
      @size, 
      @resolution, 
      @reference, 
      @description, 
      @headline, 
      @copyright, 
      @usage_terms,
      @filename,
      @country_code,
      @country,
      @state,
      @city ]
  end

  def self.header
    [ "uri", 
      "title", 
      "thumb", 
      "event_date", 
      "size", 
      "resolution", 
      "reference", 
      "description", 
      "headline", 
      "copyright", 
      "usage_terms", 
      "filename", 
      "country_code", 
      "country",
      "state", 
      "city" ]
  end

  def scrape_object

    # get the image
    if (super)
      img_src = @doc.xpath("//span[@id='ctl00_PageContent_assetPlayer_AssetDetailView_AssetPreview_ctl01_imgThumb']").children[0]["src"]
      @image = "http://audiovisual.europarl.europa.eu#{img_src}"
    else
      return nil
    end
  end
  
end

TYPES = {
  "video" => Video ,
  "audio" => Audio ,
  "photo" => Image
}


# MediaObject: 
# 
# Video: :uri, :title, :classifications, :event_date, :reference, :filename, :copyright, :duration, :description, :production_format, :format
# Audio: :uri, :title, :classifications, :event_date, :reference, :filename,             :duration, :description, :bit_rate, :file_type
# Image: :uri, :title, :classifications, :event_date, :reference, :filename, :copyright, :size, :resolution, :country, :province, :city, :caption

# <span id="ctl00_PageContent_assetPlayer_AssetDetailView_AssetPreview_ctl01_imgThumb">
# <img height="470" width="312" src="/Resource.axd?9q3JY2Pnl6+DUMx5CudVIw7RQep7iy3HDQeHsMUzenfwzUjrM01SUQJGuGQ0iM69lT/ZzCou2Doyhs7//P77GBCWFvfAZBtYPdgWQFZSpgOoNYgNl2w7VMU4au4Y5GMBF0Kpmi62CCmYun9Cs4nch1dUQW0Mw8s1NCkRTzZjsc0IBWwjAFUBDiDHtWtr40Bt" border="0" style="vertical-align:top;">
# </span>

