'''
Created on Apr 7, 2011

@author: cgueret
'''

import os
import pycurl
import cStringIO
import json
import urllib
import urllib2
from datetime import datetime
import time
import re
import md5

TEST = "http://127.0.0.1:8080/console/api"
PRODUCTION = "http://latc-console.few.vu.nl/api"
SERVER = PRODUCTION

UP_TO_DATE, UPLOADED, UPDATED, DOWNLOADED, ERROR = range(5)
messages = {
    UP_TO_DATE : '[==] %s',
    UPLOADED   : '[++] %s',
    UPDATED    : '[->] %s',
    DOWNLOADED : '[<-] %s',
    ERROR      : '[!!] %s',
}

def synchronize_directory(dir):
    '''
    Synchronise the content of a directory with the LATC console
    '''
    tasks = [f for f in os.listdir('.') if os.path.isdir(f)]
    for task in sorted(tasks):
        res = synchronize_task(dir, task)
        print messages.get(res) % task

def synchronize_task(dir, task):
    '''
    Synchronise a specific task and return a status code
    '''
    spec_file = '%s/%s/spec.xml' % (dir, task)
    
    # Retrieve the ID from a previous upload
    id = None
    if os.path.isfile('%s/%s/id.txt' % (dir, task)):
        id = open('%s/%s/id.txt' % (dir, task)).readlines()[0]
        
    # If there is no ID, upload the file
    if id == None:
        # Read meta information and generate title
        meta = {}
        if os.path.exists('%s/%s/README.txt' % (dir, task)):
            tmp = map(lambda x:re.sub(r'[\r\n]+', '', x), open('%s/%s/README.txt' % (dir, task), 'r').readlines())
	    meta = dict((tmp[i * 2], tmp[i * 2 + 1]) for i in range(len(tmp) / 2))
        tmp = task.split('-')
        meta['Title:'] = "%s -> %s (%s)" % (tmp[0], tmp[1], "".join(tmp[2:]))

        # Upload the file
        curl = pycurl.Curl()
        response = cStringIO.StringIO()
        values = [
                  ('specification', (pycurl.FORM_FILE, spec_file)),
                  ('title', meta['Title:'])
        ]
        if 'Description:' in meta.keys():
            values.append(('description', meta['Description:']))
        if 'Creator:' in meta.keys():
            values.append(('author', meta['Creator:']))
        curl.setopt(curl.URL, SERVER + "/tasks")
        curl.setopt(curl.HTTPHEADER, [ 'Content-Type:multipart/form-data', 'Expect: ']);
        curl.setopt(curl.HTTPPOST, values)
        curl.setopt(curl.WRITEFUNCTION, response.write)
        curl.perform()

        # Save its ID
        #if curl.getinfo(pycurl.HTTP_CODE) == 200:
        res = json.loads(response.getvalue())
        open('%s/%s/id.txt' % (dir, task), 'w').write(res['id'])

        curl.close()

        # Return the status message
        return UPLOADED
    else:
        # Get the last modification date on server and on disk (day based)
        res = json.loads(urllib2.urlopen(SERVER + '/task/' + id).read())
        last_modif_server = datetime.fromtimestamp(time.mktime(time.strptime(res['modified'], "%Y-%m-%dT%H:%M:%S")))
        last_modif_file = datetime.fromtimestamp(os.stat(spec_file).st_mtime)
        delta = last_modif_file - last_modif_server
        
        # Dowload the current specification on a temporary file
        spec_server = urllib2.urlopen(SERVER + '/task/' + id + '/configuration').read()
        
        # Compare the two versions
        disk = md5.new("".join(open(spec_file).readlines())).hexdigest()
        server = md5.new(spec_server).hexdigest()
        if disk == server:
            return UP_TO_DATE            
        
        # Upload if more recent on disk
        if delta.days * delta.seconds > 0:
            curl = pycurl.Curl()
            data = [
                 ('api_key', 'aa4967eb8b7a5ccab7dbb57aa2368c7f'),
                 ('configuration', "".join(open(spec_file).readlines()))
            ]
            body = urllib.urlencode(data)
            curl.setopt(curl.URL, SERVER + '/task/' + id + '/configuration')
            curl.setopt(curl.HTTPHEADER, [ 'Content-Type:application/x-www-form-urlencoded; charset=utf-8', 'Expect: ']);
            curl.setopt(curl.UPLOAD, 1)
            request_buffer = cStringIO.StringIO(body)
            curl.setopt(pycurl.READFUNCTION, request_buffer.read)
            curl.setopt(curl.INFILESIZE, len(body))
            response = cStringIO.StringIO()
            curl.setopt(curl.WRITEFUNCTION, response.write)
            curl.perform()
            curl.close()
            return UPDATED

        # Download if more recent on server
        if delta.days * delta.seconds < 0:
            open(spec_file, 'w').write(spec_server)
            return DOWNLOADED

    # We should not reach that line
    return ERROR


if __name__ == '__main__':
    synchronize_directory('.')
