# Pimcore Studio

Od Pimcore 2026 panelem administracyjnym jest Pimcore Studio (``/pimcore-studio``). Stary panel
ExtJS (``/admin``) nie istnieje - stare linki ``/admin`` i ``/admin/login/deeplink?…`` przekierowują
(301) do Studio. Panel fabryki ma własne logowanie: ``/factory/login`` (ci sami użytkownicy).

## Usługi

| Usługa | Po co |
|---|---|
| ``opensearch`` | indeks wyszukiwania (generic-data-index) - drzewa, gridy i wyszukiwarka Studio czytają z niego |
| ``mercure`` | aktualizacje na żywo w Studio, wystawione przez nginx pod ``/hub`` |
| ``supervisord`` | m.in. przetwarza kolejkę indeksu (``pimcore_generic_data_index_queue``) |

Zmienne: ``DATABASE_URL``, ``PIMCORE_OPENSEARCH_DSN``, ``APPLICATION_SECRET``, ``MERCURE_URL``
(adres ``/hub`` widziany z przeglądarki), ``MERCURE_SERVER_URL``, ``MERCURE_JWT_KEY``. Klucz Mercure
musi być w ``.env`` (nie ``.env.local``), bo czyta go też kontener ``mercure`` przez docker compose.

MariaDB działa z ``--innodb-strict-mode=0``: bez tego zapis klas ``Product``/``ProductSet`` (szerokie
tabele) kończy się błędem.

Stan indeksu: ``bin/console generic-data-index:status``. Pełna przebudowa:
``bin/console generic-data-index:update:index --recreate_index`` (kolejkę przetwarza supervisord).
Wpisy kolejki, których wiadomość zaginęła (np. restart kontenerów w trakcie), są ponawiane dopiero
po 24 h; żeby wymusić od razu: ``UPDATE generic_data_index_queue SET dispatched = 0``.

## Plugin Studio (``assets/studio``)

Przyciski w toolbarze obiektów, okna etykiet i cenników grup, pozycja menu drzewa
"Add children (key pattern)...", status ofert, obracanie assetów. Źródła TypeScript/React w
``assets/studio/js/src``, adresy endpointów w ``lib/urls.ts``.

Plugin jest commitowany jako gotowe archiwum ``assets/studio/dist/build-<hash>.zip`` - serwer sam je
rozpakowuje do ``public/studio-build`` (``App\Studio\WebpackEntryPointProvider``), więc produkcja nie
potrzebuje Node.js. Po zmianie źródeł:

```
cd assets/studio
npm ci
npm run release     # build + nowe archiwum w dist/ (usuń stare archiwum)
```

Test ``tests/js/plugin-build.test.js`` pilnuje, że w ``dist/`` jest dokładnie jedno archiwum i pasuje
do źródeł.

Dane dla pluginu (stan obiektu, jakość, Optimik) zwraca ``App\Controller\StudioExtensionController``,
ikony i podpowiedzi w drzewie - ``App\EventSubscriber\StudioElementStyleSubscriber`` (klasy
``App\Model\AdminStyle``).

## Gridy

* Kolumny w grupie "Megstyl": cena z oferty (``OfferPrice``) i wymiary paczek (``PackageDimension``),
  ``src/Studio/Grid``. Dawne operatory ``Constant`` i ``Round`` zastępuje transformer Twig w Studio.
* Każdy eksport XLSX z grida osadza miniatury obrazów
  (``App\Studio\Export\ImageEmbeddingXlsxExportService``).
* ``bin/console app:studio:migrate-grid-configs [--dry-run] [--id=…]`` kopiuje zapisane układy grida
  ze starego panelu (z właścicielem, udostępnieniami i ulubionymi); można uruchamiać wielokrotnie.
  Nie przenoszą się własne nagłówki kolumn (Alias) ani kolumny Concatenator.

## Aktualizacja instancji

Kopia na Pimcore 11: najpierw ``bin/upgrade-pimcore12.sh`` (branch ``upgrade/pimcore-12``), potem
``bin/upgrade-pimcore-studio.sh`` (branch ``upgrade/pimcore-studio``). Skrypt robi kopię bazy,
przełącza kontenery na PHP 8.4, dodaje OpenSearch i Mercure, instaluje bundle, buduje indeks
i migruje układy gridów. Można go uruchomić ponownie.
