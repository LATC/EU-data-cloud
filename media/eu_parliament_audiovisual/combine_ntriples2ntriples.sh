#!/bin/bash

if [ "$#" -gt 0 ]; then
	rapper -i turtle handcrafted_rdf/known_topics.n3 > output/known_topics.nt
	rapper -i turtle handcrafted_rdf/other.ttl > output/other.nt
	cat output/$1/*.nt output/*.nt > eup_media_assets.nt
	split -l 9000 eup_media_assets.nt eup_media.
	rm upload/*
	mv eup_media.* upload
else
	echo "You need to specify a date!"
fi

