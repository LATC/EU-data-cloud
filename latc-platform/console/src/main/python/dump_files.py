#!/usr/bin/python

import simplejson as json
import urllib2

ROOT = "http://fspc409.few.vu.nl/LATC_Console/api/"

def main():
    url= ROOT + "queue"
    res = json.load(urllib2.urlopen(url))
    for entry in res['queue']:
        file_name = entry['title']
        file_id = entry['identifier']
        print ('Save ' + file_name)
        f = open('data/%s' % file_name, 'w')
        file = urllib2.urlopen(ROOT + "configuration/%s/specification" % file_id)
        f.write(file.read())
        f.close()

if __name__=='__main__':
    main()
