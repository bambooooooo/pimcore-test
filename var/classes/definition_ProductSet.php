<?php

/**
 * Inheritance: yes
 * Variants: no
 *
 * Fields Summary:
 * - localizedfields [localizedfields]
 * -- Name [input]
 * -- Desc1 [wysiwyg]
 * -- Desc2 [wysiwyg]
 * -- Desc3 [wysiwyg]
 * -- Desc4 [wysiwyg]
 * - Width [quantityValue]
 * - Height [quantityValue]
 * - Depth [quantityValue]
 * - Set [advancedManyToManyObjectRelation]
 * - Mass [quantityValue]
 * - PackagesMass [quantityValue]
 * - PackagesVolume [quantityValue]
 * - PackageCount [numeric]
 * - SerieSize [numeric]
 * - LoadCarriers [manyToManyRelation]
 * - WidthBruttoOBI [quantityValue]
 * - HeightBruttoOBI [quantityValue]
 * - LengthBruttoOBI [quantityValue]
 * - WidthNettoOBI [quantityValue]
 * - HeightNettoOBI [quantityValue]
 * - LengthNettoOBI [quantityValue]
 * - Images [imageGallery]
 * - Video [video]
 * - Quality [calculatedValue]
 * - Serie [manyToOneRelation]
 * - Groups [manyToManyObjectRelation]
 * - Parameters [classificationstore]
 * - ParametersAllegro [classificationstore]
 * - GoogleCategory [select]
 * - BasePrice [quantityValue]
 * - Price [advancedManyToManyObjectRelation]
 * - Pricing [advancedManyToManyObjectRelation]
 * - ps_megstyl_pl [booleanSelect]
 * - ps_megstyl_pl_parent [manyToOneRelation]
 * - ps_megstyl_pl_id [numeric]
 * - sgt [checkbox]
 * - BaselinkerCatalog [advancedManyToManyObjectRelation]
 * - Image [image]
 * - EAN [input]
 * - ImagesModel [imageGallery]
 * - CN [input]
 * - GPC [input]
 * - PKWIU [input]
 * - Status [select]
 */

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'ProductSet',
   'name' => 'ProductSet',
   'title' => '',
   'description' => '',
   'creationDate' => NULL,
   'modificationDate' => 1765799336,
   'userOwner' => 2,
   'userModification' => 2,
   'parentClass' => '',
   'implementsInterfaces' => '\\App\\Model\\Interface\\StockInterface',
   'listingParentClass' => '',
   'useTraits' => '\\App\\Traits\\StockTrait',
   'listingUseTraits' => '',
   'encryption' => false,
   'encryptedTables' => 
  array (
  ),
   'allowInherit' => true,
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
      \Pimcore\Model\DataObject\ClassDefinition\Layout\Region::__set_state(array(
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
             'region' => 'center',
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
                     'name' => 'System data',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'System data',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' => 
                    array (
                      0 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields::__set_state(array(
                         'name' => 'localizedfields',
                         'title' => '',
                         'tooltip' => NULL,
                         'mandatory' => false,
                         'noteditable' => false,
                         'index' => false,
                         'locked' => false,
                         'style' => NULL,
                         'permissions' => NULL,
                         'fieldtype' => '',
                         'relationType' => false,
                         'invisible' => false,
                         'visibleGridView' => true,
                         'visibleSearch' => true,
                         'blockedVarsForExport' => 
                        array (
                        ),
                         'children' => 
                        array (
                          0 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                             'name' => 'Name',
                             'title' => 'Name',
                             'tooltip' => 'Nazwa

Nazwa zestawu produtków',
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
                             'columnLength' => 75,
                             'regex' => '',
                             'regexFlags' => 
                            array (
                            ),
                             'unique' => false,
                             'showCharCount' => true,
                             'width' => 500,
                             'defaultValueGenerator' => '',
                          )),
                        ),
                         'region' => NULL,
                         'layout' => NULL,
                         'maxTabs' => NULL,
                         'border' => false,
                         'provideSplitView' => false,
                         'tabPosition' => 'top',
                         'hideLabelsWhenTabsReached' => NULL,
                         'referencedFields' => 
                        array (
                          0 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields::__set_state(array(
                             'name' => 'localizedfields',
                             'title' => '',
                             'tooltip' => NULL,
                             'mandatory' => false,
                             'noteditable' => false,
                             'index' => false,
                             'locked' => false,
                             'style' => NULL,
                             'permissions' => NULL,
                             'fieldtype' => '',
                             'relationType' => false,
                             'invisible' => false,
                             'visibleGridView' => true,
                             'visibleSearch' => true,
                             'blockedVarsForExport' => 
                            array (
                            ),
                             'children' => 
                            array (
                              0 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                                 'name' => 'Desc1',
                                 'title' => 'Desc1',
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
                                 'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                                 'excludeFromSearchIndex' => false,
                                 'maxCharacters' => '',
                                 'height' => '',
                                 'width' => '',
                              )),
                              1 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                                 'name' => 'Desc2',
                                 'title' => 'Desc2',
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
                                 'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                                 'excludeFromSearchIndex' => false,
                                 'maxCharacters' => '',
                                 'height' => '',
                                 'width' => '',
                              )),
                              2 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                                 'name' => 'Desc3',
                                 'title' => 'Desc3',
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
                                 'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                                 'excludeFromSearchIndex' => false,
                                 'maxCharacters' => '',
                                 'height' => '',
                                 'width' => '',
                              )),
                              3 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                                 'name' => 'Desc4',
                                 'title' => 'Desc4',
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
                                 'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                                 'excludeFromSearchIndex' => false,
                                 'maxCharacters' => '',
                                 'height' => '',
                                 'width' => '',
                              )),
                            ),
                             'region' => NULL,
                             'layout' => NULL,
                             'maxTabs' => NULL,
                             'border' => false,
                             'provideSplitView' => false,
                             'tabPosition' => 'top',
                             'hideLabelsWhenTabsReached' => NULL,
                             'referencedFields' => 
                            array (
                            ),
                             'permissionView' => NULL,
                             'permissionEdit' => NULL,
                             'labelWidth' => 100,
                             'labelAlign' => 'left',
                             'width' => '',
                             'height' => '',
                             'fieldDefinitionsCache' => NULL,
                          )),
                        ),
                         'permissionView' => NULL,
                         'permissionEdit' => NULL,
                         'labelWidth' => 100,
                         'labelAlign' => 'left',
                         'width' => '',
                         'height' => '',
                         'fieldDefinitionsCache' => NULL,
                      )),
                      1 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'Width',
                         'title' => 'Width',
                         'tooltip' => 'Szerokość

