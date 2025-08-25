## Dodawanie nowego produktu

W celu dodania nowego produktu należy:

1. Znaleźć odpowiednie miejsce w drzewie ```/PRODUKTY```
1. Dodać nowy produkt typu ```ACTUAL```, a jeśli to zasadne dodać również produkty pośrednie (typu ``VIRTUAL``, ``MODEL``, ``SKU``)
1. Uzupełnić wymagane dane
1. Opublikować produkt

### Nadawanie kodu EAN

W celu przypisania kodu EAN do danego produktu należy skorzystać z opcji ``Operacje > Kod EAN (GTIN)``

### Tłumaczenie nazwy produktu

Nazwy są tłumaczone z języka domyślnego dla zalogowanego użytkownika na wszystkie pozostałe, które są dostępne w systemie.

W celu przetłumaczenia nazwy produktu lub zestawu należy uzupełnić nazwę w domyślnym języku, a następnie kliknąć opcję 
``Operacje > Tłumacz nazwę``

### Dodatkowe kody odbiorców

Specyficzne dla danego odbiorcy kody produktów należy uzupełnić w zakładce ``Kody > Dodatkowe kody``

## Automatyczne akcje

### Obliczanie danych logistycznych

Na podstawie uzupełnionych danych paczek produktów wyznaczane jest:

- łączna objętość paczek
- łączna waga paczek
- łączna ilość paczek
- wielkość serii (NWW z ilości paczek na domyślnym nośniku, np. palecie EURO)

### Wyznaczanie cen

#### Cena bazowa

Cena bazowa wyznaczana jest automatycznie po publikacji na podstawie cen bazowych paczek i ich ilości

#### Wyceny

Wyceny są obliczane automatycznie po publikacji na podstawie dostępnych wycen i ich wytycznych

Dostępne są następujące ograniczenia dla wycen:

- Cena bazowa - cena bazowa produktu musi zawierać się w zdefiniowanym przedziale
- Rozmiar paczki - rozmiar każdej z paczek musi zawierać się w zdefiniowanym przedziale
- Obwód paczki - "obwód" (długość + szerokość + wysokość)  każdej z paczek musi zawierać się w zdefiniowanym przedziale
- Waga paczki - waga każdej z paczek musi zawierać się w zdefiniowanym przedziale
- Minimalna marża - marża względem ceny bazowej nie może być niższa niż wskazany próg
- Minimalny narzut - narzut względem ceny bazowej nie może być niższy niż wskazany próg
- Minimalny zysk - zysk względem ceny bazowej nie może być niższy niż wskazany próg
- Kraj pochodzenia produktu
- Wymiary produktu
- Wybrane grupy - produkt musi należeć do dowolonej ze wskazanych grup

Po spełnieniu wskazanych wyżej ograniczeń w ramach danej wyceny możliwe jest już wyznaczanie cen. Do tego celu 
definiuje się ścisła listę kroków, które należy wykonać, aby otrzymać końcową cenę w zdefiniowanej walucie. Każdy
kolejny krok bazuje na bieżącej cenie wyznaczonej w kroku poprzednim

Dostępne są następujące kroki obliczeń:

- Mnożnik - mnoży cenę przez określoną wartość
- Waga i objętość paczek - dodaje wartość z tabeli (waga - objętość) w zależności od trybu:
  - łącznie dla wszystkich paczek
  - dla każdej z paczki osobno
- Objętość paczek - dodaje wartość transportu za 1m3 przemnożoną przez łączną objętość paczek
- Kwota - dodaje stałą wartość z uzasadnieniem, np. koszt pakowania paczki lub dopłata strefowa do zamówienia
- Inna wycena - dodaje końcową wartość innej wyceny, np. koszt transportu detalicznego w danym kraju
- Operacja na zbiorze wycen - funkcja na zbiorze wycen (np. kurierzy w danym kraju), która w zależności od operatora zwraca:
  - minimalną wartość
  - maksymalną wartość
  - średnią

#### Oferty

Oferty w ramach produktu lub zestawu są obliczane automatycznie na podstawie wycen przypisanych do definicji oferty. 
Pierwsza wycena spełniająca swoje ograniczenia w ramach oferty jest uznawana za końcową ofertę dla danego produktu.

Istnieje możliwość ręcznej edycji oferty poprzez zaznaczenie pola ```IsFixed```

