FROM docker-registry.internal.ccel.org:5443/php7

WORKDIR /var/www/web

COPY 000-default.conf /etc/apache2/sites-available/
COPY node.tar.xz      /tmp

RUN tar -xf /tmp/node.tar.xz -C /var/lib                                                \
	&& apt-get install -y python pandoc texlive                                         \
	&& mv $(find /var/lib -maxdepth 1 -mindepth 1 -type d -name node-*) /var/lib/nodejs

ENV PATH="${PATH}:/var/lib/nodejs/bin"
