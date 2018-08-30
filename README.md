PATACS - AKA PHUN2 - project
============================


### Install

- clone this repo and cd to to  `clonedfolder/application`
- create .env file from .env.dist

```bash
cp .env.dist .env
```

- edit `.env` file.
- set the proper database connection string. It must match with environment variables set in `docker-compose.yml`
- in order to make mail work change the MAILER_URL entry

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
chown -R www-data:www-data application/public/user_images application/public/project_files

```

- symfony app should be available @ http://localhost:8082/
- adminer should be available @ http://localhost:8080/


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
