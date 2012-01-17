#!/bin/bash

rapper -i turtle handcrafted_rdf/known_topics.n3 > output/known_topics.nt
rapper -i turtle handcrafted_rdf/other.ttl > output/other.nt
cat output/*/*.nt output/*.nt > eup_media_assets.nt
split -l 9000 eup_media_assets.nt eup_media.
rm upload/*
mv eup_media.* upload