Szerokość zestawu',
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
                          1 => 'cm',
                          2 => 'm',
                          3 => 'cal',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => false,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      2 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'Height',
                         'title' => 'Height',
                         'tooltip' => 'Wysokość

Wysokość zestawu',
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
                          1 => 'cm',
                          2 => 'm',
                          3 => 'cal',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => false,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      3 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'Depth',
                         'title' => 'Depth',
                         'tooltip' => 'Głębokość

Głębokość zestawu',
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
                          1 => 'cm',
                          2 => 'm',
                          3 => 'cal',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => false,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      4 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
                         'name' => 'Set',
                         'title' => 'Skład zestawu',
                         'tooltip' => 'Zestaw

Skład zestawu - produkty w określonych ilościach',
                         'mandatory' => true,
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
                          0 => 
                          array (
                            'classes' => 'Product',
                          ),
                        ),
                         'displayMode' => NULL,
                         'pathFormatterClass' => '',
                         'maxItems' => NULL,
                         'visibleFields' => 'key,Width,Height,Depth',
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
                            'type' => 'number',
                            'position' => 1,
                            'key' => 'Quantity',
                            'label' => 'Ilość',
                          ),
                        ),
                         'columnKeys' => 
                        array (
                          0 => 'Quantity',
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
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/tools.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  1 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Packing',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Packing',
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
                         'name' => 'Mass',
                         'title' => 'Mass',
                         'tooltip' => 'Masa zestawu

Łączna masa produktów w zestawie',
                         'mandatory' => true,
                         'noteditable' => true,
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
                         'defaultUnit' => 'kg',
                         'validUnits' => 
                        array (
                          0 => 'kg',
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'PackagesMass',
                         'title' => 'Packages Mass',
                         'tooltip' => 'Masa paczek

Łączna masa paczek wszystkich produtków w zestawie',
                         'mandatory' => false,
                         'noteditable' => true,
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
                         'defaultUnit' => 'kg',
                         'validUnits' => 
                        array (
                          0 => 'kg',
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
                      2 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'PackagesVolume',
                         'title' => 'Packages Volume',
                         'tooltip' => 'Objętość paczek

Łączna objętość wszystkich produtków w zestawie',
                         'mandatory' => false,
                         'noteditable' => true,
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
                         'defaultUnit' => 'm3',
                         'validUnits' => 
                        array (
                          0 => 'm3',
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
                      3 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
                         'name' => 'PackageCount',
                         'title' => 'Package Count',
                         'tooltip' => 'Ilość paczek',
                         'mandatory' => false,
                         'noteditable' => true,
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
                         'integer' => false,
                         'unsigned' => false,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'unique' => false,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                         'width' => '',
                         'defaultValueGenerator' => '',
                      )),
                      4 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
                         'name' => 'SerieSize',
                         'title' => 'Serie Size',
                         'tooltip' => '',
                         'mandatory' => false,
                         'noteditable' => true,
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
                         'integer' => false,
                         'unsigned' => false,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'unique' => false,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                         'width' => '',
                         'defaultValueGenerator' => '',
                      )),
                      5 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation::__set_state(array(
                         'name' => 'LoadCarriers',
                         'title' => 'Load Carriers',
                         'tooltip' => 'Nośniki

Nośniki, na których może być wysyłany towar. Przykładowo: wrażliwe na uszkodzenia towary mogą być ograniczone do transportu wyłącznie na wybranej palecie.

Brak oznacza dostępność na wszystkich nośnikach, z wysyłką "luzem" włącznie',
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
                          0 => 
                          array (
                            'classes' => 'LoadCarrier',
                          ),
                        ),
                         'displayMode' => NULL,
                         'pathFormatterClass' => '',
                         'maxItems' => NULL,
                         'assetInlineDownloadAllowed' => false,
                         'assetUploadPath' => '',
                         'allowToClearRelation' => true,
                         'objectsAllowed' => true,
                         'assetsAllowed' => false,
                         'assetTypes' => 
                        array (
                        ),
                         'documentsAllowed' => false,
                         'documentTypes' => 
                        array (
                        ),
                         'enableTextSelection' => false,
                         'width' => '',
                         'height' => '',
                      )),
                      6 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'WidthBruttoOBI',
                         'title' => 'Width Brutto O B I',
                         'tooltip' => '',
                         'mandatory' => false,
                         'noteditable' => true,
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
                          0 => 'h',
                          1 => 'K',
                          2 => 'kg',
                          3 => 'y',
                          4 => 'lm',
                          5 => 'm2',
                          6 => 'm3',
                          7 => 'mm',
                          8 => 'N',
                          9 => 'W',
                          10 => 'PLN',
                          11 => 'st_C',
                          12 => 'g',
                          13 => 't',
                          14 => 'cm',
                          15 => 'cal',
                          16 => 'm',
                          17 => 'USD',
                          18 => 'EUR',
                          19 => 'GBP',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => true,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      7 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'HeightBruttoOBI',
                         'title' => 'Height Brutto O B I',
                         'tooltip' => '',
                         'mandatory' => false,
                         'noteditable' => true,
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
                          0 => 'h',
                          1 => 'K',
                          2 => 'kg',
                          3 => 'y',
                          4 => 'lm',
                          5 => 'm2',
                          6 => 'm3',
                          7 => 'mm',
                          8 => 'N',
                          9 => 'W',
                          10 => 'PLN',
                          11 => 'st_C',
                          12 => 'g',
                          13 => 't',
                          14 => 'cm',
                          15 => 'cal',
                          16 => 'm',
                          17 => 'USD',
                          18 => 'EUR',
                          19 => 'GBP',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => true,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      8 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'LengthBruttoOBI',
                         'title' => 'Length Brutto O B I',
                         'tooltip' => '',
                         'mandatory' => false,
                         'noteditable' => true,
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
                          0 => 'h',
                          1 => 'K',
                          2 => 'kg',
                          3 => 'y',
                          4 => 'lm',
                          5 => 'm2',
                          6 => 'm3',
                          7 => 'mm',
                          8 => 'N',
                          9 => 'W',
                          10 => 'PLN',
                          11 => 'st_C',
                          12 => 'g',
                          13 => 't',
                          14 => 'cm',
                          15 => 'cal',
                          16 => 'm',
                          17 => 'USD',
                          18 => 'EUR',
                          19 => 'GBP',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => true,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      9 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'WidthNettoOBI',
                         'title' => 'Width Netto OBI',
                         'tooltip' => 'Szerokość Netto OBI - Najszersza z paczek (jeśli ułożymy je w stos)',
                         'mandatory' => false,
                         'noteditable' => true,
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
                          0 => 'h',
                          1 => 'K',
                          2 => 'kg',
                          3 => 'y',
                          4 => 'lm',
                          5 => 'm2',
                          6 => 'm3',
                          7 => 'mm',
                          8 => 'N',
                          9 => 'W',
                          10 => 'PLN',
                          11 => 'st_C',
                          12 => 'g',
                          13 => 't',
                          14 => 'cm',
                          15 => 'cal',
                          16 => 'm',
                          17 => 'USD',
                          18 => 'EUR',
                          19 => 'GBP',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => true,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      10 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'HeightNettoOBI',
                         'title' => 'Height Netto OBI',
                         'tooltip' => 'Wysokość Netto OBI - Suma wysokości paczek (jeśli ułożymy je w stos)',
                         'mandatory' => false,
                         'noteditable' => true,
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
                          0 => 'h',
                          1 => 'K',
                          2 => 'kg',
                          3 => 'y',
                          4 => 'lm',
                          5 => 'm2',
                          6 => 'm3',
                          7 => 'mm',
                          8 => 'N',
                          9 => 'W',
                          10 => 'PLN',
                          11 => 'st_C',
                          12 => 'g',
                          13 => 't',
                          14 => 'cm',
                          15 => 'cal',
                          16 => 'm',
                          17 => 'USD',
                          18 => 'EUR',
                          19 => 'GBP',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => true,
                         'unsigned' => true,
                         'minValue' => NULL,
                         'maxValue' => NULL,
                         'decimalSize' => NULL,
                         'decimalPrecision' => NULL,
                      )),
                      11 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'LengthNettoOBI',
                         'title' => 'Length Netto OBI',
                         'tooltip' => 'Długość Netto OBI - Najdłuższa z paczek (jeśli ułożymy je w stos)',
                         'mandatory' => false,
                         'noteditable' => true,
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
                          0 => 'h',
                          1 => 'K',
                          2 => 'kg',
                          3 => 'y',
                          4 => 'lm',
                          5 => 'm2',
                          6 => 'm3',
                          7 => 'mm',
                          8 => 'N',
                          9 => 'W',
                          10 => 'PLN',
                          11 => 'st_C',
                          12 => 'g',
                          13 => 't',
                          14 => 'cm',
                          15 => 'cal',
                          16 => 'm',
                          17 => 'USD',
                          18 => 'EUR',
                          19 => 'GBP',
                        ),
                         'unique' => false,
                         'autoConvert' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'defaultValue' => NULL,
                         'integer' => true,
                         'unsigned' => true,
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
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/deployment.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  2 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Media',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Media',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' => 
                    array (
                      0 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ImageGallery::__set_state(array(
                         'name' => 'Images',
                         'title' => 'Images',
                         'tooltip' => 'Zdjęcia

Dodatkowe zdjęcia zestawu. Nie należy tu duplikować zdjęć produktów składowych, które są już w odpowiednim miejscu w systemie.',
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
                         'uploadPath' => '/PRODUKT-ZDJĘCIA',
                         'ratioX' => NULL,
                         'ratioY' => NULL,
                         'predefinedDataTemplates' => '',
                         'height' => 240,
                         'width' => 240,
                      )),
                      1 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Video::__set_state(array(
                         'name' => 'Video',
                         'title' => 'Video',
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
                         'uploadPath' => '',
                         'allowedTypes' => 
                        array (
                          0 => 'youtube',
                        ),
                         'supportedTypes' => 
                        array (
                          0 => 'asset',
                          1 => 'youtube',
                          2 => 'vimeo',
                          3 => 'dailymotion',
                        ),
                         'height' => '',
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
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/image_file.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  3 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Quality',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Quality',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' => 
                    array (
                      0 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\CalculatedValue::__set_state(array(
                         'name' => 'Quality',
                         'title' => 'Quality',
                         'tooltip' => 'Jakość danych

Stopień uzupełnienia danych produktu',
                         'mandatory' => false,
                         'noteditable' => true,
                         'index' => false,
                         'locked' => false,
                         'style' => '',
                         'permissions' => NULL,
                         'fieldtype' => '',
                         'relationType' => false,
                         'invisible' => true,
                         'visibleGridView' => false,
                         'visibleSearch' => false,
                         'blockedVarsForExport' => 
                        array (
                        ),
                         'elementType' => 'input',
                         'calculatorType' => 'class',
                         'calculatorExpression' => 'calcExprTest',
                         'calculatorClass' => 'App\\Model\\Quality\\QualityRate',
                         'columnLength' => 190,
                         'width' => '',
                      )),
                      1 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields::__set_state(array(
                         'name' => 'localizedfields',
                         'title' => '',
                         'tooltip' => NULL,
                         'mandatory' => false,
                         'noteditable' => false,
                         'index' => false,
                         'locked' => false,
                         'style' => NULL,
                         'permissions' => NULL,
                         'fieldtype' => '',
                         'relationType' => false,
                         'invisible' => false,
                         'visibleGridView' => true,
                         'visibleSearch' => true,
                         'blockedVarsForExport' => 
                        array (
                        ),
                         'children' => 
                        array (
                          0 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                             'name' => 'Desc1',
                             'title' => 'Desc1',
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
                             'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                             'excludeFromSearchIndex' => false,
                             'maxCharacters' => '',
                             'height' => '',
                             'width' => '',
                          )),
                          1 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                             'name' => 'Desc2',
                             'title' => 'Desc2',
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
                             'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                             'excludeFromSearchIndex' => false,
                             'maxCharacters' => '',
                             'height' => '',
                             'width' => '',
                          )),
                          2 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                             'name' => 'Desc3',
                             'title' => 'Desc3',
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
                             'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                             'excludeFromSearchIndex' => false,
                             'maxCharacters' => '',
                             'height' => '',
                             'width' => '',
                          )),
                          3 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                             'name' => 'Desc4',
                             'title' => 'Desc4',
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
                             'toolbarConfig' => '{
  "modules": {
    "toolbar": [
      ["code-block"],
      [{ "header": [1, 2, false] }],
      [{ "list": "ordered" }, { "list": "bullet" }],
      ["bold"]
    ]
  }
}',
                             'excludeFromSearchIndex' => false,
                             'maxCharacters' => '',
                             'height' => '',
                             'width' => '',
                          )),
                        ),
                         'region' => NULL,
                         'layout' => NULL,
                         'maxTabs' => NULL,
                         'border' => false,
                         'provideSplitView' => false,
                         'tabPosition' => 'top',
                         'hideLabelsWhenTabsReached' => NULL,
                         'referencedFields' => 
                        array (
                        ),
                         'permissionView' => NULL,
                         'permissionEdit' => NULL,
                         'labelWidth' => 100,
                         'labelAlign' => 'left',
                         'width' => '',
                         'height' => '',
                         'fieldDefinitionsCache' => NULL,
                      )),
                    ),
                     'locked' => false,
                     'blockedVarsForExport' => 
                    array (
                    ),
                     'fieldtype' => 'panel',
                     'layout' => NULL,
                     'border' => false,
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/star.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  4 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Parameters',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Parameters',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' => 
                    array (
                      0 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                         'name' => 'Serie',
                         'title' => 'Serie',
                         'tooltip' => 'Kolekcja/Seria',
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
                          0 => 
                          array (
                            'classes' => 'Group',
                          ),
                        ),
                         'displayMode' => 'grid',
                         'pathFormatterClass' => '',
                         'assetInlineDownloadAllowed' => false,
                         'assetUploadPath' => '',
                         'allowToClearRelation' => true,
                         'objectsAllowed' => true,
                         'assetsAllowed' => false,
                         'assetTypes' => 
                        array (
                        ),
                         'documentsAllowed' => false,
                         'documentTypes' => 
                        array (
                        ),
                         'width' => '',
                      )),
                      1 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
                         'name' => 'Groups',
                         'title' => 'Grupy',
                         'tooltip' => 'Grupy

