# Development

Utworzenie środowiska testowego:

```
git clone https://github.com/bambooooooo/pimcore-test.git
docker compose up -d
docker compose exec php composer install
docker compose exec php vendor/bin/pimcore-install
```

