TACT - Plateforme de Transcription et d'Annotation de Corpus Scientifiques
==========================================================================


### Install

- clone this repo
- cd to `clonedfolder`
- create .env file from .env.dist

```bash
mv .env.dist .env
```

- edit `.env` file entries. It must match with environment variables set in `docker-compose.yml`


- do the same with the .env.dist file in  `clonedfolder/application`
- create .env file from .env.dist


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
