# RoboHummer

## Setup using Dart

This project uses `/srv/robo-media` to store media.

`dart up` to get the site going

### Melody Search

```
dart x web
cd ../search
python indexer.py /srv/robo-media/*/melody.xml
```

## Traditional Setup

### Web front end

    apt-get install php php-mbstring php-xml php-zip composer
    a2enmod rewrite
    cd web
    composer install
    chgrp -R www-data storage/*
    chgrp -R www-data bootstrap/cache/
    cp .env.example .env
    ./artisan key:generate

Edit `.env` as necessary for system configuration.

Edit `/etc/apache2/sites-enabled/xxx.conf`, adding the key `AllowOverride All`
(for htaccess files), and configure to serve `web/public/`.

    systemctl restart apache2

in the web folder:

    npm install
    npm run dev

--------------------------------------

### Audio analyzer service

Edit `MARSYAS_INSTALL` path in `analyzer/makefile` to match intall location of
[Marsyas](https://github.com/marsyas/marsyas).

    apt-get install build-essential libevent-dev libevhtp-dev libssl-dev
    cd analyzer
    make
    cd bin
    ./analyzer

To send it to the background, press `Ctrl-Z`, then execute `bg`.

--------------------------------------

### Search index

    apt-get install python-pip python-dev virtualenv
    source venv/bin/activate
    pip install nmslib
    cd search
    python indexer.py /path/to/music-xml/*.xml