Grupy (kolekcje, kategorie, oferty) do których należy zestaw)',
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
                          0 => 
                          array (
                            'classes' => 'Group',
                          ),
                        ),
                         'displayMode' => 'grid',
                         'pathFormatterClass' => '',
                         'maxItems' => NULL,
                         'visibleFields' => 'key',
                         'allowToCreateNewObject' => false,
                         'allowToClearRelation' => true,
                         'optimizedAdminLoading' => false,
                         'enableTextSelection' => false,
                         'visibleFieldDefinitions' => 
                        array (
                        ),
                         'width' => '',
                         'height' => '',
                      )),
                      2 => 
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
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Classificationstore::__set_state(array(
                                 'name' => 'Parameters',
                                 'title' => 'Parametry',
                                 'tooltip' => 'Parametry

Kolekcje i grupy parametrów dotyczące całego zestawu. Nie wprowadzamy tutaj parametrów produktów składowych.',
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
                                 'children' => 
                                array (
                                ),
                                 'labelWidth' => 200,
                                 'localized' => false,
                                 'storeId' => 27,
                                 'hideEmptyData' => false,
                                 'disallowAddRemove' => false,
                                 'referencedFields' => 
                                array (
                                ),
                                 'fieldDefinitionsCache' => NULL,
                                 'allowedGroupIds' => 
                                array (
                                ),
                                 'activeGroupDefinitions' => 
                                array (
                                ),
                                 'maxItems' => NULL,
                                 'height' => NULL,
                                 'width' => NULL,
                              )),
                            ),
                             'locked' => false,
                             'blockedVarsForExport' => 
                            array (
                            ),
                             'fieldtype' => 'panel',
                             'layout' => NULL,
                             'border' => false,
                             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/tools.svg',
                             'labelWidth' => 100,
                             'labelAlign' => 'left',
                          )),
                          1 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                             'name' => 'Layout',
                             'type' => NULL,
                             'region' => NULL,
                             'title' => 'Allegro',
                             'width' => '',
                             'height' => '',
                             'collapsible' => false,
                             'collapsed' => false,
                             'bodyStyle' => '',
                             'datatype' => 'layout',
                             'children' => 
                            array (
                              0 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Classificationstore::__set_state(array(
                                 'name' => 'ParametersAllegro',
                                 'title' => 'Parameters Allegro',
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
                                 'children' => 
                                array (
                                ),
                                 'labelWidth' => 0,
                                 'localized' => false,
                                 'storeId' => 32,
                                 'hideEmptyData' => false,
                                 'disallowAddRemove' => false,
                                 'referencedFields' => 
                                array (
                                ),
                                 'fieldDefinitionsCache' => NULL,
                                 'allowedGroupIds' => 
                                array (
                                ),
                                 'activeGroupDefinitions' => 
                                array (
                                ),
                                 'maxItems' => 1,
                                 'height' => NULL,
                                 'width' => NULL,
                              )),
                            ),
                             'locked' => false,
                             'blockedVarsForExport' => 
                            array (
                            ),
                             'fieldtype' => 'panel',
                             'layout' => NULL,
                             'border' => false,
                             'icon' => '/LOGO/allegro.svg',
                             'labelWidth' => 100,
                             'labelAlign' => 'left',
                          )),
                          2 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                             'name' => 'Google',
                             'type' => NULL,
                             'region' => NULL,
                             'title' => 'Google',
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
                                 'name' => 'GoogleCategory',
                                 'title' => 'Google Category',
                                 'tooltip' => '',
                                 'mandatory' => false,
                                 'noteditable' => false,
                                 'index' => false,
                                 'locked' => false,
                                 'style' => '',
                                 'permissions' => NULL,
                                 'fieldtype' => '',
                                 'relationType' => false,
                                 'invisible' => true,
                                 'visibleGridView' => false,
                                 'visibleSearch' => false,
                                 'blockedVarsForExport' => 
                                array (
                                ),
                                 'defaultValue' => '',
                                 'columnLength' => 190,
                                 'dynamicOptions' => false,
                                 'defaultValueGenerator' => '',
                                 'width' => 500,
                                 'optionsProviderType' => 'select_options',
                                 'optionsProviderClass' => 'Pimcore\\Bundle\\CoreBundle\\OptionsProvider\\SelectOptionsOptionsProvider',
                                 'optionsProviderData' => 'GoogleCategory',
                              )),
                            ),
                             'locked' => false,
                             'blockedVarsForExport' => 
                            array (
                            ),
                             'fieldtype' => 'panel',
                             'layout' => NULL,
                             'border' => false,
                             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/google.svg',
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
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/genealogy.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  5 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Prices',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Prices',
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
                         'name' => 'BasePrice',
                         'title' => 'Cena bazowa',
                         'tooltip' => 'Cena bazowa

