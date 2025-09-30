## Zakres integracji

Synchronizacja jednostronna z Pimcore do Baselinkera obejmuje:

* produkty
* zestawy
* katalogi
* grupy cenowe

## Mapowane pola

### Produkt / Zestaw

|           Pimcore | →  | Baselinker            |
|------------------:|:--:|:----------------------|
|            Nazwa* |→| Nazwa produktu*       |
| Key (kod obiektu) |→| SKU                   |
|               EAN |→| EAN                   |
|              Waga |→| Waga                  |
|      Cena[Oferta] |→| Cena[Grupa cenowa]    |
|       Cena bazowa |→| Cena zakupu (średnia) |
| Parametry Allegro |→| Parametry[Allegro]    |
|   Opis {1,2,3,4}* |→| Opis {1,2,3,4}*       |

> \* - obsługa języków zdefioniowanych w parametrach danego katalogu

### Produkt

|                            Pimcore |→| Baselinker |
|-----------------------------------:|:-:|:-----------|
|                          Szerokość |→| Szerokość  |
|                           Wysokość |→| Wysokość   |
|                          Głębokość |→| Głębokość  |
|                     Zdjęcie główne |→| Zdjęcia    |
| Zdjęcia (dopełniając do ilości 16) |→| Zdjęcia    |
|                          Aranżacje |→| Zdjęcia    |
|         Zdjęcia wspólne dla modelu |→| Zdjęcia    |

### Zestaw

|                                                                     Pimcore |→| Baselinker |
|----------------------------------------------------------------------------:|:-:|:-----------|
|                                                               Skład zestawu |→| Produkty   |
|                                                              Zdjęcie główne |→| Zdjęcia               |
|                                          Zdjęcia (dopełniając do ilości 16) |→| Zdjęcia               |
| (Zdjęcie główne, Pierwsze zdjęcie modelu)<br/> dla każdego składika zestawu |→| Zdjęcia               |
|                                                  Zdjęcia wspólne dla modelu |→| Zdjęcia               |

### Katalog

| Pimcore | → | Baselinker     |
|--------:|:-:|:---------------|
|   Nazwa | → | Nazwa          |
|    Opis | → | Opis           |
|   Język | → | Język domyślny |
|  Języki | → | Języki         |
| Oferty | → | Grupy cenowe   |

## Oferta → Grupa cenowa

| Pimcore | → | Baselinker     |
|--------:|:-:|:---------------|
|   Nazwa | → | Nazwa          |

## Schemat działania

1. Dodaj obiekt ```Baselinker```, uzupełnij klucz API, opublikuj. Pimcore zweryfikuje poprawność połącznia z API.
1. W wybranych ofertach uzupełnij pole ``Baselinker`` referencją do obiektu z pkt 1. Opublikuj ofertę. W baselinker
zostanie utworzona ``Grupa cenowa``, a jej ``Id`` zostanie zapisane jako ```BaselinkerPriceGroupId```.
1. Dodaj obiekt ```BaselinkerCatalog```, uzupełnij pole ```Baselinker``` referencją do obiektu z pkt. 1 oraz pole 
``Offer`` referencjami do powiązanych z Baselinkerem Ofert w pkt. 2. Opublikuj. Do konta baselinkera zostanie 
dodany katalog produktu, a jego Id zostanie zapisane w polu ``CatalogId``.
1. Publikacja produktu / zestawu, który zawiera co najmniej jedną ofertę połączoną z danym katalogiem baselinkera 
automatycznie doda go do kolejki integracji baselinker
1. Program działający w tle odbierze zadanie z kolejki i zaktualizuje dane w Baselinker

## Uwagi techniczne

* Po publikacji generowana jest wiadomość typu ``BlkIndex``, którą obsługuje ``BlkProductHandler``. 
* Zakładana jest blokada ``obj_[id obiektu]`` realizując jednoczesny dostęp dla co najwyżej jednego workera. 
* Przed wysłaniem zapytania do API sprawdzana jest suma kontrolna wersji (hash zawartości zapytania do API). 
* Jeśli produkt został już dodany i suma jest znana - system nie wysyła żadania, w przeciwnym wypadku 
wysyłane jest żądanie z pełną informacją o produkcie.
* Zdjęcia są redukowane do maksymalnego rozmiaru 2MB poprzez generowanie miniaturek w coraz mniejszej rozdzielczości