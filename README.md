TACT - Plateforme de Transcription et d'Annotation de Corpus Scientifiques
==========================================================================


### Install (DEV - localhost)

```bash
git clone [thisrepository]
cd tact
cp .env.dist .env
# définition des comptes/bases mysql pour qu'ils soient générés par docker.
vi|nano|emacs .env
cp ./application/.env.dist ./application/.env
# répercussion pour Symfony des comptes/bases précédemment créés
vi|nano|emacs ./application/.env
```

### Install (production env.)
- copy your ssl certificate(s) and key into `docker-files/certs` folder
- edit `docker-files/Dockerfile`

```
# For local dev -> in production comment those lines
ADD ./certs/server.crt /etc/ssl/certs/server.crt
ADD ./certs/server.key /etc/ssl/private/server.key

# For production uncomment those lines
# ADD ./certs/star_demarre-shs_fr.crt /etc/ssl/certs/server.crt
# ADD ./certs/demarre-shs.fr.key /etc/ssl/private/server.key

```
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

### Run containers and install dependencies

```bash
# lance les services en mode daemon (et les build si besoin la première fois)
docker-compose up -d
# lance le make init présent dans ./application/Makefile
docker-compose exec apache make init
```

- symfony app should be available @ http://localhost:8082/
- adminer should be available @ http://localhost:8088/

### Usefull commands

> all this commands should be run inside docker container

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

### Tagging
- vX.Y.Z
X incremented for major changes
Y incremented for new feature
Z incremented for bug fix or really minor change

### Thanks
Myriam EL HELOU & Sami BOUHOUCHE for their [useful work](https://github.com/elheloum/TEI2JSON) not implemented in this app yet.
