'''
Created on May 10, 2011

@author: cgueret
'''
import urllib2
import json
import os
import pycurl
import urllib

TEST = "http://127.0.0.1:8080/console/api"
PRODUCTION = "http://latc-console.few.vu.nl/api"
SERVER = PRODUCTION


if __name__ == '__main__':
    # Get list of IDs on the server
    id_map = {}
    res = json.loads(urllib2.urlopen(SERVER + "/tasks.json?filter=false").read())
    ids = set([task['identifier'] for task in res['task']])
    for task in res['task']:
        id_map[task['identifier']] = task['title']
    
    # Get list of IDs from the disk
    ids_disk = set()
    for task in sorted([f for f in os.listdir('.') if os.path.isdir(f)]):
        if os.path.isfile('%s/id.txt' % task):
            id = open('%s/id.txt' % task).readlines()[0]
            ids_disk.add(id)

    # Go over the things that are on the server but not mapped to the disk
    for id in ids - ids_disk:
        dir_name = id_map[id].replace(' -> ', '-').replace(' (', '-').replace(')', '')
        if os.path.isfile('%s/spec.xml' % dir_name):
            # On the disk but not mapped, delete on the server
            print "[DEL] " + dir_name
            curl = pycurl.Curl()
            curl.setopt(curl.URL, str(SERVER + "/task/" + id))
            curl.setopt(curl.CUSTOMREQUEST, 'DELETE')
            curl.setopt(curl.POSTFIELDS, urllib.urlencode([('api_key', 'aa4967eb8b7a5ccab7dbb57aa2368c7f')]))
            curl.perform()
        else:
            # On the server but not on the disk, download
            print "[GET] " + dir_name
            # TODO implement this eventually
            