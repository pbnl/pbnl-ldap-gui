# Set the base image to Ubuntu
FROM ubuntu

RUN apt-get update

RUN apt-get install -y apache2 apache2-php php php-ldap php-xml php-mbstring php-zip