<?php

/**
 * Fields Summary:
 * - Barcode [input]
 * - Code [input]
 * - Name [input]
 * - Codes [advancedManyToManyObjectRelation]
 */

return \Pimcore\Model\DataObject\Objectbrick\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'IndexAgata',
   'parentClass' => '',
   'implementsInterfaces' => '',
   'title' => 'Agata meble',
   'group' => 'Dodatkowe kody',
   'layoutDefinitions' => 
  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
     'name' => NULL,
     'type' => NULL,
     'region' => NULL,
     'title' => NULL,
     'width' => 0,
     'height' => 0,
     'collapsible' => false,
     'collapsed' => false,
     'bodyStyle' => NULL,
     'datatype' => 'layout',
     'children' => 
    array (
      0 => 
      \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
         'name' => 'Layout',
         'type' => NULL,
         'region' => NULL,
         'title' => 'Agata meble',
         'width' => '',
         'height' => '',
         'collapsible' => false,
         'collapsed' => false,
         'bodyStyle' => '',
         'datatype' => 'layout',
         'children' => 
        array (
          0 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Tabpanel::__set_state(array(
             'name' => 'Wystrój',
             'type' => NULL,
             'region' => NULL,
             'title' => '',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                 'name' => 'Wystrój',
                 'type' => NULL,
                 'region' => NULL,
                 'title' => 'Produkt / Paczka produktu',
                 'width' => '',
                 'height' => '',
                 'collapsible' => false,
                 'collapsed' => false,
                 'bodyStyle' => '',
                 'datatype' => 'layout',
                 'children' => 
                array (
                  0 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                     'name' => 'Barcode',
                     'title' => 'Barcode (Package)',
                     'tooltip' => 'Kod kreskowy paczki w systemie Agata. 

Zwykle w formacie EAN13, przykładowo:
- 2011009795512
- 2011009795529
- 2011009795536',
                     'mandatory' => false,
                     'noteditable' => false,
                     'index' => false,
                     'locked' => false,
                     'style' => '',
                     'permissions' => NULL,
                     'fieldtype' => '',
                     'relationType' => false,
                     'invisible' => false,
                     'visibleGridView' => false,
                     'visibleSearch' => false,
                     'blockedVarsForExport' => 
                    array (
                    ),
                     'defaultValue' => NULL,
                     'columnLength' => 190,
                     'regex' => '',
                     'regexFlags' => 
                    array (
                    ),
                     'unique' => false,
                     'showCharCount' => false,
                     'width' => '',
                     'defaultValueGenerator' => '',
                  )),
                  1 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                     'name' => 'Code',
                     'title' => 'Index (Product or Package)',
                     'tooltip' => 'Kod w sytemie Agata - produkt lub paczka

Przykładowe kody produktów:
- 2720-010-007-009-0001
- 2720-010-007-009-0002
- 2720-010-007-010-0001

Przykładowe kody paczek:
- P_979551
- P_979552
- P_979553
',
                     'mandatory' => false,
                     'noteditable' => false,
                     'index' => false,
                     'locked' => false,
                     'style' => '',
                     'permissions' => NULL,
                     'fieldtype' => '',
                     'relationType' => false,
                     'invisible' => false,
                     'visibleGridView' => false,
                     'visibleSearch' => false,
                     'blockedVarsForExport' => 
                    array (
                    ),
                     'defaultValue' => NULL,
                     'columnLength' => 190,
                     'regex' => '',
                     'regexFlags' => 
                    array (
                    ),
                     'unique' => false,
                     'showCharCount' => false,
                     'width' => '',
                     'defaultValueGenerator' => '',
                  )),
                  2 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                     'name' => 'Name',
                     'title' => 'Name (Product)',
                     'tooltip' => 'Nazwa produktu w systemie Agata

Przykładowo:
- LEVEL-01-DA BIURKO Z REGULACJĄ WYSOKOŚCI DĄB ARISTAN
- ROSARIO RTV 2K Z KOMINKIEM 04-CAS KASZMIR
- BLAT 06 BIAŁY MAT',
                     'mandatory' => false,
                     'noteditable' => false,
                     'index' => false,
                     'locked' => false,
                     'style' => '',
                     'permissions' => NULL,
                     'fieldtype' => '',
                     'relationType' => false,
                     'invisible' => false,
                     'visibleGridView' => false,
                     'visibleSearch' => false,
                     'blockedVarsForExport' => 
                    array (
                    ),
                     'defaultValue' => NULL,
                     'columnLength' => 190,
                     'regex' => '',
                     'regexFlags' => 
                    array (
                    ),
                     'unique' => false,
                     'showCharCount' => false,
                     'width' => '',
                     'defaultValueGenerator' => '',
                  )),
                ),
                 'locked' => false,
                 'blockedVarsForExport' => 
                array (
                ),
                 'fieldtype' => 'panel',
                 'layout' => NULL,
                 'border' => false,
                 'icon' => 'http://localhost/bundles/pimcoreadmin/img/flat-color-icons/object.svg',
                 'labelWidth' => 200,
                 'labelAlign' => 'left',
              )),
              1 => 
              \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                 'name' => 'Wystrój',
                 'type' => NULL,
                 'region' => NULL,
                 'title' => 'Paczka zależna od produktu',
                 'width' => '',
                 'height' => '',
                 'collapsible' => false,
                 'collapsed' => false,
                 'bodyStyle' => '',
                 'datatype' => 'layout',
                 'children' => 
                array (
                  0 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
                     'name' => 'Codes',
                     'title' => 'Codes',
                     'tooltip' => 'Dla paczek - kody w systemie Agata zależne od produktu.

Ta sama paczka może być sprzedawana (wydawana z magazynu) pod różnymi kodami SKU. 

Przykładowo:
Paczka z kominkiem 32" może być traktowana zarówno jako produkt pełnowartościowy (1-paczkowy) oraz jako paczka nr 3 w komodzie wybranej kolekcji. Z tego powodu należy rozróżnić dodatkowe SKU oraz kody kreskowe w ramach tej samej paczki, która w ERP widnieje jako ten sam indeks.',
                     'mandatory' => false,
                     'noteditable' => false,
                     'index' => false,
                     'locked' => false,
                     'style' => '',
                     'permissions' => NULL,
                     'fieldtype' => '',
                     'relationType' => true,
                     'invisible' => false,
                     'visibleGridView' => false,
                     'visibleSearch' => false,
                     'blockedVarsForExport' => 
                    array (
                    ),
                     'classes' => 
                    array (
                    ),
                     'displayMode' => NULL,
                     'pathFormatterClass' => '',
                     'maxItems' => NULL,
                     'visibleFields' => 'key,id',
                     'allowToCreateNewObject' => false,
                     'allowToClearRelation' => true,
                     'optimizedAdminLoading' => false,
                     'enableTextSelection' => false,
                     'visibleFieldDefinitions' => 
                    array (
                    ),
                     'width' => '',
                     'height' => '',
                     'allowedClassId' => 'Product',
                     'columns' => 
                    array (
                      0 => 
                      array (
                        'type' => 'text',
                        'position' => 1,
                        'key' => 'Code',
                        'label' => 'Kod systemowy paczki',
                      ),
                      1 => 
                      array (
                        'type' => 'text',
                        'position' => 2,
                        'key' => 'Barcode',
                        'label' => 'Kod kreskowy paczki',
                      ),
                    ),
                     'columnKeys' => 
                    array (
                      0 => 'Code',
                      1 => 'Barcode',
                    ),
                     'enableBatchEdit' => false,
                     'allowMultipleAssignments' => false,
                  )),
                ),
                 'locked' => false,
                 'blockedVarsForExport' => 
                array (
                ),
                 'fieldtype' => 'panel',
                 'layout' => NULL,
                 'border' => false,
                 'icon' => '/UI/package-virtual.svg',
                 'labelWidth' => 100,
                 'labelAlign' => 'left',
              )),
            ),
             'locked' => false,
             'blockedVarsForExport' => 
            array (
            ),
             'fieldtype' => 'tabpanel',
             'border' => false,
             'tabPosition' => 'top',
          )),
        ),
         'locked' => false,
         'blockedVarsForExport' => 
        array (
        ),
         'fieldtype' => 'panel',
         'layout' => NULL,
         'border' => false,
         'icon' => '/LOGO/agatameble_logotyp.jpg',
         'labelWidth' => 100,
         'labelAlign' => 'left',
      )),
    ),
     'locked' => false,
     'blockedVarsForExport' => 
    array (
    ),
     'fieldtype' => 'panel',
     'layout' => NULL,
     'border' => false,
     'icon' => NULL,
     'labelWidth' => 100,
     'labelAlign' => 'left',
  )),
   'fieldDefinitionsCache' => NULL,
   'blockedVarsForExport' => 
  array (
  ),
   'activeDispatchingEvents' => 
  array (
  ),
   'classDefinitions' => 
  array (
    0 => 
    array (
      'classname' => 'Product',
      'fieldname' => 'Codes',
    ),
    1 => 
    array (
      'classname' => 'Package',
      'fieldname' => 'Codes',
    ),
  ),
   'activeDispatchingEvents' => 
  array (
  ),
));
