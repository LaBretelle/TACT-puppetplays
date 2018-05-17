install:
	composer install --prefer-source
	npm install
	npm run dev
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:schema:update --force
