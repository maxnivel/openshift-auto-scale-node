FROM php:7.1-apache

RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load

RUN apt-get update -y && apt-get install ssh -y

ADD entrypoint.sh /bin/

ENTRYPOINT /bin/entrypoint.sh