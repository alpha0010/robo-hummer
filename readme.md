## RoboHummer

    apt-get install php php-mbstring php-xml php-zip composer
    a2enmod rewrite
    cd web
    composer install
    chgrp -R www-data storage/*
    chgrp -R www-data bootstrap/cache/
    cp .env.example .env
    ./artisan key:generate

Edit `/etc/apache2/sites-enabled/xxx.conf`, adding the key `AllowOverride All`
(for htaccess files), and configure to serve `web/public/`.

    systemctl restart apache2

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


