A Symfony project created on August 1, 2016, 11:07 pm.

Ein GUI für das PBNL-LDAP

Installation:

Extrahiere das Archiv in den Webordner.

Du brauchst diese minimale Apache Konfiguration:
```
<VirtualHost *:80>
    ServerName domain.tld
    ServerAlias www.domain.tld

    DocumentRoot /var/www/project/web
    <Directory /var/www/project/web>
        AllowOverride All
        Order Allow,Deny
        Allow from All
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeScript assets
    # <Directory /var/www/project>
    #     Options FollowSymlinks
    # </Directory>

    ErrorLog /var/log/apache2/project_error.log
    CustomLog /var/log/apache2/project_access.log combined
</VirtualHost>
```
Du musst folgende Umgebungsvariablen setzen:
```
export LDAP_IP=192.168.1.1
export LDAP_BIND_PWD=passwort
export LDAP_BIND_DN=cn=admin,dc=domain,dc=com
```
Das System arbeitet mit dem LDAP-Tree prefix dc=pbnl,dc=de
