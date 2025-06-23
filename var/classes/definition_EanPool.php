<?php

/**
 * Inheritance: no
 * Variants: no
 * Title: Póla kodów EAN
 * Pula kodów EAN wykupiona w systemie GS1
 *
 * Fields Summary:
 * - AvailableCodes [table]
 * - Prefix [input]
 */

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'EanPool',
   'name' => 'EanPool',
   'title' => 'Póla kodów EAN',
   'description' => 'Pula kodów EAN wykupiona w systemie GS1',
   'creationDate' => NULL,
   'modificationDate' => 1745820310,
   'userOwner' => 2,
   'userModification' => 2,
   'parentClass' => '',
   'implementsInterfaces' => '',
   'listingParentClass' => '',
   'useTraits' => '',
   'listingUseTraits' => '',
   'encryption' => false,
   'encryptedTables' =>
  array (
  ),
   'allowInherit' => false,
   'allowVariants' => false,
   'showVariants' => false,
   'layoutDefinitions' =>
  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
     'name' => 'pimcore_root',
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
      \Pimcore\Model\DataObject\ClassDefinition\Layout\Tabpanel::__set_state(array(
         'name' => 'Layout',
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
             'name' => 'Layout',
             'type' => NULL,
             'region' => NULL,
             'title' => 'System',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' =>
            array (
              0 =>
              \Pimcore\Model\DataObject\ClassDefinition\Data\Table::__set_state(array(
                 'name' => 'AvailableCodes',
                 'title' => 'Available Codes',
                 'tooltip' => 'Dostępne do przypisania kodu',
                 'mandatory' => false,
                 'noteditable' => true,
                 'index' => false,
                 'locked' => false,
                 'style' => 'float: right; margin: 8px;',
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => false,
                 'visibleSearch' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'cols' => 1,
                 'colsFixed' => true,
                 'rows' => NULL,
                 'rowsFixed' => false,
                 'data' => '',
                 'columnConfigActivated' => true,
                 'columnConfig' =>
                array (
                  0 =>
                  array (
                    'key' => 'GTIN',
                    'label' => 'GTIN',
                  ),
                ),
                 'height' => '',
                 'width' => 320,
              )),
              1 =>
              \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                 'name' => 'Prefix',
                 'title' => 'Prefix',
                 'tooltip' => 'Prefix z systemu mojegs1.pl służący do nadawania kolejnych kodów GTIN dla produktów',
                 'mandatory' => true,
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
                 'columnLength' => 12,
                 'regex' => '^[0-9]{7,12}$',
                 'regexFlags' => 
                array (
                ),
                 'unique' => true,
                 'showCharCount' => true,
                 'width' => '',
                 'defaultValueGenerator' => '',
              )),
              2 =>
              \Pimcore\Model\DataObject\ClassDefinition\Layout\Text::__set_state(array(
                 'name' => 'Layout',
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
                ),
                 'locked' => false,
                 'blockedVarsForExport' =>
                array (
                ),
                 'fieldtype' => 'text',
                 'html' => '<h1>Prefix</h1>

Wykupiona w portalu mojegs1.pl pula kodów EAN w wielkości:
<ul>
<li>
10 - 110,57 zł
</li>
<li>
100 - 250,00 zł
</li>
<li>
1 000 - 500,00 zł
</li>
<li>
10 000 - 1 000,00 zł
</li>
<li>
100 000 - 3 000,00 zł
</li>
</ul>

<p>
Ceny aktualne na dzień 10.04.2025
</p>

<p>
Po wykorzystaniu puli należy wykupić kolejną, aby możliwe było nadawanie kolejnych kodów GTIN.
</p>

<h1>Lista kodów</h1>

Lista dostępnych do wykorzystania kodów znajduje się w tabeli po prawo.<br>

Dla nowej puli należy uzupełnić listę kodów, którą można wygenerować na stronie <a href="https://mojegs1.pl/moje-produkty/wolne-numery">https://mojegs1.pl/moje-produkty/wolne-numery</a>',
                 'renderingClass' => '',
                 'renderingData' => '',
                 'border' => false,
              )),
            ),
             'locked' => false,
             'blockedVarsForExport' =>
            array (
            ),
             'fieldtype' => 'panel',
             'layout' => NULL,
             'border' => false,
             'icon' => '',
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
     'icon' => NULL,
     'labelWidth' => 100,
     'labelAlign' => 'left',
  )),
   'icon' => '/UI/barcode.svg',
   'group' => '',
   'showAppLoggerTab' => false,
   'linkGeneratorReference' => '',
   'previewGeneratorReference' => '',
   'compositeIndices' =>
  array (
  ),
   'showFieldLookup' => false,
   'propertyVisibility' =>
  array (
    'grid' =>
    array (
      'id' => true,
      'key' => true,
      'path' => false,
      'published' => true,
      'modificationDate' => false,
      'creationDate' => false,
    ),
    'search' =>
    array (
      'id' => true,
      'key' => true,
      'path' => false,
      'published' => true,
      'modificationDate' => false,
      'creationDate' => false,
    ),
  ),
   'enableGridLocking' => false,
   'deletedDataComponents' =>
  array (
  ),
   'blockedVarsForExport' =>
  array (
  ),
   'fieldDefinitionsCache' =>
  array (
  ),
   'activeDispatchingEvents' =>
  array (
  ),
));
