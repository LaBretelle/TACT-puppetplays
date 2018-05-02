Phun 2 project
==============


### Install

- clone this repo and cd to to the cloned folder
- create .env file from .env.dist

```bash
cp .env.dist .env
```

- edit `.env` file.
- set the proper database connection string. It must match with environment variables set in `docker-compose.yml`
- open `config/packages/doctrine.yaml` file
- check that the driver is set to `driver:db` (this is the docker mysql service name)
- edit `docker/override.ini` file, it will override php.ini default configuration
- edit `docker/app.conf` file if you need to

### Run containers and install dependencies


```bash
# get your containers names
docker ps

# build / start the containers and detach the process
docker-compose up -d

# execute bash in apache container service
docker exec -it phun2_apache_1 /bin/bash

# you should be in /var/www folder where symfony code lives
# install php dependencies
composer install --prefer-source
# install node dependencies
npm install
# build css and js
npm run dev

# test your mysql connection
php bin/console doc:sch:create
```

- symfony app should be available @ http://localhost:8082/
- adminer should be available @ http://localhost:8080/
