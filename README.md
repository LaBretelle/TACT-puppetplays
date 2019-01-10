TACT - Plateforme de Transcription et d'Annotation de Corpus Scientifiques
==========================================================================


### Install (DEV - localhost)

- clone this repo
- cd to `clonedfolder`
- create .env file from .env.dist

```bash
cp .env.dist .env
```

- edit `.env` file entries.
- *this `.env` file must be placed in the same folder than the `docker-compose.yml` file!!!*

- do the same with the .env.dist file in  `clonedfolder/application`

### Install (PRODUCTION)

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
- we use apache and reverseproxy to target multiple applications in various docker containers below is an example

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
# get your containers names
docker ps

# build / start the containers and detach the process
docker-compose up -d

# execute makefile:install in apache container service
docker-compose exec apache make init

# change directories ownership
docker-compose exec apache bash
chown -R www-data:www-data public/user_images public/project_files var/log var/cache

```

- symfony app should be available @ http://localhost:8082/
- adminer should be available @ http://localhost:8088/

### Update project TEI Schema

> Each project *must* define a schema.

- Put the CSV file (must be named `schema.csv`) in the `public/project_files/{projectid}/` folder
- Exec `php bin/console app:create-schema` (*inside the php docker container*)
- You'll be asked for the id of the project...
- This will  
  - Generate the file `tei-schema.json`
  - Update tranalations files `tei.en.yml` and `tei.fr.yml`

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
Z incremented for bug fix
