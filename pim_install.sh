#!/bin/bash

# Pobieranie pimcore
docker run -u `id -u`:`id -g` --rm -v `pwd`:/var/www/html pimcore/pimcore:php8.2-latest composer create-project pimcore/skeleton pimcore

cd pimcore

# Ustawienie ownera plików
sed -i "s|#user: '1000:1000'|user: '$(id -u):$(id -g)'|g" docker-compose.yaml

docker compose up -d

# Instalacja
docker compose exec php vendor/bin/pimcore-install --mysql-host-socket=db --mysql-username=pimcore --mysql-password=pimcore --mysql-database=pimcore \
	--admin-username=pimcore --admin-password=pimcore \
	--install-bundles=PimcoreApplicationLoggerBundle,PimcoreCustomReportsBundle \
	--no-interaction

# Owner dla plików c.d.
sudo chown -R $(id -u):$(id -g) var
sudo chown -R $(id -u):$(id -g) public

# Uprawnienia dla skryptów
chmod ug+x bin/*

# Testy
# docker compose run --user=root --rm test-php chown -R $(id -u):$(id -g) var/ public/var/
# docker compose run --rm test-php vendor/bin/pimcore-install -n
# docker compose run --rm test-php vendor/bin/codecept run -vv
