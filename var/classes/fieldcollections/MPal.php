<?php

/**
 * Fields Summary:
 * - Price [quantityValue]
 */

return \Pimcore\Model\DataObject\Fieldcollection\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'MPal',
   'parentClass' => '',
   'implementsInterfaces' => '',
   'title' => '+ Cena za MPAL (Miejsce paletowe)',
   'group' => 'Pricing',
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
          \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
             'name' => 'Price',
             'title' => 'Price',
             'tooltip' => 'Cena za miejsce paletowe, czyli powierzchnia załadunku palety EURO 1,2 m x 0,8 m (0,96 m2) liczona zgodnie z regulaminem dostaw krajowych DB Schenker:

2|16. MIEJSCE PALETOWE (MPAL) DO WYCENY – powierzchnia zajmowana przez jedną
jednostkę logistyczną podatną do przeładunku mechanicznego, obliczona zgodnie z regułami
określonymi w powyższej definicji miejsca paletowego, z zastrzeżeniem że każdy z wymiarów
(długość / szerokość) jest zaokrąglany w górę do wielokrotności 0,2 m. Obliczone w ten sposób
wartości zaokrąglane są do góry, z dokładnością do dwóch miejsc po przecinku.',
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
             'unitWidth' => '',
             'defaultUnit' => 'PLN',
             'validUnits' => 
            array (
              0 => 'PLN',
            ),
             'unique' => false,
             'autoConvert' => false,
             'defaultValueGenerator' => '',
             'width' => '',
             'defaultValue' => NULL,
             'integer' => false,
             'unsigned' => false,
             'minValue' => NULL,
             'maxValue' => NULL,
             'decimalSize' => NULL,
             'decimalPrecision' => NULL,
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
));
