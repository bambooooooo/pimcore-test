# Zestaw produktów (ProductSet)

Zbiór produktów w określonej ilości

## Publikacja zestawu

### Walidacja

1. Zestaw musi posiadać nazwę co najmniej w języku polskim
2. Zestaw musi posiadać produkty, które są opublikowane i przypisane w ilości większej niż ``0``

### Akcje

1. Sumowanie wag produktów składowych - netto (wagi produktów) i brutto (wagi paczek produktów)
1. Sumowanie objętości paczek produktów
1. Wyznaczanie cen wysyłek kurierskich na podstawie paczek produtków składowych
1. Sumowanie cen bazowych produktów składowych
1. Tłumaczenie nazw na pozostałe języki na podstawie nazwy polskiej
1. Publikacja w RabbitMQ ```PRD:product``` - głównie w celu dodania do systemu ERP 

## Dodatkowe funkcje

- Eksport zdjęć (ze zdjęciami produktów składowych włącznie) do archiwum .zip

![export images](./export-images.png)

- Nadawanie kodu EAN z pierwszej dostępnej puli ```EanPool```

![add ean](./add-ean.png)