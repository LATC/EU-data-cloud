This is a collection of manually-curated link specifications along with
resulting link sets.

Each link set should be in its own directory. The general naming convention for
directories is "dataset1-dataset2-entitytype", e.g., "dbpedia-geonames-cities".

Each directory should contain the following files:

README.txt -- short document with some documentation: who created this, when,
              were any major revisions made, anything else noteworthy
spec.xml -- Silk link specification
links.nt -- resulting link set (all links)
positive.nt -- manually verified correct links (at least 10, please try 100)
negative.nt -- manually verified incorrect links (at least 10)

The positive.nt and negative.nt files should contain some difficult corner
cases if possible.

NOTE: This file is intended as an initial starting point and a request for
      feedback. Any comments to Richard or latc-project.
