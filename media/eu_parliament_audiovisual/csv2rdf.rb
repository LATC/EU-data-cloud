#!/usr/bin/env ruby
# encoding: UTF-8

require "csv"
require "rdf"
require "rdf/n3"
require "pp"
require "logger"
require "unicode_utils"
require "./diacritics"

include RDF

DCT = RDF::Vocabulary.new("http://purl.org/dc/terms/")
DCE = RDF::Vocabulary.new("http://purl.org/dc/elements/1.1/")
NFO = RDF::Vocabulary.new("http://www.semanticdesktop.org/ontologies/nfo\#")
PNA = RDF::Vocabulary.new("http://data.press.net/ontology/asset/")
GEONAMES = RDF::Vocabulary.new("http://www.geonames.org/ontology#")
PLACES = RDF::Vocabulary.new("http://purl.org/ontology/places#")
ORG = RDF::Vocabulary.new("http://www.w3.org/ns/org#")
BASE = "http://data.kasabi.com/dataset/latc-eu-media/"
MEDIA_DEF = RDF::Vocabulary.new("#{BASE}schema/")
MEDIA_TOPICS = RDF::Vocabulary.new("#{BASE}topics/")
MEDIA_PEOPLE = RDF::Vocabulary.new("#{BASE}people/")
MEDIA_ROLES = RDF::Vocabulary.new("#{BASE}roles/")
MEDIA_ORGS = RDF::Vocabulary.new("#{BASE}organisations/")
KASABI_COUNTRIES = RDF::Vocabulary.new("http://data.kasabi.com/dataset/countries/")
MEDIA_COUNTRIES = RDF::Vocabulary.new("#{BASE}countries/")
EUP_VID = RDF::Vocabulary.new("#{BASE}video/eu_parliament/")

