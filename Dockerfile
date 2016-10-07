# Set the base image to Ubuntu
FROM ubuntu

MAINTAINER nkpmedia <webmaster@nkp-media.de>

RUN apt-get update

RUN apt-get install -y apache2 php php-ldap php-xml php-mbstring php-zip libapache2-mod-php7.0  git

RUN git clone https://github.com/pbnl/pbnl-ldap-gui.git
RUN mkdir /var/www/pbnl-ldap-gui
RUN mv ./pbnl-ldap-gui /var/www

RUN rm /etc/apache2/sites-available/000-default.conf
ADD ./docker-data/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN rm /etc/ldap/ldap.conf
ADD ./docker-data/ldap.conf /etc/ldap/ldap.conf

ADD ./docker-data/pbnl-ldapserver.pem /etc/ldap/pbnl-ldapserver.pem

RUN a2enmod rewrite

EXPOSE 80

RUN service apache2 restart

WORKDIR /var/www/pbnl-ldap-gui/
RUN chown www-data:www-data ./ -R
RUN ./composer.phar -n install

WORKDIR /

CMD /usr/sbin/apache2ctl -D FOREGROUND