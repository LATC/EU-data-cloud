'''
Created on Aug 5, 2011

@author: Aftab Iqbal
'''

from SPARQLWrapper import SPARQLWrapper, JSON
import sys

def getDescriptions(dsName):
    
    sparqlEndpoint = "http://vmlion14.deri.ie:3030/DataSet/query"
    #sparqlEndpoint = "http://localhost:3030/Dataset/query"
    
    queryString = """prefix meta: <http://eurostat.linked-statistics.org/meta#>
    prefix dss: <http://eurostat.linked-statistics.org/dss#>
    prefix dcterms: <http://purl.org/dc/terms/>
    prefix void: <http://rdfs.org/ns/void#>
    prefix foaf: <http://xmlns.com/foaf/0.1/>
    prefix qb: <http://purl.org/linked-data/cube#>

    SELECT ?datasetDSDURI ?datasetDumpURL WHERE {
    ?mainds a void:Dataset;
    void:subset <""" + dsName + """> .
    <""" + dsName + """> a void:Dataset ;
    void:dataDump ?datasetDumpURL ;
    qb:DataStructureDefinition ?datasetDSDURI .
    }"""

    sparql = SPARQLWrapper(sparqlEndpoint)
    sparql.setQuery(queryString)
    
    # JSON output
    sparql.setReturnFormat(JSON)
    results = sparql.query().convert()

    print '\n'            
    for result in results["results"]["bindings"]:
        print 'dataset description URI --> ' + result["datasetDSDURI"]["value"]
        print 'dataset dump URL --> ' + result["datasetDumpURL"]["value"]


if __name__ == "__main__":
    getDescriptions(sys.argv[1])