Suma cen bazowych produktów',
                         'mandatory' => false,
                         'noteditable' => true,
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
                         'autoConvert' => true,
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
                         'name' => 'Price',
                         'title' => 'Price',
                         'tooltip' => '',
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
                         'visibleFields' => 'key',
                         'allowToCreateNewObject' => false,
                         'allowToClearRelation' => false,
                         'optimizedAdminLoading' => false,
                         'enableTextSelection' => false,
                         'visibleFieldDefinitions' => 
                        array (
                        ),
                         'width' => '',
                         'height' => '',
                         'allowedClassId' => 'Offer',
                         'columns' => 
                        array (
                          0 => 
                          array (
                            'type' => 'number',
                            'position' => 1,
                            'key' => 'Price',
                            'label' => 'Price',
                            'value' => '',
                            'width' => NULL,
                          ),
                          1 => 
                          array (
                            'type' => 'text',
                            'position' => 2,
                            'key' => 'Currency',
                            'label' => 'Currency',
                            'value' => '',
                            'width' => NULL,
                          ),
                          2 => 
                          array (
                            'type' => 'bool',
                            'position' => 3,
                            'key' => 'Fixed',
                            'label' => 'Is Fixed?',
                          ),
                        ),
                         'columnKeys' => 
                        array (
                          0 => 'Price',
                          1 => 'Currency',
                          2 => 'Fixed',
                        ),
                         'enableBatchEdit' => false,
                         'allowMultipleAssignments' => false,
                      )),
                      2 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
                         'name' => 'Pricing',
                         'title' => 'Pricing',
                         'tooltip' => 'Wyceny',
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
                         'visibleFields' => 'key,Countries',
                         'allowToCreateNewObject' => false,
                         'allowToClearRelation' => true,
                         'optimizedAdminLoading' => false,
                         'enableTextSelection' => false,
                         'visibleFieldDefinitions' => 
                        array (
                        ),
                         'width' => '',
                         'height' => '',
                         'allowedClassId' => 'Pricing',
                         'columns' => 
                        array (
                          0 => 
                          array (
                            'type' => 'number',
                            'position' => 1,
                            'key' => 'Price',
                            'label' => 'Price',
                            'width' => NULL,
                            'value' => '',
                          ),
                          1 => 
                          array (
                            'type' => 'text',
                            'position' => 2,
                            'key' => 'Currency',
                            'label' => 'Currency',
                          ),
                        ),
                         'columnKeys' => 
                        array (
                          0 => 'Price',
                          1 => 'Currency',
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
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/sales_performance.svg',
                     'labelWidth' => 200,
                     'labelAlign' => 'left',
                  )),
                  6 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Integrations',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Integrations',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' => 
                    array (
                      0 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Layout\Accordion::__set_state(array(
                         'name' => 'Channels',
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
                             'name' => 'Prestashop',
                             'type' => NULL,
                             'region' => NULL,
                             'title' => 'Prestashop 8',
                             'width' => '',
                             'height' => '',
                             'collapsible' => true,
                             'collapsed' => true,
                             'bodyStyle' => '',
                             'datatype' => 'layout',
                             'children' => 
                            array (
                              0 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\BooleanSelect::__set_state(array(
                                 'name' => 'ps_megstyl_pl',
                                 'title' => 'Publish on megstyl.pl',
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
                                 'yesLabel' => 'Yes',
                                 'noLabel' => 'No',
                                 'emptyLabel' => '',
                                 'options' => 
                                array (
                                  0 => 
                                  array (
                                    'key' => '',
                                    'value' => 0,
                                  ),
                                  1 => 
                                  array (
                                    'key' => 'Yes',
                                    'value' => 1,
                                  ),
                                  2 => 
                                  array (
                                    'key' => 'No',
                                    'value' => -1,
                                  ),
                                ),
                                 'width' => '',
                              )),
                              1 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                                 'name' => 'ps_megstyl_pl_parent',
                                 'title' => 'Parent group',
                                 'tooltip' => '',
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
                                  0 => 
                                  array (
                                    'classes' => 'Group',
                                  ),
                                ),
                                 'displayMode' => 'grid',
                                 'pathFormatterClass' => '',
                                 'assetInlineDownloadAllowed' => false,
                                 'assetUploadPath' => '',
                                 'allowToClearRelation' => true,
                                 'objectsAllowed' => true,
                                 'assetsAllowed' => false,
                                 'assetTypes' => 
                                array (
                                ),
                                 'documentsAllowed' => false,
                                 'documentTypes' => 
                                array (
                                ),
                                 'width' => '',
                              )),
                              2 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
                                 'name' => 'ps_megstyl_pl_id',
                                 'title' => 'Id',
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
                                 'defaultValue' => NULL,
                                 'integer' => false,
                                 'unsigned' => false,
                                 'minValue' => NULL,
                                 'maxValue' => NULL,
                                 'unique' => false,
                                 'decimalSize' => NULL,
                                 'decimalPrecision' => NULL,
                                 'width' => 220,
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
                             'icon' => '/LOGO/prestashop.png',
                             'labelWidth' => 180,
                             'labelAlign' => 'left',
                          )),
                          1 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                             'name' => 'Subiekt GT',
                             'type' => NULL,
                             'region' => NULL,
                             'title' => 'Subiekt GT',
                             'width' => '',
                             'height' => '',
                             'collapsible' => true,
                             'collapsed' => true,
                             'bodyStyle' => '',
                             'datatype' => 'layout',
                             'children' => 
                            array (
                              0 => 
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
                                 'html' => '<div class="alert alert-warning">
Not used in common version. Shown as a placeholder
</div>',
                                 'renderingClass' => '',
                                 'renderingData' => '',
                                 'border' => false,
                              )),
                              1 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\Checkbox::__set_state(array(
                                 'name' => 'sgt',
                                 'title' => 'Subiekt GT',
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
                                 'defaultValue' => NULL,
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
                             'icon' => '/LOGO/sgt.png',
                             'labelWidth' => 100,
                             'labelAlign' => 'left',
                          )),
                          2 => 
                          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                             'name' => 'Baselinker',
                             'type' => NULL,
                             'region' => NULL,
                             'title' => 'Baselinker',
                             'width' => '',
                             'height' => '',
                             'collapsible' => true,
                             'collapsed' => true,
                             'bodyStyle' => '',
                             'datatype' => 'layout',
                             'children' => 
                            array (
                              0 => 
                              \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
                                 'name' => 'BaselinkerCatalog',
                                 'title' => 'Baselinker Catalog',
                                 'tooltip' => '',
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
                                 'visibleFields' => 'key,CatalogId,Name',
                                 'allowToCreateNewObject' => false,
                                 'allowToClearRelation' => true,
                                 'optimizedAdminLoading' => false,
                                 'enableTextSelection' => false,
                                 'visibleFieldDefinitions' => 
                                array (
                                ),
                                 'width' => '',
                                 'height' => '',
                                 'allowedClassId' => 'BaselinkerCatalog',
                                 'columns' => 
                                array (
                                  0 => 
                                  array (
                                    'type' => 'number',
                                    'position' => 1,
                                    'key' => 'ProductId',
                                    'label' => 'Product Id',
                                    'id' => 'extModel9950-1',
                                  ),
                                  1 => 
                                  array (
                                    'type' => 'text',
                                    'position' => 2,
                                    'key' => 'Version',
                                    'label' => 'Version',
                                    'id' => 'extModel9950-2',
                                  ),
                                ),
                                 'columnKeys' => 
                                array (
                                  0 => 'ProductId',
                                  1 => 'Version',
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
                             'icon' => '/LOGO/base.png',
                             'labelWidth' => 100,
                             'labelAlign' => 'left',
                          )),
                        ),
                         'locked' => false,
                         'blockedVarsForExport' => 
                        array (
                        ),
                         'fieldtype' => 'accordion',
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
                     'icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHJlY3QgeD0iMy4yIiB5PSIxNS40IiB0cmFuc2Zvcm09Im1hdHJpeCgwLjc1OTcgLTAuNjUwMyAwLjY1MDMgMC43NTk3IC04LjgzNCA3Ljk5MzQpIiBmaWxsPSIjMjg3OEYwIiB3aWR0aD0iNi4zIiBoZWlnaHQ9IjEiLz4NCjxyZWN0IHg9IjUuOCIgeT0iNC45IiB0cmFuc2Zvcm09Im1hdHJpeCgwLjY1MSAtMC43NTkxIDAuNzU5MSAwLjY1MSAtMy44NjA4IDcuNTk5MykiIGZpbGw9IiMyODc4RjAiIHdpZHRoPSIxIiBoZWlnaHQ9IjYuMSIvPg0KPHJlY3QgeD0iMTAuOSIgeT0iNi4zIiB0cmFuc2Zvcm09Im1hdHJpeCgwLjUzMDQgLTAuODQ3OCAwLjg0NzggMC41MzA0IDAuOTI3MyAxNS4yNzY1KSIgZmlsbD0iIzI4NzhGMCIgd2lkdGg9IjYuNiIgaGVpZ2h0PSIxIi8+DQo8cmVjdCB4PSIxMy43IiB5PSIxMS41IiBmaWxsPSIjMjg3OEYwIiB3aWR0aD0iNi4zIiBoZWlnaHQ9IjEiLz4NCjxyZWN0IHg9IjEzLjgiIHk9IjEzLjkiIHRyYW5zZm9ybT0ibWF0cml4KDAuODQ3OCAtMC41MzAzIDAuNTMwMyAwLjg0NzggLTYuOTUxOSAxMC4xNzYyKSIgZmlsbD0iIzI4NzhGMCIgd2lkdGg9IjEiIGhlaWdodD0iNi42Ii8+DQo8Zz4NCgk8cGF0aCBmaWxsPSIjMjg3OEYwIiBkPSJNMTEsMTBjMS4xLDAsMiwwLjksMiwycy0wLjksMi0yLDJzLTItMC45LTItMlM5LjksMTAsMTEsMTAgTTExLDhjLTIuMiwwLTQsMS44LTQsNHMxLjgsNCw0LDRzNC0xLjgsNC00DQoJCVMxMy4yLDgsMTEsOEwxMSw4eiIvPg0KPC9nPg0KPGNpcmNsZSBmaWxsPSIjMjg3OEYwIiBjeD0iNCIgY3k9IjYiIHI9IjIiLz4NCjxjaXJjbGUgZmlsbD0iIzI4NzhGMCIgY3g9IjQiIGN5PSIxOCIgcj0iMiIvPg0KPGNpcmNsZSBmaWxsPSIjMjg3OEYwIiBjeD0iMTYiIGN5PSI0IiByPSIyIi8+DQo8Y2lyY2xlIGZpbGw9IiMyODc4RjAiIGN4PSIxNiIgY3k9IjIwIiByPSIyIi8+DQo8Y2lyY2xlIGZpbGw9IiMyODc4RjAiIGN4PSIyMCIgY3k9IjEyIiByPSIyIi8+DQo8L3N2Zz4NCg==',
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
             'icon' => '',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          1 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Layout',
             'type' => NULL,
             'region' => 'east',
             'title' => '',
             'width' => 460,
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
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
                     'title' => 'System data',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' => 
                    array (
                      0 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Image::__set_state(array(
                         'name' => 'Image',
                         'title' => 'Image',
                         'tooltip' => 'Zdjęcie główne

Miniturka zestawu - często na przeźroczystym tle',
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
                         'uploadPath' => '',
                         'width' => 400,
                         'height' => 400,
                      )),
                      1 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                         'name' => 'EAN',
                         'title' => 'EAN',
                         'tooltip' => 'EAN

Globalny identyfikator (GTIN) dla całego zestawu (z konkretnymi ilościami produktów składowych), nadawany w portalu mojegs1.pl',
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
                         'unique' => true,
                         'showCharCount' => false,
                         'width' => 400,
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
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/tools.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'top',
                  )),
                  1 => 
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Layout',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Model',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' => 
                    array (
                      0 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ImageGallery::__set_state(array(
                         'name' => 'ImagesModel',
                         'title' => 'Images of model (2000 x 2000)',
                         'tooltip' => 'Zdjęcia wspólne dla modelu zestawu

Przykładowo: rysunek z wymiarami',
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
                         'uploadPath' => '',
                         'ratioX' => NULL,
                         'ratioY' => NULL,
                         'predefinedDataTemplates' => '',
                         'height' => 380,
                         'width' => 380,
                      )),
                      1 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                         'name' => 'CN',
                         'title' => 'CN Code',
                         'tooltip' => 'CN

Kod taryfy celnej produktu. Można skorzystać z wyszukiwarki ext-isztar4.mf.gov.pl

Kod CN zawiera automatycznie kod HS.',
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
                         'name' => 'GPC',
                         'title' => 'GPC',
                         'tooltip' => 'GPC

Ośmiocyfrowy numer "Brick" klasyfikacji produktowej GS1 GPC. Wyszukiwarka kodów znajduje się pod adresem: https://www.gs1.org/services/gpc-browser ',
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
                      3 => 
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                         'name' => 'PKWIU',
                         'title' => 'Kod PKWiU',
                         'tooltip' => 'Polska Klasyfikacja Wyrobów i Usług (2015)
Uwaga!
Od stycznia 2026 będzie aktualizacja kodów
https://poradnikprzedsiebiorcy.pl/-nowa-pkwiu-2025-juz-ogloszona
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
                    ),
                     'locked' => false,
                     'blockedVarsForExport' => 
                    array (
                    ),
                     'fieldtype' => 'panel',
                     'layout' => NULL,
                     'border' => false,
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/ruler.svg',
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
             'icon' => '',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          2 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Layout',
             'type' => NULL,
             'region' => 'south',
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
                 'name' => 'Status',
                 'title' => 'Status',
                 'tooltip' => '',
                 'mandatory' => true,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => 'width: 400px; float: left;',
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
                    'key' => 'Draft',
                    'value' => 'Draft',
                  ),
                  1 => 
                  array (
                    'key' => 'Active (sellable)',
                    'value' => 'Active',
                  ),
                  2 => 
                  array (
                    'key' => 'Sale',
                    'value' => 'Sale',
                  ),
                  3 => 
                  array (
                    'key' => 'End of Life (not sellable)',
                    'value' => 'EoF',
                  ),
                ),
                 'defaultValue' => 'Draft',
                 'columnLength' => 190,
                 'dynamicOptions' => false,
                 'defaultValueGenerator' => '',
                 'width' => '',
                 'optionsProviderType' => 'configure',
                 'optionsProviderClass' => '',
                 'optionsProviderData' => '',
              )),
            ),
             'locked' => false,
             'blockedVarsForExport' => 
            array (
            ),
             'fieldtype' => 'panel',
             'layout' => '',
             'border' => true,
             'icon' => '',
             'labelWidth' => 180,
             'labelAlign' => 'left',
          )),
        ),
         'locked' => false,
         'blockedVarsForExport' => 
        array (
        ),
         'fieldtype' => 'region',
         'icon' => '',
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
   'icon' => '/UI/4-squares-red.svg',
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
      'key' => false,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
    ),
    'search' => 
    array (
      'id' => true,
      'key' => false,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
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
