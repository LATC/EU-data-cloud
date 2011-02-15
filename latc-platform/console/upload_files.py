#!/usr/bin/python

import os
import pycurl

#ROOT = "http://fspc409.few.vu.nl/LATC_Console/api/"
ROOT="http://localhost:8080/console/api/"

def main():
    for file_name in os.listdir('data/'):
        print ('Send ' + file_name)
        # Upload the file
        c = pycurl.Curl()
        values = [("fileToUpload", (pycurl.FORM_FILE, 'data/' + file_name))]
        c.setopt(c.URL, ROOT + "tasks")
        c.setopt(c.HTTPPOST, values)
        c.perform()
        c.close()

#length = int(self.headers['Content-Length'])
#post_data = urllib.parse.parse_qs(self.rfile.read(length).decode('utf-8'))
        
        
if __name__ == '__main__':
    main()
