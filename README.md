## TACT - plateforme de Transcription et d'Annotation de Corpus Textuels
---------------------

### Requirements
* a web server (apache, nginx, etc.)
* git
* docker
* docker-compose
* openssl
* make

### Install
```bash
git clone https://gitlab.com/litt-arts-num/tact.git
cd tact
make init
```

By default, Symfony app should be available @ http://localhost:8082/ or https://localhost:8443/
and adminer should be available @ http://localhost:8088/


### Https
By default, `make init` cmd (see `Makefile`) generates a self-signed certificate (.key & crt). You can replace it, by a production one, here : `docker-files/certs/`

### Usefull commands
> all this commands should be run inside docker container (`docker-compose exec apache bash`)

- create a new user (use `-a` if you want to create an admin user)
`bin/console app:create-user [-a]`

- create a new project status (public / private are created via data fixtures)
`bin/console app:create-project-status`

- create a new user status (manager / transcriber / validator are created via data fixtures)
`bin/console app:create-user-status`

- update exposed routes
`make routes`

- load fixtures
`make fixtures`

- other usefull commands can be found in `application/Makefile`


### Host config.
- we use apache and reverseproxy to target multiple applications in various docker containers. See this apache conf. example.

```
<IfModule mod_ssl.c>
 <VirtualHost *:443>
     ServerName tact.demarre-shs.fr
     SSLEngine on
     SSLProxyEngine on
     ProxyPreserveHost On
         ProxyPass / https://localhost:8443/
         ProxyPassReverse / https://localhost:8443/
     SSLCertificateFile /etc/ssl/certs/star_demarre-shs_fr.crt
     SSLCertificateKeyFile /etc/ssl/private/demarre-shs.fr.key
     SSLCertificateChainFile /etc/ssl/certs/DigiCertCA.crt
 </VirtualHost>
</IfModule>

```

### Thanks
Myriam EL HELOU & Sami BOUHOUCHE for their [useful work](https://github.com/elheloum/TEI2JSON) not implemented in this app yet. A maintained version is available [here](https://gitlab.com/litt-arts-num/tei2json).
