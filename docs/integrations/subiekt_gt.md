![](https://img.shields.io/static/v1?label=Subiekt%20GT%20REST%20Bridge&message=1.0&color=blue)

## Zakres integracji

Synchronizacja jednostronna (Pimcore -> Subiekt) obejmuje:

- produkty (tylko typu ``ACTUAL``)
- zestawy
- paczki (tylko typu ``SKU``)
- kontrahentów (tylko z numerem NIP i przypisaną ofertą)

> Uwaga
> 
> Ręczna edycja danych w Subiekcie powinna nie być możliwa dla zwykłego użytkownika, ponieważ zintegrowane pola zostaną
> bezwzględnie nadpisane po kolejnej publikacji obiektu w pimcore. Możliwy jest podgląd danych w Subiekcie za pomocą
> klawisza ```F3```

## Mapowane pola

### Produkty, Zestawy, Paczki

|                  Pimcore |→| Subiekt                   |
|-------------------------:|:-:|:--------------------------|
|Id (Identyfikator obiektu) |→| Symbol                    |
|        Key (kod obiektu) |→| Nazwa                     |
|              Cena bazowa |→| Cena kartotekowa, Cena[TKW]|


### Produkty i Zestawy

|                    Pimcore | →  | Subiekt                            |
|---------------------------:|:--:|:-----------------------------------|
|Nazwa(PL)| →  | Opis                               |
|Nazwa(EN)| →  | Nazwa EN (Pole własne)             |
|Cena[Oferta]| →  | Cena[Oferta], System rabatowy LEO* |
|Zdjęcie główne (200x200)| →  | Zdjęcie                            |
|EAN| →  | Podstawowy kod kreskowy            |

> \* dodatkowe oprogramowanie LEO System Rabatowy - dodatek do Subiekta GT

### Produkty

|       Pimcore |→| Subiekt                             |
|--------------:|:--:|:------------------------------------|
| Waga produktu |→| Masa                                |
| Paczki produktu |→| Produkty wchodzące w skład kompletu |
| Szerokość |→| Szerokość (pole własne)             |
| Wysokość |→| Wysokość (pole własne)              |
| Głębokość |→| Głębokość (pole własne)             |

### Paczki

|Pimcore|→|Subiekt GT|
|--------------:|:--:|:--------|
|Key (kod obiektu)|→|Opis|
|Kod kreskowy|→|Podstawowy kod kreskowy|
|Szerokość|→|Szerokość (Pole własne)|
|Wysokość|→|Wysokość (Pole własne)|
|Długość|→|Głębokość (Pole własne)|
|Objętość|→|Objętość|
|Waga|→|Masa|

### Zestawy

|Pimcore|→|Subiekt GT|
|--------------:|:--:|:--------|
| Łączna waga paczek|→|Masa|
|Skład zestawu|→|Paczki wchodzące w skład produktów, które wchodzą w skład tego zestawu|

## Użytkownicy → Kontrahenci

| Pimcore |→| Subiekt GT |
|--------:|:--:|:-----------|
|   Nazwa |→| Symbol     |
| Nazwa |→| Nazwa |
| VAT (NIP)|→|NIP|

> Po eksporcie z pimcore zalecane jest uzupełnienie pozostałych danych przez pobranie danych z GUS - za pomocą
> dedykowanego przycisku bezpośrednio w Subiekcie

## Uwagi techniczne

### Towary

* Po publikacji generowana jest wiadomość ```ErpIndex``` z ```Id``` produktu i wysyłana do brokera (rabbitmq).
* Następnie ```ErpProductHandler``` odbiera wiadomość, pobiera dane obiektu i komunikuje się z REST API Bridge, który
aktualizuje dane przez natywne API Subiekta GT (```Sfera```) oraz bezpośrednie operacje na tabelach programu System
Rabatowy.

### Kontrahenci

* Integracja kartotek kontrahentów nie korzysta z mechanizmu kolejkowania - operacja ta jes stosunkowo rzadka i szybka.

## TODO

### Towary

* Usuwanie obiektu / Odpublikowanie w pimie --> usunięcie lub dezaktywacja w Subiekcie
* Ponowna publikacja w pimie --> aktywacja lub dodanie produktu do subiekta

### Zamówienia

* Zapytanie do REST API Bridge w celu utworzenia ZK/ZD z możliwością rozbicia na poszczególne paczki