## RoboHummer

    cd web
    composer install
    chgrp -R www-data storage/
    chgrp -R www-data bootstrap/cache/

--------------------------------------

    apt-get install libevhtp-dev libevent-dev libssl-dev


## Build the indexer

* virtualenv venv
* source venv/bin/activate
* pip install nmslib
* sudo apt install sqlite
* cd search
* python indexer.py /home/hplantin/work/fanny/flexdata/melody/xml/*.xml

## run the analyzer webserver

* cd analyzer
* make
* cd bin
* ./analyzer


