#!/usr/bin/env ruby
# encoding: UTF-8

require 'test/unit'
require './csv2rdf'


class TestScrapeMediaObject < Test::Unit::TestCase
  
  def setup
    
    @family_names = { 
      "MERKEL" => "Merkel" , 
      "VAN ROMPUY" => "van Rompuy" ,
      "THUN UND HOHENSTEIN" => "Thun und Hohenstein" ,
      "MÜLLER MEIER" => "Müller Meier" ,
    }
    
  #   @topics = [
  #     ["KARZAI, Hamid", "KARZAI", "Hamid", nil, nil],
  #     ["JAUREGUI ATONDO, Ramon", "JAUREGUI ATONDO", "Ramon", nil, nil],
  #     ["BENARAB-ATTOU, Malika", "BENARAB-ATTOU", "Malika", nil, nil],
  #     ["BENARAB-ATTOU, Malika (Greens/EFA, FR)", "BENARAB-ATTOU", "Malika", "Greens/EFA", "FR"],
  #     ["BARROSO, Jose Manuel (EC President)", "BARROSO", "Jose Manuel", "EC President", nil]
  #   ]
  #   
  #   @names = [
  #     ["KARZAI, Hamid", 
  #       { "givenName" => "Hamid", 
  #         "familyName" => "KARZAI",
  #         "title" => nil,
  #         "fullName" => "Hamid KARZAI",
  #       }],
  #     ["JAUREGUI ATONDO, Ramon", 
  #       { "givenName" => "Ramon", 
  #         "familyName" => "JAUREGUI ATONDO",
  #         "title" => nil,
  #         "fullName" => "Ramon JAUREGUI ATONDO",
  #       }],
  #     ["THUN UND HOHENSTEIN, Róza Maria Barbara (Gräfin von)",
  #       { "givenName" => "Róza Maria Barbara",
  #         "familyName" => "THUN UND HOHENSTEIN",
  #         "title" => "Gräfin von",
  #         "fullName" => "Róza Maria Barbara Gräfin von THUN UND HOHENSTEIN",
  #       }],
  #     ["BEATRIX (Queen)",
  #       { "title" => "Queen",
  #         "fullName" => "Queen BEATRIX",
  #       }],
  #     ["BENEDICT XVI (Pope)",
  #       { "title" => "Pope",
  #         "fullName" => "Pope BENEDICT XVI",
  #       }],
  #   ]
    
  end
  
  # # this tests parsing of the complete label
  # def test_parse_topic
  #   @topics.each do |topic|
  #     match = topic[0].match(TOPIC_PATTERN)
  #     assert_not_nil(match)
  #     assert_equal(topic[1], match[1])
  #     assert_equal(topic[2], match[2])
  #   end
  # end
  
  # def test_parse_name
  #   @names.each do |name|
  #     assert_equal(EUMediaCSV2RDF.parse_name(name[0]), name[1])
  #   end
  # end

  def test_name_case
    @family_names.keys.each do |name|
      assert_equal(EUMediaCSV2RDF.name_case(name), @family_names[name])
    end
  end
  
end