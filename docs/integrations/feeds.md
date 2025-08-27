# Feedy produktowe

Feedy produktowe są dodawane do Zasobów do folderu ``/STOCKS``. Możliwe jest generowanie feedów w różnych schematach
 w zależności od wymagań użytkowników.

## Generowanie feedu

```bash
php bin/console feed:generate <id oferty> <schemat> [-o <nazwa pliku wyjściowego]
```

### Przykładowe wywołanie dla feedu wg wytycznych Meb24:

```bash
php bin/console feed:generate 24657 "App\Feed\XmlFeedMeb24" -o testmeb24
```