# regex patterns:
URI_MATCH = /\?g=(.*?)$/
NAME_PATTERN = /^([\p{L}\s\-'']+), ([^\(]*?)( \(([^\(]*?), (.*?)\))?$/
EC_PATTERN = /^([\p{L}\s\-'']+), ([^\(]*?)( \((EC|CSL)\))$/

KNOWN_TOPICS = {}
graph = RDF::Graph.load("handcrafted_rdf/known_topics.n3")
query = RDF::Query.new({
  :topic => { 
    RDFS.label => :label 
  } 
})
query.execute(graph).each do |solution|
  KNOWN_TOPICS[solution.label.to_s] = solution.topic.to_s
end

KNOWN_TAGS = []
File.open("handcrafted_rdf/known_tags.txt", "r").each do |tag|
  KNOWN_TAGS << tag.strip
end

LOGGER = Logger.new("logs/eu_media.log")

NAME_PARTS = [ "van", "von", "de", "la", "den", "und" ]

RDF_TYPES = {
  "video" => NFO.Video,
  "audio" => NFO.Audio,
  "photo" => NFO.Image
}

class EUMediaCSV2RDF
  
  # do some things with input string to make it nice-looking for a URI
  def self.normalise_name(name)
    DIACRITICS.keys.each do |character|
      name = name.gsub(character, DIACRITICS[character])
    end
    name = UnicodeUtils.downcase(name).gsub(/[\s',()&\/\.]/, " ").strip.gsub(" ", "-").squeeze("-")
    return name
  end
  
  # a version of title_case that takes into account that some bits like "von", "van", etc. should not be capitalised
  def self.name_case(name)
    parts = UnicodeUtils.downcase(name).split
    parts.each do |part|
      part.gsub!(/\b\w/){$&.upcase} unless NAME_PARTS.index(part)
    end
    return parts.join(" ")
  end

  def initialize(rdf_file_name, asset_csv_file_name, topic_csv_file_name, type)
    @rdf_file_name = rdf_file_name
    @asset_csv_file_name = asset_csv_file_name
    @topic_csv_file_name = topic_csv_file_name
    @type = type
    @ns = RDF::Vocabulary.new("#{BASE}#{@type}/eu_parliament/")
  end

  
  def run

    topic_csv = CSV.open(@topic_csv_file_name, "r")
    out_file = File.open(@rdf_file_name, "w")

    header = topic_csv.shift

    # convert csv into an array of hashes (see http://snippets.dzone.com/posts/show/3899)
    asset_csv_data = CSV.read(@asset_csv_file_name)
    headers = asset_csv_data.shift.map {|i| i.to_s }
    string_data = asset_csv_data.map {|row| row.map {|cell| cell.to_s } }
    array_of_hashes = string_data.map {|row| Hash[*headers.zip(row).flatten] }
    
    # Ontologies used: 
    #  - dublin core terms (dct) http://purl.org/dc/terms/
    #  - dublin core elements (dce) http://purl.org/dc/elements/1.1/   - for a simple coypright statement
    #  - nepomuk file ontology (nfo) http://www.semanticdesktop.org/ontologies/nfo#
    #  - skos (skos) http://www.w3.org/2004/02/skos/core#
    #  - foaf (FOAF)


    # [ 0 =>"uri",                pna:Video
    #   1 => "title",             dct:title
    #   2 => "event_date",        dct:created
    #   3 => "duration",          nfo:duration  "P3M34S"^^^xsd:duration
    #   4 => "reference",         skos:notation
    #   5 => "description",       dct:description
    #   6 => "prod_format",       media:prod_format (either media:Edited or media:PreEdited)
    #   7 => "format",            nfo:aspectRatio
    #   8 => "copyright",         dce:rights
    #   9 => "filename"]          nfo:filename

    graph = RDF::Graph.new
    puts "converting assets..."
    array_of_hashes.each do |line|
      
      # common to all media types:
      
      id = "#{line['uri'].match(URI_MATCH)[1]}"
      puts id
      asset = @ns[id]
      graph << [asset, RDF.type, RDF_TYPES[@type]]
      
      graph << [asset, FOAF.page, RDF::URI(line['uri'])]
      graph << [asset, DCT.title, line['title']]
      graph << [asset, DCT.created, RDF::Literal.new(Date.parse(line['event_date']))] if (line['event_date'] != "")
      graph << [asset, SKOS.notation, line['reference']] if (line['reference'])
      graph << [asset, DCT.description, line['description']] if (line['description'])
      graph << [asset, DCE.rights, line['copyright']] if (line['copyright'])
      graph << [asset, NFO.filename, line['filename']] if (line['filename'])


      if (@type == "video")
        if (line['duration'] != "")
          duration = line['duration'].match(/([0-9]+)\:([0-9]+)/)
          graph << [asset, NFO.duration, RDF::Literal.new("P#{duration[1]}M#{duration[2]}S", :datatype => RDF::XSD.duration)]
        end

        line['prod_format'] == "Edited" ? prod_format = MEDIA_DEF.Edited : prod_format = MEDIA_DEF.PreEdited
        graph << [asset, MEDIA_DEF.production_format, prod_format]

        graph << [asset, NFO.aspectRatio, line['format']] if (line['format'])
      elsif (@type == "audio")
        if (line['duration'] != "")
          duration = line['duration'].match(/([0-9]+)\:([0-9]+)/)
          graph << [asset, NFO.duration, RDF::Literal.new("P#{duration[1]}M#{duration[2]}S", :datatype => RDF::XSD.duration)]
        end
        graph << [asset, NFO.averageBitrate, line['bit_rate']] if (line['bit_rate'])
        # TODO: file_type
      elsif (@type == "photo")
        graph << [asset, FOAF.thumbnail, RDF::URI(line['thumb'])] if (line['thumb'])
        if (line['size'])
          size = line['size'].match(/([0-9]+) x ([0-9]+)/)
          graph << [asset, NFO.width, size[1]]
          graph << [asset, NFO.height, size[2]]
        end
        if (resolution = line['resolution'])
          graph << [asset, NFO.horizontalResolution, resolution]
          graph << [asset, NFO.verticalResolution, resolution]
        end
        graph << [asset, RDFS.comment, line['headline']] if (line['headline'])
      end
    end

    puts "converting topics..."

    topic_csv.each do |line|
      id = "#{line[0].match(URI_MATCH)[1]}"
      puts id
      asset = @ns[id]
      link = line[1]
      label = line[2]

      # pattern to match:
      # KARZAI, Hamid
      # JAUREGUI ATONDO, Ramon
      # BENARAB-ATTOU, Malika
      # BENARAB-ATTOU, Malika (Greens/EFA, FR)

      subject_id = "#{link.match(/\?cid=(.*?)$/)[1]}"
      norm_label = EUMediaCSV2RDF.normalise_name(label)
      # we always create a link to the subject resource
      
      unless (KNOWN_TOPICS[label]) # this is not something in our gazetteer
        if (match = label.match(NAME_PATTERN) || match = label.match(EC_PATTERN)) # the tag matches our name pattern. We havea a person.
          subject_resource = MEDIA_PEOPLE[EUMediaCSV2RDF.normalise_name("#{match[1]} #{match[2]}")]
          graph << [subject_resource, RDF.type, FOAF.Person]
          givenName = match[2]
          familyName = EUMediaCSV2RDF.name_case(match[1])
          graph << [subject_resource, FOAF.familyName, familyName] # if match[1]
          graph << [subject_resource, FOAF.givenName, givenName] # if match[2]
          graph << [subject_resource, FOAF.name, "#{givenName} #{familyName}"]
          if match[3] # there is an organisation
            role = MEDIA_ROLES[norm_label]
            organisation = MEDIA_ORGS[EUMediaCSV2RDF.normalise_name(match[3])]
            graph << [subject_resource, ORG.hasMembership, role]
            graph << [role, ORG.organization, organisation]
            graph << [organisation, FOAF.name, match[4]] if match[4]
            graph << [organisation, RDF.type, ORG.FormalOrganization]
            if match[5] # there is a country
              country = KASABI_COUNTRIES[EUMediaCSV2RDF.normalise_name(match[5]).upcase]
              graph << [organisation, ORG.site, country]
              graph << [country, GEONAMES.countryCode, match[5]]
              graph << [country, RDF.type, PLACES.Country]
            end
          end
        else # the tag does not match our name pattern
          # just create a generic topic:
          subject_resource = MEDIA_TOPICS[norm_label]
          graph << [subject_resource, RDF.type, OWL.Thing]
          LOGGER.debug("topic not recognised as person or known tag: '#{label}'") unless KNOWN_TAGS.include?(label)
        end
        # in any case, keep the original tag as a label and link to the page showing all resources with this tag
        graph << [subject_resource, RDFS.label, label]
        graph << [subject_resource, FOAF.page, RDF::URI.new(link)]
      else
        subject_resource = RDF::URI.new(KNOWN_TOPICS[label])
      end
      graph << [asset, DCT.subject, subject_resource]
    end



    RDF::Writer.for(:ntriples).new(out_file) do |writer|
      graph.each_statement do |statement|
        writer << statement
      end
    end

  end

end

class MediaTopic
  attr_accessor :label
  
  def initialize(label)
    @label = label
  end
  
end

class MediaPerson < MediaTopic
  attr_accessor :givenName, :familyName, :fullName, :party, :country, :role

  def initialize(label)
    @label = label
    # name_affiliation_pattern = /(.*?) \((.*?)\)/
    # name_pattern = /^([\p{L}\s\-'']+), (.*?)/
    # if (match = @label.match(name_affiliation_pattern))
    #   name_part = match[1]
    #   role_affiliation_part = match[2]
    # 
    #   if (match = name_part.match(name_pattern))
    #     @familyName = match[1]
    #     @givenName = match[2]
    #     @fullName = "#{@givenName} #{@familyName}"
    #   else
    #     @fullName = name_part
    #   end
    #   
    #   if (match = role_affiliation_part.match(/(.*?), (.*?)/)
    #     @party = match[1]
    #     @country = match[2]
    #   end
    # else
    #   @fullName = @label
    # end
  end

end


