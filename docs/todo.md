## `pim` `dev` `development` `integracje`

Dla każdego z integrowanych serwisów przygotować plik docker-compose, który po dodaniu do głównego docker-compose 
pimcore pozowli na sprawny rozwój i testowanie integracji. Jeśli nie jest dostępne dedykowane środowisko testowe - 
utworzyć odwzorowanie w postaci mockupu, np. klienta http.

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

## `erp` `formatowanie warunkowe`

Ustalenie formatowania warunkowego dla wybranych dokumentów:
- ZD przeterminowane - na czerwono - wymaga pilnego wyjaśnienia
- ZK możliwe do realizacjia - na niebiesko

## `wayfair` `stocki`

Integracja stanów magazynowych z wayfair

## `agata` `zamówienia`

Pobieranie zamówień z systemu MAD Agata meble

## `baselinker` `zamówienia`

Poprawnki integracji zamówień z baselinkerem

## `pim` `def` `Product`

Nazwa generyczna na podstawie parametrów produktu i jego kategorii głównej

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

## `pim` `ean`

Integracja z portalem mojegs1.pl - nadawanie kodów EAN. Skorzystać z Rabbit'a MQ. Synchronizacja kodów na podstawie Id obiektu w pim, tj. Id obiektu musi zawierać się w polu Symbol wewnętrzny w mojegs1. Skrypt PHP wyrzuca wymagane dane na kolejkę, program (python 3) odbiera wiadamość, aktualizuje dane przez API GS1 i wysyła POST request do pim'a, dzięki któremu uzupełniane jest pole EAN dla produktu lub zestawu produktów.

## `erp` `wzorce wydruków` `crystal reports`

Do aktualizacji będą prawie wszystkie wzorce wydruku


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

## `factory` `różne`

- Wydruki PDF dla zamówień i harmonogramu
- Dostęp do statycznych plików (regularnie drukowanych, np. tabela terminów dostaw dla dostawców surowców)


## `factory` `dev` `różne`

- podgląd stanów magazynowych
- podgląd dostaw produktu
- Podgląd layoutu paczki


## `factory` `etykiety`

- przegląd funkcji poprzedniego progamu, którego dokumentacja znajduję się tutaj: file://10.10.5.1/projects/IT/Dokumentacja/Etykiety/Etykiety.md
