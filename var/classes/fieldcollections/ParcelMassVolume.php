<?php

/**
 * Fields Summary:
 * - Mode [select]
 * - Prices [table]
 */

return \Pimcore\Model\DataObject\Fieldcollection\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'ParcelMassVolume',
   'parentClass' => '',
   'implementsInterfaces' => '',
   'title' => '+ Waga i objętość paczek',
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
          \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
             'name' => 'Mode',
             'title' => 'Mode',
             'tooltip' => '',
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
             'options' => 
            array (
              0 => 
              array (
                'key' => 'Per parcel',
                'value' => 'PARCEL',
              ),
              1 => 
              array (
                'key' => 'Per package',
                'value' => 'PACKAGE',
              ),
            ),
             'defaultValue' => 'PARCEL',
             'columnLength' => 190,
             'dynamicOptions' => false,
             'defaultValueGenerator' => '',
             'width' => '',
             'optionsProviderType' => 'configure',
             'optionsProviderClass' => '',
             'optionsProviderData' => '',
          )),
          1 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Table::__set_state(array(
             'name' => 'Prices',
             'title' => 'Prices',
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
             'cols' => NULL,
             'colsFixed' => false,
             'rows' => NULL,
             'rowsFixed' => false,
             'data' => '',
             'columnConfigActivated' => false,
             'columnConfig' => 
            array (
            ),
             'height' => '',
             'width' => '100%',
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
             'html' => '<style>
.border td
{
border: 1px solid #212121;
border-collapse: collapse;
padding: 4px;
}
</style>
<table class="border">
<tbody>
<tr>
<td>
<div>
Waga w kg <img height="20" src="/bundles/pimcoreadmin/img/twemoji/27a1.svg"/>
</div>
<div>
<img height="20" src="/bundles/pimcoreadmin/img/twemoji/2b07.svg"/>
Objętość w m<sup>3</sup> 
</div>
</td>
<td>do 3kg</td>
<td>do 15kg</td>
<td>do 30kg</td>
</tr>
<tr>
<td>do 0.1 m <sup>3</sup></td>
<td>~</td>
<td>~</td>
<td>~</td>
</tr>
<tr>
<td>do 0.2 m <sup>3</sup></td>
<td>~</td>
<td>~</td>
<td>~</td>
</tr>
<tr>
<td>do 0.3 m <sup>3</sup></td>
<td>~</td>
<td>~</td>
<td>...</td>
</tr>
</tbody>
</table>',
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
));
