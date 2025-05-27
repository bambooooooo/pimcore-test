<?php

/**
 * Fields Summary:
 * - WidthRange [quantityValueRange]
 * - HeightRange [quantityValueRange]
 * - DepthRange [quantityValueRange]
 */

return \Pimcore\Model\DataObject\Objectbrick\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'ProductDimensions',
   'parentClass' => '',
   'implementsInterfaces' => '',
   'title' => 'Wielkość produktu',
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
          \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValueRange::__set_state(array(
             'name' => 'WidthRange',
             'title' => 'WidthRange',
             'tooltip' => 'Zakres dla szerokości produktu',
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
             'defaultUnit' => 'mm',
             'validUnits' => 
            array (
              0 => 'mm',
            ),
             'decimalPrecision' => NULL,
             'autoConvert' => false,
             'width' => '',
          )),
          1 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValueRange::__set_state(array(
             'name' => 'HeightRange',
             'title' => 'HeightRange',
             'tooltip' => 'Zakres dla wysokości produktu',
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
             'defaultUnit' => 'mm',
             'validUnits' => 
            array (
              0 => 'mm',
            ),
             'decimalPrecision' => NULL,
             'autoConvert' => false,
             'width' => '',
          )),
          2 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValueRange::__set_state(array(
             'name' => 'DepthRange',
             'title' => 'DepthRange',
             'tooltip' => 'Zakres dla głębokości produktu',
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
             'defaultUnit' => 'mm',
             'validUnits' => 
            array (
              0 => 'mm',
            ),
             'decimalPrecision' => NULL,
             'autoConvert' => false,
             'width' => '',
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
         'labelWidth' => 180,
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
      'classname' => 'Pricing',
      'fieldname' => 'Restrictions',
    ),
  ),
   'activeDispatchingEvents' => 
  array (
  ),
));
