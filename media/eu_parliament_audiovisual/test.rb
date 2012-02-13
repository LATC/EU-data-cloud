require "./euMediaScraper.rb"
media_class = TYPES["photo"]
puts media_class.header
asset_id = "7142c3a2-545e-4d1d-b80b-576b81d279e6"
asset_page = "http://audiovisual.europarl.europa.eu/AssetDetail.aspx?g=#{asset_id.strip}"
asset = media_class.check_and_create(asset_page)
puts asset.to_a