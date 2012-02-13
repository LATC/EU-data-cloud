- the EU Parliament media is hosted through an aspx site, which makes it hard to crawl
- for part of the scraping, I'm therefore using watir-webdriver to remote-control a browser from ruby
- the rest of the scraping can be done nicely with nokogiri
- there are several scripts involved:

  1.) find_all_assets.rb goes to http://audiovisual.europarl.europa.eu/Search.aspx with watir and does a search
		on "*", restricted to videos, and then pages through the results. It scrapes the hash ids for each video 
		from the pages and puts them into a text file, line by line. The current date will be appended, in a
		similar matter as server log files do it.
		The script will also check the top line of the latest such file to see which video asset is the newest 
		one we already know. This is to prevent the script from having to scrape everything again, which takes
		a long time.
		
  2.) euMediaScraper.rb (launched from run_scraper.rb) visits every asset and scrapes information from it. Two 
		csv files are created as output: video_assets.csv and video_topics.csv, using a URI based on the asset id 
		as a common key. Also appended with the specified date (current date is default).
		
  3.) csv2rdf.rb finally converts the csv to rdf. Apart from simple mapping of values, it also tries to figure
		out for each topic if it is a person or not. This is done based on a regex (covering about 95% of the cases)
		and an additional gazetteer list with know-persons and known-non-persons.