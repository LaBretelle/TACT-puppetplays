init: install cache

update-prod:
	composer install --prefer-source
	npm install
	php bin/console fos:js-routing:dump --format=json --target=public/routes/fos_js_routes.json
	php bin/console bazinga:js-translation:dump
	npm run build
	php bin/console doctrine:schema:update --force
	php bin/console cache:clear --env=prod
	chown -R www-data:www-data public
	chown -R www-data:www-data var/

install:
	composer install --prefer-source
	php bin/console fos:js-routing:dump --format=json --target=public/routes/fos_js_routes.json
	npm install
	npm run dev
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:schema:update --force
	php bin/console doctrine:fixtures:load
	php bin/console bazinga:js-translation:dump
	chown -R www-data:www-data public
	chown -R www-data:www-data var/

cache:
	php bin/console cache:clear
	php bin/console cache:clear --env=prod

routes:
	php bin/console fos:js-routing:dump --format=json --target=public/routes/fos_js_routes.json

fixtures:
	php bin/console doctrine:fixtures:load

entity:
	php bin/console make:entity
	php bin/console doctrine:schema:update --force

update-dev:
	composer install --prefer-source
	npm install
	php bin/console fos:js-routing:dump --format=json --target=public/routes/fos_js_routes.json
	php bin/console bazinga:js-translation:dump
	npm run dev
	php bin/console doctrine:schema:update --force
	php bin/console cache:clear
	chown -R www-data:www-data public
	chown -R www-data:www-data var/
