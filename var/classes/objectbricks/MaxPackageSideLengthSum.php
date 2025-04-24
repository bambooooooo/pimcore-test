<?php

/**
 * Fields Summary:
 * - Limit [quantityValue]
 */

return \Pimcore\Model\DataObject\Objectbrick\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'MaxPackageSideLengthSum',
   'parentClass' => '',
   'implementsInterfaces' => '',
   'title' => 'Maksymalny obwód paczki',
   'group' => '',
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
             'name' => 'Limit',
             'title' => 'Limit',
             'tooltip' => '',
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
             'unitWidth' => '',
             'defaultUnit' => 'mm',
             'validUnits' => 
            array (
              0 => 'mm',
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
          1 => 
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
             'html' => '<h4>
Suma długości boków paczki nie może być większa niż wskazany limit.
</h4>
Przykładowo paczka o wymiarach 2 000 mm x 450 mm x 120 mm ma sumę długości równą 2570 mm, co jest mniejsze niż 3000 mm',
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
      'classname' => 'Parcel',
      'fieldname' => 'Restrictions',
    ),
  ),
   'activeDispatchingEvents' => 
  array (
  ),
));
