## Zakres integracji

Integracja obejmuje:

* nadawanie kodów EAN do produktów oraz zestawów w obrębie wykupionych kodów w portalu mojegs1.pl 

## Schemat działania

1. Dodaj obiekt ``EanPool``, uzupełnij prefiks (przedrostek wykupionej puli kodów EAN). Opublikuj
2. Lista dostępnych kodów zostanie automatycznie pobrana i uzupełniona w tabeli
3. Dla produktu lub zestawu kliknij w przycisk ``Operacje > EAN13 (GTIN)``
4. Z listy z pkt. 2 zostanie automatycznie pobrany pierwszy kod, który zostanie przypisany do produktu

## TODO

* Dodać obiekt, który będzie przechowywał dane logowania / klucz do API
* Pula kodów EAN ma wskazywać na ten obiekt
* Prefix EAN powinien być generalnie tylko do odczytu, a możliwość jego nadania powinna być możliwa tylko raz
podczas tworzenia obiektu. 
* Po utworzeniu obiektu podpiąć się pod odpowiednie zdarzenie js i wyświetlić dialog z polem do wpisania prefixu.
* Po zatwierdzeniu przeprowadzić publikację puli, zapisać prefix, który w ustawieniach powinien być jako not editable
* Nie traktować połączenia z systemem gs1 jako singleton - podłączonych kont może być więcej.