#!/usr/bin/env ruby 

# == Synopsis
#   This is a sample description of the application.
#   Blah blah blah.
#
# == Examples
#   This command does blah blah blah.
#     ruby run_scraper.rb 
#
#   Other examples:
#     ruby run_scraper.rb -q 
#     ruby run_scraper.rb --verbose 
#
# == Usage 
#   ruby run_scraper.rb [options] $output.ttl
#
#   For help use: ruby run_scraper.rb -h
#
# == Options
#   -h, --help          Displays help message
#   -v, --version       Display the version, then exit
#   -q, --quiet         Output as little as possible, overrides verbose
#   -V, --verbose       Verbose output
#   TO DO - add additional options
#
# == Author
#   Knud Möller
#
# == Copyright
#   Copyright (c) 2011 Knud Möller, DERI. 


require 'rubygems'
require 'optparse' 
require 'ostruct'
require 'date'
require './euMediaScraper'
require './csv2rdf'

class App
  VERSION = '0.0.1'
  
  attr_reader :options

  def initialize(arguments, stdin)
    @arguments = arguments
    @stdin = stdin
    

    # Set defaults
    @options = OpenStruct.new
    @options.verbose = false
    @options.quiet = false
    # TO DO - add additional defaults
    @options.date = Date.today.iso8601
    @options.out_folder = "intermediate"
    @options.type = "video"
  end

  # Parse options, check arguments, then process the command
  def run
                
    if parsed_options? && arguments_valid? 
      
      puts "Start at #{DateTime.now}\n\n" if @options.verbose
      
      output_options if @options.verbose # [Optional]
            
      process_arguments            
      process_command
      
      puts "\nFinished at #{DateTime.now}" if @options.verbose
      
    else
      output_usage
    end
      
  end
  
  protected
  
    def parsed_options?
      
      # Specify options
      opts = OptionParser.new 
      opts.on('-v', '--version')        { output_version ; exit 0 }
      opts.on('-h', '--help')           { output_help }
      opts.on('-V', '--verbose')        { @options.verbose = true }  
      opts.on('-q', '--quiet')          { @options.quiet = true }
      
      # TO DO - add additional options
      opts.on('-o', '--outfolder [OUTFOLDER]') do |folder|
        @options.out_folder = folder
      end
            
      opts.on('-d', '--date [DATE]') do |date|
        @options.date = date
      end
      
      opts.on('-t', '--type [TYPE]') do |type|
        @options.type = type
      end
      
      opts.parse!(@arguments) rescue return false
      
      process_options
      true      
    end

    # Performs post-parse processing on options
    def process_options
      @options.verbose = false if @options.quiet
    end
    
    def output_options
      puts "Options:\n"
      
      @options.marshal_dump.each do |name, val|        
        puts "  #{name} = #{val}"
      end
    end

    # True if required arguments were provided
    def arguments_valid?
      # TO DO - implement your real logic here
      # return @arguments.count == 0
      return TRUE
    end
    
    # Setup the arguments
    def process_arguments
      # @ttl_output = File.open(@arguments[0], "w")
    end
    
    def output_help
      output_version
      # RDoc::usage() #exits app
    end
    
    def output_usage
      # RDoc::usage('usage') # gets usage from comments above
    end
    
    def output_version
      puts "#{File.basename(__FILE__)} version #{VERSION}"
    end
    
    def process_command
      asset_file = "#{@options.out_folder}/#{@options.type}/media_assets_#{@options.date}.txt"
      asset_csv_file = "#{@options.out_folder}/#{@options.type}/csv/#{@options.type}_assets_#{@options.date}.csv"
      topic_csv_file = "#{@options.out_folder}/#{@options.type}/csv/#{@options.type}_topics_#{@options.date}.csv"
      # scraper = EUMediaScraper.new(asset_file, asset_csv_file, topic_csv_file, @options.type)
      # scraper.run

      rdf_output_folder = "output/#{@options.date}"
      if (!Dir.exists?(rdf_output_folder))
        Dir.mkdir(rdf_output_folder)
      end
      rdf_output_file = "#{rdf_output_folder}/eup_media_assets_#{@options.type}_#{@options.date}.nt"
      converter = EUMediaCSV2RDF.new(rdf_output_file, asset_csv_file, topic_csv_file, @options.type)
      converter.run
    end

    def process_standard_input
      input = @stdin.read      
      # TO DO - process input
      
      # [Optional]
      # @stdin.each do |line| 
      #  # TO DO - process each line
      #end
    end
end


# TO DO - Add your Modules, Classes, etc


# Create and run the application
app = App.new(ARGV, STDIN)
app.run