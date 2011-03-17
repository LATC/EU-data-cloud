#!/usr/bin/python

import simplejson as json
import urllib2

ROOT = "http://latc-console.few.vu.nl/api/"

def main():
    url= ROOT + "tasks"
    res = json.load(urllib2.urlopen(url))
    for entry in res['task']:
        print entry['title']
        file_name = entry['title']
        file_id = entry['identifier']
        file_name = file_name.replace(' -> ','To').replace(' ', '_')
        #print ('Save ' + file_name)
        #f = open('data/%s' % file_name, 'w')
        #file = urllib2.urlopen(ROOT + "configuration/%s/specification" % file_id)
        #f.write(file.read())
        #f.close()

if __name__=='__main__':
    main()
