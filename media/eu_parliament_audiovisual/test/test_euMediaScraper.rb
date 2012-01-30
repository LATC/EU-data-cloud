require 'test/unit'
require '../euMediaScraper'


class TestScrapeMediaObject < Test::Unit::TestCase
  
  def setup
    @object = MediaObject.new("http://audiovisual.europarl.europa.eu/AssetDetail.aspx?g=05746769-e5a9-401d-847a-55384b03adf0")
  end
  
  def test_title_scraped
    assert_equal("Jerzy BUZEK, EP President, visits to Libya: Meeting with civil society and visit to the Susha and Al-Hayat refugee camps on the Libyan/Tunisian border", 
      @object.title)
  end
  
end