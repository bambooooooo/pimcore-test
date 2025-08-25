W projekcie zakłada się następujące konwencje:

- Wszystkie operacje niekrytyczne, czyli eksportowanie produktów, generowanie plików, zbiorcze przeliczenia w produktach,
czy reindeksacja muszą być procesowane z wykorzystaniem wątków roboczych i komponentu messenger realizowanego przez 
kolejki RabbitMQ (AMQP)
- Operacje wykonywane rzadko - uruchamianie nowej integracji, pobieranie puli kodów EAN - mogą być wykonane na wątku
głównym, ponieważ informacja o statusie operacji jest istotna w kontekście dalszej pracy z systemem.
- Integracje typu singleton - system ERP - należy kodować jako usługi (Services), a ich konfigurację przechowywać w zmiennych 
środowiskowych
- Integracje wielokrotne - sklepy internetowe, marketplace, portale społecznościowe, kanały wideo - należy programować
jako zbiór klas, które mogą być łatwo zarządzane przez użytkownika.
- Integrowane serwisy (np. sklep prestashop 8.2.1) powinny być opracowane jako gotowy do użycia stos docker-compose 
w dedykowanym folderze.