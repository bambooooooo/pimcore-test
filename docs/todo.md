## `pim` `Product` `DQM`

Definicja i implementacja wskaźników DQM:
- wspólnych dla wszystkich produktów / zestawów
- zależnych od kategorii produktu / zestawu

## `pim` `deepl`

Integracja tłumaczeń kolejnych obiektów - kategorii, zestawów, parametrów

## `pim` `sklep`

Integracja produtków, zdjęć, kategorii, parametrów z prestashop 8.2.0

## `erp` `zamówienia`

Poprawki w integracji zamówień Subiekt'a GT:
- wyjątek mówiący o braku kodu produktu nie powinien być krytyczny (aplikacja powinna dalej obsługiwać wiadomości)
- tłumaczenia ładowane z pożądnej bazy, a nie z pliku
- automatyczne montowanie kompletu, jeśli jest to możliwe

## `agata` `zamówienia`

Pobieranie zamówień z systemu MAD Agata meble

## `wypłaty`
- przepisanie aplikacji na web (vue.js)
- na podstawie dokumentacji: file://10.10.5.1/projects/IT/Dokumentacja/Kalkulator%20godzin%20pracy/Kalkulator.md

## `pim` `elementy produktu`

Dodawanie rysunków technicznych elementów produktów

## `sklep` `promocje`

Moduł, który będzie cyklicznie dodawał promocje na losowo wybrane produkty.

## `pim``layout paczki`

- aplikacja, która pozwala na szybką edycję rozmieszczenia elementów w poszczególnych wartstwach paczki
- możliwość dodawania formatek (najczęściej styropian) do wypełniania przestrzeni w paczce


## `pim``maintenance`

1. Produkty bez parametrów - Lista produktów ```ACTUAL```, która nie ma lub nie rozszerza odziedziczonych po obiektach ```VIRTUAL``` zbiorów parametrów
1. DQM - produkty ```ACTUAL``` i ich wskaźniki uzupełnienia DQM

## `pim` `zlecenie` `wydruk`

Wydruk PDF
Podgląd HTML

## `pim` `reklamacje`

Prosty system dodawania i zaznaczania reklamacji (zlecenia)
Wydruk dokumentu "WZ"

## `pim` `katalogi`

Mechanizm do efektywnego generowania katalogów:
- html
- pdf (web - mały rozmiar)
- pdf (do druku)

Wykorzystanie relacji pomiędzy grupami w katalogu

## `pim` `waluty` `update`

Aktualizacja kursów walut na życzenie - aktualizacja produktów - zmiana ceny w obcej walucie

## `pim` `ec` `opis`

Długi opis produktu w standardzie allegro (wiersze po 1 lub 2 sekcje tekstu / zdjęć)

## `erp` `sandbox`

Docker + Docker compose, który umożliwi development integracji z subiektem gt. Kopia bazy produkcyjnej.

## `pim` `role` `opis`

Opisanie uprawnień przypisanych do danej roli

## `pim` `pdf` `karta zestawu`

Karta zestawu przygotowana do wyruku w formie PDF'a

## `pim` `llm` `kolekcje`

Automatyczne tworzenie opisu kolekcji o wybranej długości

## `factory` `cache`

Dodać obsługę cache.

## `factory` `harmonogram`

Przepisać w taki sposób, aby możliwe było aktualizowanie treści bez konieczności przeładowywania. Co stały interwał
wysyłać zapytania, czy zamówienia uległy zmianie (czy zmienił się ich czas modyfikacji), a jeśli tak, to pobrać tylko
zmienione dane.

## `factory` `różne`

Dodać konfigurator dla własnych checkboxów - kolor, tooltip, kolejność.

## `factory` `wydruki`

- Wydruki PDF dla zamówień i harmonogramu
- Dostęp do statycznych plików (regularnie drukowanych, np. tabela terminów dostaw dla dostawców surowców)


## `factory` `dev` `różne`

- podgląd stanów magazynowych
- podgląd dostaw produktu
- Podgląd layoutu paczki


## `factory` `etykiety`

- przegląd funkcji poprzedniego progamu, którego dokumentacja znajduję się tutaj: file://10.10.5.1/projects/IT/Dokumentacja/Etykiety/Etykiety.md

## `stocki` `webhook` `integracja`

- wdrożyć webhook'i dla klientów, które będą aktualizować stany magazynowe w czasie rzeczywistym