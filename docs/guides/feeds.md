Feedy produktowe generowane są cyklicznie, zwykle co 8h. W ramach oferty mozliwe jest generowanie wielu feedów o różnych
definicjach. Dostępne definicje feedów wyglądają jak następuje.

## CsvFeedBasic

Format: ``csv``

|  nazwa pola | opis                       | przykład                          |
|------------:|:---------------------------|:----------------------------------|
|          ``id`` | Identyfikator (Id pimcore) | 23711                             |
|     ``instock`` | Stan magazynowy            | 32                                |
|         ``key`` | Kod obiektu (Key pimcore)  | GAVI-01-CHR                       |
|       ``price`` | Cena                       | 230.0                             |
|        ``name`` | Nazwa polska               | GAVI-01 BATERIA UMYWALKOWA CHROM  |
|      ``nameEn`` | Nazwa angielska            | GAVI-01 WASHBASIN FAUCET CHROME   |

## JsonFeedBasic

Format: ```json```

| nazwa pola | opis                       | przykład                         |
|-----------:|:---------------------------|:---------------------------------|
|         ``id`` | Identyfikator (Id pimcore) | 23711                            |
|       ``name`` | Nazwa po polsku            | GAVI-01 BATERIA UMYWALKOWA CHROM |
|    ``instock`` | Stan magazynowy            | 32                               |
|      ``price`` | Cena                       | 230.0                            |

## XmlFeedBasic

Format: ```xml```

| nazwa pola | opis                       | przykład                          |
|-----------:|:---------------------------|:----------------------------------|
|         ``id`` | Identyfikator (Id pimcore) | 23711                             |
|       ``name`` | Nazwa po polsku            | GAVI-01 BATERIA UMYWALKOWA CHROM  |
|    ``instock`` | Stan magazynowy            | 32                                |
|      ``price`` | Cena                       | 230.0                             |

## XmlFeedDefault 

Format: ```xml```

|                                                nazwa pola | opis                                                  | przykład                         |
|----------------------------------------------------------:|:------------------------------------------------------|:---------------------------------|
|                                                    ``id`` | Identyfikator (Id pimcore)                            | 23711                            |
|                                                   ``sku`` | Identyfikator (Id pimcore)                            | 23711                            |
|                                               ``instock`` | Stan magazynowy                                       | 32                               |
|                                                   ``ean`` | Kod EAN (GTIN)                                        | 5903418900038                    |
|                                                  ``name`` | Nazwa polska                                          | GAVI-01 BATERIA UMYWALKOWA CHROM |
|                                                 ``serie`` | Produkt: Model<br>Zestaw: ``key`` rodzica             | GAVI-01                          |
|                                                 ``price`` | Cena                                                  | 230.0                            |
|                                              ``endprice`` | Cena referencyjna                                     | 310.0                            |
|                                              ``currency`` | Waluta                                                | PLN                              |
|                                                 ``width`` | Szerokość produktu w mm                               | 50                               |
|                                                ``height`` | Wysokość produktu w mm                                | 29                               |
|                                                 ``depth`` | Głębokość produktu w mm                               | 29                               |
|                                                ``weight`` | Waga netto                                            | 1.25                             |
|                                     ``descriptionextra1`` | Opis dodatkowy 1                                      | ...                              |
|                                     ``descriptionextra2`` | Opis dodatkowy 2                                      | ...                              |
|                                     ``descriptionextra3`` | Opis dodatkowy 3                                      | ...                              |
|                                     ``descriptionextra4`` | Opis dodatkowy 4                                      | ...                              |
|                                                ``bundle`` | Czy obiekt jest zestawem<br/>Produkt: 0<br/>Zestaw: 1 | 0                                |
|                                    ``bundle`` > ``items`` | Skład zestawu                                         |                                  |
|              ``bundle`` > ``items`` > ``item `` > ``sku`` | Identyfikator produktu w zestawie                     |                                  |
|         ``bundle`` > ``items`` > ``item `` > ``quantity`` | Ilość j.m. produktu w zestawie                        |                                  |
|                                          ``packagecount`` | Ilość paczek w zestawie                               | -                                |
|                                              ``packages`` | Paczki produktu lub produktów w zestawie              |                                  |
|                     ``packages`` > ``package`` > ``code`` | Kod kreskowy paczki                                   | 11000000000000022489             |
|                   ``packages`` > ``package`` >  ``count`` | Ilość paczek                                          | 1                                |
|                   ``packages`` > ``package`` >  ``width`` | Szerokość paczki w mm                                 | 21                               |
|                   ``packages`` > ``package`` >  ``depth`` | Długość paczki w mm                                   | 36                               |
|                   ``packages`` > ``package`` > ``height`` | Wysokość paczki w mm                                  | 8                                |
|                   ``packages`` > ``package`` > ``weight`` | Waga paczki w kg                                      | 1.25                             |
|                                                ``images`` | Zdjęcia (max. rozdzielczość)                          | ...                              |
|                                                 ``files`` | Pliki (linki)                                         | ...                              |
|                                        ``googlecategory`` | Id kategorii Google                                   | -                                |
|                                          ``manufacturer`` | Producent                                             | MEGSTYL                          |
|                                            ``parameters`` | Parametry produktu                                    | |
|                              ``parameters`` > ``allegro`` | Parametry zgodne z allegro                            | |
|               ``parameters`` > ``allegro`` > ``category`` | Kategoria produktu w Allegro                          | |
|             ``parameters`` > ``allegro`` > ``parameters`` | Parametry dla kategorii allegro                       | | 
|    ``parameters`` > ``allegro`` > ``parameters`` > ``id`` | Id parametru allegro                                  | |
|  ``parameters`` > ``allegro`` > ``parameters`` > ``name`` | Nazwa parametru allegro                               | |
| ``parameters`` > ``allegro`` > ``parameters`` > ``value`` | Wartość parametru allegro                             | |
| ``parameters`` > ``allegro`` > ``parameters`` > ``label`` | Opis wartości parametru allegro                       | |

