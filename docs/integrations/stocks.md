Stany magazynowe są zsynchronizowane w czasie rzeczywistym z Subiektem GT.
>
> Stany magazynowe są dostępne niejawnie, np. z poziomu grida. Możliwe jest dodanie operatora ekstraktora 
> ``Operator ObjectField Getter`` i ustawienie w nim pola ``Attribute`` na wartość ``Stock``, co spowoduje
> dodanie do grida nowej kolumny traktującej o stanach magazynowych.
> 

## Endpoint do aktualizacji

Aktualizacja odbywa się w paczkach, zwykle po 500 obiektów.

endpoint: ``POST`` ``/objects/stocks``

payload: tabela w postaci: ``id obiektu`` ``=>`` ``aktualny stan``

## Uwagi techniczne

Stany magazynowe realizowane są przez dedykowaną tabelę, niepowiazaną bezpośrednio z mechanizmami pim'a.
Daje to możliwość aktualizacji stanów w czasie rzeczywistym bez zbędnego tworzenia kolejnych wersji obiektu oraz
jego nadmiarowej publikacji.

Klasy ``Product`` oraz ``ProductSet`` są rozszerzone o Trait ``StockTrait`` oraz implementują
interfejs ``StockInterface``, który deklaruje metody do odczytu i zapisu stanów magazynowych w dedykowanej tabeli

Integracja realizowana jest przez skrypt pythona, który odbiera zmiany w stockach z wybranego exchange i wybranych
binding keys.




