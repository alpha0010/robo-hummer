ARG IMG_TAG=$IMG_TAG
FROM docker-registry.internal.ccel.org:5443/php7:${IMG_TAG} AS web

WORKDIR /var/www/web

COPY 000-default.conf /etc/apache2/sites-available/
COPY node.tar.xz      /tmp

RUN tar -xf /tmp/node.tar.xz -C /var/lib \
    && apt-get update \
    && apt-get install -y python pandoc texlive python-pip python-dev \
                          fluidsynth fluid-soundfont-gm timgm6mb-soundfont lame ffmpeg \
    && pip install pycodestyle \
    && mv $(find /var/lib -maxdepth 1 -mindepth 1 -type d -name node-*) /var/lib/nodejs \
    && apt-get install -y python3=3.5.3* python3-pip sudo \
    && yes | adduser -q --disabled-password python \
    && echo "www-data ALL=(python) NOPASSWD: ALL" > /etc/sudoers.d/www-data \
    && su python -c 'pip3 install music21==5.5.0' && su python -c 'pip3 install numpy' \
    && su python -c 'pip3 install pybind11' && su python -c 'pip3 install nmslib' \
    && su python -c 'pip3 install simplejson'

RUN curl http://lilypond.org/download/binaries/linux-64/lilypond-2.18.2-1.linux-64.sh > lilypondinstall.sh \
    && sh lilypondinstall.sh

ENV PATH="${PATH}:/var/lib/nodejs/bin"

FROM web AS cron

RUN apt-get update \
    && apt-get install --no-install-recommends -y cron rsyslog \
    && echo 'EXTRA_OPTS="-L 4"' >> /etc/default/cron \
    && echo "* * * * * /usr/local/bin/php /var/www/web/artisan schedule:run >> /dev/null 2>&1" | crontab -

COPY cronmain.sh /

CMD [ "/cronmain.sh" ]
