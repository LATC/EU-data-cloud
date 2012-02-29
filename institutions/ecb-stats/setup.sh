#!/usr/bin/bash
sh xml-schema/download-schema.sh

php key_families_to_json.php > keyfamily.json
php key_families_to_rdf.php > keyfamily.nt

ruby crawler/download_ecb_files.rb

php crawler_csv_to_rdf.php crawler/output/series_list.csv > series.nt


