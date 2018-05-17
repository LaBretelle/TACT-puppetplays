init: install cache

install:
	composer install --prefer-source
	npm install
	npm run dev
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:schema:update --force

cache:
	php bin/console cache:clear
	php bin/console cache:clear --env=prod
	php bin/console cache:warmup
	php bin/console cache:warmup --env=prod

	
