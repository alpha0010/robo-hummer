ARG IMG_TAG=$IMG_TAG
FROM docker-registry.internal.ccel.org:5443/php7:${IMG_TAG}

WORKDIR /var/www/web

COPY 000-default.conf /etc/apache2/sites-available/
COPY node.tar.xz      /tmp

RUN tar -xf /tmp/node.tar.xz -C /var/lib \
	&& apt-get install -y python pandoc texlive python-pip python-dev \
	&& pip install pybind11 && pip install nmslib \
	&& mv $(find /var/lib -maxdepth 1 -mindepth 1 -type d -name node-*) /var/lib/nodejs

RUN apt-get update && apt-get install -y python3=3.5.3* python3-pip \
	&& pip3 install music21==5.5.0 && pip3 install numpy

ENV PATH="${PATH}:/var/lib/nodejs/bin"
