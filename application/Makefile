init: install cache

install:
	composer install --prefer-source
	npm install
	npm run dev
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:schema:update --force
	php bin/console fos:js-routing:dump --format=json --target=public/routes/fos_js_routes.json
	php bin/console doctrine:fixtures:load

cache:
	php bin/console cache:clear
	php bin/console cache:clear --env=prod
	php bin/console cache:warmup
	php bin/console cache:warmup --env=prod

routes:
	php bin/console fos:js-routing:dump --format=json --target=public/routes/fos_js_routes.json

fixtures:
	php bin/console doctrine:fixtures:load
