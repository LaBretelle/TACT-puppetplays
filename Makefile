init:
	cp .env.dist .env
	vi .env
	cp application/.env.dist application/.env
	vi application/.env
	openssl genrsa 2048 > docker-files/certs/server.key
	openssl req -new -x509 -nodes -sha256 -days 365 -key docker-files/certs/server.key -out docker-files/certs/server.crt
	cp docker-files/certs/server.crt docker-files/certs/server-chain-file.crt
	docker-compose up -d
	docker-compose exec apache make init
