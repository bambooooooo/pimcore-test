## Zakres integracji

Integracja obejumuje cykliczne dodawanie nowych zdjęć (wg daty modyfikacji pliku) z zamontowanego dysku sieciowego 
oraz próbę ich przypisania do zestawów na podstawie nazwy.

### Schemat działania

Zadanie wgrywania nowych plików realizowane jest cykliczne poprzez dedykowaną komendę ```assets:viz:upload``` wywoływaną 
zgodnie z cron (domyślnie o pełnych godzinach). Sprawdza ona które pliki zostały zmodyfikowane (utworzone) od momentu 
ostatniego wywołania. Bieżący indykator czasowy jest zapisywany do SettingsStore, czyli bezpośrednio do bazy pimcore'a.

Próba powiązania zdjęć następuje:

- po dodaniu nowego zdjęcia do folderu `/VIZ-SET`
- po utworzeniu nowego obiektu zestawu

### Mapowanie powiązań

Zdjęcia kojarzone są z zestawami jak poniżej

|     Rodzaj      |                          Wzór                          |                                Regex                                 |          Przykładowe zdjęcie |              Powiązany zestaw               |
|:---------------:|:------------------------------------------------------:|:--------------------------------------------------------------------:|-----------------------------:|:-------------------------------------------:|
| Zdjęcie główne  | `<kolekcja>`-`<kolor>`-PACKSHOT-BTP.`<nr zestawu>`.png | ```/^(\w+)-(\w+)-SET-PACKSHOT-(BTP)\.(\d{4})\.(jpg\|jpeg\|png)$/i``` |   ARCOS-BM-PACKSHOT.0014.png |                 ARCOS-BM-14                 |
|    1 zdjęcie    | `<kolekcja>`-`<kolor>`-PACKSHOT-BT.`<nr zestawu>`.png  | ```/^(\w+)-(\w+)-SET-PACKSHOT-(BT)\.(\d{4})\.(jpg\|jpeg\|png)$/i```  |   ARCOS-BM-PACKSHOT.0014.png |                 ARCOS-BM-14                 |
| kolejne zdjęcia | `<kolekcja>`-`<kolor>`-`<nr>*`-SET.`<nr zdjęcia>`.jpg  |    ```/^(\w+)-(\w+)-(\d+)-SET\.(\d{4})\.(jpg\|jpeg\|png)$/i```       |     ARCOS-BM-49-SET.0003.jpg | ARCOS-BM-34<br/>ARCOS-BM-35<br/>ARCOS-BM-49 | 

\* - Pole `<nr>` określa nr zestawu, do którego dane zdjęcie pasuje. Przykładowo zdjęcie z nr 49 pasuje do zestawów
34, 35 oraz 49 zgodnie z tabelą `viz_set` umieszczoną w pliku `/config/packages/viz.yaml`

### Dostęp do zasobów sieciowych

Zasoby połączone są w następujący sposób:

(NAS) ```\\10.10.5.1\projects```

↓ (CIFS, użytkownik z uprawnieniami tylko do odczytu)

(VM) ```/mnt/projects```

↓ (bind-mount)

(docker compose) ```/mnt/projects/RENDERS: /var/www/html/renders:ro```

↓ (symfony cli command)

(pimcore) ```/VIZ-SET```
