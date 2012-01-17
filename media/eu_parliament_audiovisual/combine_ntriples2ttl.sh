#!/bin/bash

rapper -i turtle handcrafted_rdf/known_topics.n3 > output/known_topics.nt
rapper -i turtle handcrafted_rdf/other.ttl > output/other.nt
cat output/*/*.nt output/*.nt | rapper -i ntriples -o turtle - http://data.kasabi.com/dataset/latc-eu-media/  \
	-f 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"' \
	-f 'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"' \
	-f 'xmlns:owl="http://www.w3.org/2002/07/owl#"' \
	-f 'xmlns:foaf="http://xmlns.com/foaf/0.1/"' \
	-f 'xmlns:dct="http://purl.org/dc/terms/"' \
	-f 'xmlns:dce="http://purl.org/dc/elements/1.1/"' \
	-f 'xmlns:nfo="http://www.semanticdesktop.org/ontologies/nfo#"' \
	-f 'xmlns:pna="http://data.press.net/ontology/asset/"' \
	-f 'xmlns:places="http://purl.org/ontology/places#"' \
	-f 'xmlns:geonames="http://www.geonames.org/ontology#"' \
	-f 'xmlns:org="http://www.w3.org/ns/org#"' \
	-f 'xmlns:media_def="http://data.kasabi.com/dataset/latc-eu-media/schema/"' \
	-f 'xmlns:media_topics="http://data.kasabi.com/dataset/latc-eu-media/topics/"' \
	-f 'xmlns:media_roles="http://data.kasabi.com/dataset/latc-eu-media/roles/"' \
	-f 'xmlns:media_people="http://data.kasabi.com/dataset/latc-eu-media/people/"' \
	-f 'xmlns:media_orgs="http://data.kasabi.com/dataset/latc-eu-media/organisations/"' \
	-f 'xmlns:kasabi_countries="http://kasabi.com/dataset/countries/"' \
	-f 'xmlns:eup_vid="http://data.kasabi.com/dataset/latc-eu-media/video/eu_parliament/"' \
	-f 'xmlns:eup_aud="http://data.kasabi.com/dataset/latc-eu-media/audio/eu_parliament/"' \
	-f 'xmlns:eup_img="http://data.kasabi.com/dataset/latc-eu-media/photo/eu_parliament/"' \
> eup_media_assets.ttl