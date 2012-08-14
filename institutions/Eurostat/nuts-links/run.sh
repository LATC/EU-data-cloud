echo "Processing FU Berlin Eurostat additional links"
rdfcopy estatfub-additional.ttl TURTLE N-TRIPLE | sort > estatfub-additional.nt
echo "  ... `wc -l estatfub-additional.nt` triples"
echo "Processing FU Berlin Eurostat country links"
sparql --query estatfub-countries.sparql --results nt | sort > estatfub-countries.nt
echo "  ... `wc -l estatfub-countries.nt` triples"
echo "Processing FU Berlin Eurostat region links"
sparql --query estatfub-regions.sparql --results nt | sort > estatfub-regions.nt
echo "  ... `wc -l estatfub-regions.nt` triples"
echo "Processing KIT Eurostat links"
sparql --query estatwrap.sparql --results nt | sort > estatwrap.nt
echo "  ... `wc -l estatwrap.nt` triples"
echo "Processing nuts.geovocab.org links"
sparql --query geovocab.sparql --results nt | sort > geovocab.nt
echo "  ... `wc -l geovocab.nt` triples"
echo "Processing Linked NUTS links"
sparql --query linkednuts.sparql --results nt | sort > linkednuts.nt
echo "  ... `wc -l linkednuts.nt` triples"
echo "Processing RAMON country links"
sparql --query ramon-countries.sparql --results nt | sort > ramon-countries.nt
echo "  ... `wc -l ramon-countries.nt` triples"
echo "Processing RAMON region links"
sparql --query ramon-regions.sparql --results nt | sort > ramon-regions.nt
echo "  ... `wc -l ramon-regions.nt` triples"
echo "Done!"
