## Zakres integracji

Integracja obejmuje:

* integracje kategorii produktu

Na podstawie katagorii zawartych pod adresem [taxonomy-with-ids.pl-PL.txt](https://www.google.com/basepages/producttype/taxonomy-with-ids.pl-PL.txt)
pobierana jest określona lista kategorii końcowych (liści drzewa) i zapisywana w dedykowanym ```SelectOptions``` o nazwie
```GoogleCategory```.

```bash
google:category:update "Sprzęt > Hydraulika > Armatura wodociągowa i części >" "Meble >"
```