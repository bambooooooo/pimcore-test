# Development

Utworzenie środowiska testowego:

```
git clone https://github.com/bambooooooo/pimcore-test.git
docker compose up -d
docker compose exec php composer install
docker compose exec php vendor/bin/pimcore-install
docker compose exec php bin/console generic-data-index:update:index --recreate_index
```

Panel administracyjny: ``/pimcore-studio``, panel fabryki: ``/factory/login``. Szczegóły (OpenSearch,
Mercure, plugin Studio, gridy, aktualizacja starszych instancji): [studio.md](studio.md).
