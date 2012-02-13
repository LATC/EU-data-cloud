#!/usr/bin/env ruby

require 'rubygems'
require 'kasabi'

dataset = "latc-eu-media"
apikey = "5c8c1a8ea63cbe2176949dfe223860ad992e7913"
base = "http://data.kasabi.com"

#create our client
dataset = Kasabi::Dataset.new("#{base}/dataset/#{dataset}", {:apikey => apikey})

dataset.client.debug_dev = $stderr
puts "Dataset: #{dataset.endpoint}"
puts "API key: #{apikey}"
puts "Submitting test data to Kasabi dataset ..."
client = dataset.store_api_client()

Dir.entries("upload").each do |entry|
  if entry.match("eup_media")
    puts "submitting upload/#{entry} ..."
    resp = client.store_file( File.new("upload/#{entry}", "r"), "text/plain")
    puts "Data submitted. Update URI: #{resp}"

    while !client.applied?(resp)
      #all updates are async.
      #here we wait for update to be applied, but this is just for demo purposes
    end

    puts "Update applied"
  end

end