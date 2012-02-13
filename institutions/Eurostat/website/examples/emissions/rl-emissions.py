#!/usr/bin/python
#This is a query that shows the amount of Green House Gases per capitae
#in the period 1990-2009.

#You will need:
#Reportlabs package: http://www.reportlab.com/software/opensource/rl-toolkit/download/
#or on Fedora install python-reportlab
#If you get an exception "RenderPMError: Can't setFont(Times-Roman)
#missing the T1 files?" (like I got) on d.save in above code - Solution
#is to download and extract http://www.reportlab.com/ftp/fonts/pfbfer.zip
#in reportlabs/fonts directory
#The sparql.py from https://svn.eionet.europa.eu/repositories/Zope/trunk/sparql-client

import sparql
from reportlab.graphics.shapes import Drawing
from reportlab.graphics.charts.linecharts import HorizontalLineChart
from reportlab.graphics.charts.textlabels import Label

querytemplate = """
PREFIX qb: <http://purl.org/linked-data/cube#>
PREFIX e: <http://ontologycentral.com/2009/01/eurostat/ns#>
PREFIX sdmx-measure: <http://purl.org/linked-data/sdmx/2009/measure#>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX g: <http://eurostat.linked-statistics.org/ontologies/geographic.rdf#>
PREFIX dataset: <http://eurostat.linked-statistics.org/data/>

SELECT ?country
       ?year
       ?population
       ?ghgtotal
       xsd:decimal(?ghgtotal)*1000/(xsd:decimal(?population)) AS ?percapita
FROM <http://eurostat.linked-statistics.org/data/demo_pjanbroad.rdf>
FROM <http://eurostat.linked-statistics.org/data/env_air_gge.rdf>
FROM <http://semantic.eea.europa.eu/home/roug/eurostatdictionaries.rdf>
WHERE {
  ?popobs qb:dataset dataset:demo_pjanbroad ;
        e:time ?uyear;
        e:freq <http://eurostat.linked-statistics.org/dic/freq#A>;
        e:age <http://eurostat.linked-statistics.org/dic/age#TOTAL>; 
        e:sex <http://eurostat.linked-statistics.org/dic/sex#T>;
        e:geo ?ucountry;
        sdmx-measure:obsValue ?population.
  ?ghgobs qb:dataset dataset:env_air_gge ;
        e:geo ?ucountry;
        e:time ?uyear;
        e:airsect <http://eurostat.linked-statistics.org/dic/airsect#TOT_X_5>;
        sdmx-measure:obsValue ?ghgtotal.
  ?ucountry skos:prefLabel ?country.
  ?uyear skos:prefLabel ?year
} ORDER BY ?country ?year
"""

endpoint = "http://semantic.eea.europa.eu/sparql"
s = sparql.Service(endpoint)

result = s.query(querytemplate)
h_labels = map(str,range(1990, 2010))
data_values = []
linelabels = [] # Must match data_values array
c = ""
data_row = -1
countries = []
for row in result.fetchall():
    if c != unicode(row[0]):
        c = unicode(row[0])
        countries.append(c)
        data_row += 1
        data_col = 0
        data_values.append([])
        linelabels.append([])
    data_values[data_row].append(float(str(row[4])))
    if data_col == 0:
        linelabels[data_row].append(c)
    else:
        linelabels[data_row].append(None)
    data_col += 1

d = Drawing(800, 600)
chart = HorizontalLineChart()
chart.width = 740
chart.height = 560
chart.x = 50
chart.y = 20
chart.lineLabelArray = linelabels
chart.lineLabelFormat = 'values'
chart.data = data_values
chart.categoryAxis.categoryNames = h_labels
chart.valueAxis.valueMin = 0

d.add(chart)
d.save(fnRoot='ghg-totals', formats=['png'])
