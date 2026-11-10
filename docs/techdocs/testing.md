# Testy automatyczne

Wszystkie testy uruchamia jedno polecenie (wymaga działającego ``docker compose up -d``):

```
bin/run-tests                       # wszystkie testy PHP + JS
bin/run-tests --fresh               # najpierw odtwarza testową bazę (po zmianie klas lub bundli)
bin/run-tests --php Unit            # tylko wybrany zestaw Codeception (Unit, Integration, Functional)
bin/run-tests --php Functional:StockApiCest
bin/run-tests --js                  # tylko testy JS (Vitest)
bin/run-tests --dump-reference-data # odświeża dane referencyjne z bazy deweloperskiej
```

## Testowa baza danych

Testy nigdy nie dotykają bazy ``db``. Używają osobnego kontenera ``test_db`` (profil ``test`` w
``docker-compose.yaml``), którego dane trzymane są w pamięci (tmpfs). Przy pierwszym uruchomieniu
``bin/run-tests``:

1. instaluje pustego Pimcore w bazie ``pimcore_test`` (``tests/Support/bin/init-test-db.php``),
2. instaluje bundle i tworzy tabele klas z ``var/classes``,
3. tworzy tabelę ``stocks`` i ładuje dane referencyjne (``tests/Support/Data/reference_data.sql``:
   jednostki, definicje classification store).

Baza żyje do zatrzymania kontenera ``test_db``. Moduł ``App\Tests\Support\Helper\Pimcore`` odmawia
pracy na innej bazie niż ``test_db/*_test``.

Rejestracja Pimcore 12 (``PIMCORE_INSTANCE_IDENTIFIER``, ``PIMCORE_ENCRYPTION_SECRET``,
``PIMCORE_PRODUCT_KEY``) jest kopiowana z ``.env.local`` do ``.env.test.local`` automatycznie.

## Zestawy testów

| Zestaw | Katalog | Co testuje |
|---|---|---|
| Unit | ``tests/Unit`` | pojedyncze klasy bez kernela i bazy (feedy, operatory grida, Twig, PDF, bezpieczeństwo) |
| Integration | ``tests/Integration/<Integracja>`` | integracje z systemami zewnętrznymi na zamockowanym HTTP |
| Functional | ``tests/Functional`` | pełny kernel Symfony/Pimcore na ``test_db``: kontrakt URL-i, uprawnienia, API |
| JS | ``tests/js`` | skrypty panelu administracyjnego z ``public/static/js`` (Vitest + jsdom) |

### Integracje a paczki composera

Każda integracja ma własny katalog w ``tests/Integration`` (Allegro, Baselinker, Broker, DeepL, GS1,
Optimik, Prestashop, SubiektGT). Testy zależą tylko od klasy integracji i ``MockHttpClient`` - przy
wydzielaniu integracji do paczki composera katalog przenosi się razem z kodem, zmieniając jedynie
namespace. Nowa integracja = nowy katalog, nigdy prawdziwe połączenia sieciowe.

### Fixtury w testach funkcjonalnych

```php
$product = $I->haveDataObject('Product', published: false); // tworzony w /_tests, usuwany po teście
$I->amLoggedInAsPimcoreAdmin();                             // sesja jak w panelu administracyjnym
$I->haveValidApiToken();                                    // nagłówek X-Api-Token klienta testowego
$I->sendJson('POST', '/objects/stocks', [$product->getId() => 5]);
```

Wiadomości messengera w testach trafiają do transportów ``in-memory://`` - nic nie wychodzi do RabbitMQ.

## Publiczne linki (kontrakt URL-i)

Linki do Pimcore są wysyłane klientom, zapisane w zakładkach, feedach i integracjach - muszą działać
zawsze. Każdy route kontrolera ``App\`` musi być opisany w ``tests/Support/Data/url_contract.yaml``
(przykładowy URL, poziom dostępu, kto z niego korzysta). Test ``UrlContractCest`` pilnuje, że:

* każdy nowy route jest sklasyfikowany,
* każdy opisany URL nadal trafia w ten sam route,
* poziom dostępu się zgadza (``public``, ``protected``, ``login``),
* każde przekierowanie odpowiada ``301`` na istniejący route z zachowaniem parametrów.

### Przeniesienie lub zmiana nazwy URL-a

1. Zmień route i zaktualizuj ``path`` w ``url_contract.yaml``.
2. Dodaj przekierowanie ze starego adresu w ``config/routes/legacy_redirects.yaml``
   (nazwa route'a zaczyna się od ``legacy_``, kontroler ``RedirectController``, ``permanent: true``).
3. Dopisz stary adres w sekcji ``redirects`` kontraktu. Wpisów nigdy się nie usuwa.

Testy JS sprawdzają dodatkowo, że każdy URL wywoływany przez panel administracyjny (Ajax, store,
``window.open``, ``Routing.generate``) istnieje w aplikacji - przeniesienie endpointu bez aktualizacji
skryptu kończy się błędem testu.

## Endpointy zmieniające dane

Akcje kontrolerów oznaczone atrybutem ``#[ProtectedEndpoint]`` wymagają sesji administratora Pimcore
(panel administracyjny wysyła ją automatycznie) albo nagłówka ``X-Api-Token``. Tokeny systemów
zewnętrznych ustawia się w ``.env.local`` (produkcja: sekret w deployu):

```
API_TOKENS=subiekt:dlugi-losowy-token,optimik:inny-token
```

Bez sesji i tokenu endpoint zwraca ``401``. Adresy URL się nie zmieniają.
