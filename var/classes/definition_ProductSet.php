<?php

/**
 * Inheritance: yes
 * Variants: no
 *
 * Fields Summary:
 * - Image [image]
 * - localizedfields [localizedfields]
 * -- Name [input]
 * - Set [advancedManyToManyRelation]
 * - EAN [input]
 * - Images [imageGallery]
 * - Groups [manyToManyObjectRelation]
 * - Parameters [classificationstore]
 * - BasePrice [quantityValue]
 */

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'ProductSet',
   'name' => 'ProductSet',
   'title' => '',
   'description' => '',
   'creationDate' => NULL,
   'modificationDate' => 1744110334,
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
             'title' => 'Dane systemowe',
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
                 'tooltip' => '',
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => 'float: right; margin-left: 8px;',
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
                ),
                 'permissionView' => NULL,
                 'permissionEdit' => NULL,
                 'labelWidth' => 100,
                 'labelAlign' => 'left',
                 'width' => '',
                 'height' => '',
                 'fieldDefinitionsCache' => NULL,
              )),
              2 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyRelation::__set_state(array(
                 'name' => 'Set',
                 'title' => 'Skład zestawu',
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
                    'classes' => 'Product',
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
                  0 => 
                  array (
                    'assetTypes' => '',
                  ),
                ),
                 'documentsAllowed' => false,
                 'documentTypes' => 
                array (
                  0 => 
                  array (
                    'documentTypes' => '',
                  ),
                ),
                 'enableTextSelection' => false,
                 'width' => '',
                 'height' => NULL,
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
                 'phpdocType' => '\\Pimcore\\Model\\DataObject\\Data\\ElementMetadata[]',
                 'optimizedAdminLoading' => false,
                 'enableBatchEdit' => false,
                 'allowMultipleAssignments' => false,
              )),
              3 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                 'name' => 'EAN',
                 'title' => 'EAN',
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
              4 => 
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
                 'html' => '<h1>Zdjęcie główne</h1>

Miniturka zestawu - często na przeźroczystym tle

<h1>Nazwa</h1>

Nazwa zestawu produtków

<h1>Zestaw</h1>

Skład zestawu - produkty w określonych ilościach

<h1>EAN</h1>

Kod EAN przypisany do całego zestawu (z konkretnymi ilościami produktów składowych).',
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
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/tools.svg',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          1 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Images',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Zdjęcia',
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
                 'tooltip' => '',
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => 'float: left; width: 100%',
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
                 'height' => '',
                 'width' => '',
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
                 'html' => '<h1>Zdjęcia</h1>

Dodatkowe zdjęcia zestawu',
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
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/image_file.svg',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          2 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Parameters',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Parametry',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
                 'name' => 'Groups',
                 'title' => 'Grupy',
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
              1 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Classificationstore::__set_state(array(
                 'name' => 'Parameters',
                 'title' => 'Parametry',
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
                 'labelWidth' => 200,
                 'localized' => false,
                 'storeId' => 1,
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
                 'html' => '<h1>Grupy</h1>

Grupy (kolekcje, kategorie, inne) do których należy zestaw

<h1>Parametry</h1>

Kolekcje i grupy parametrów dotyczące całego zestawu. Nie wprowadzamy tutaj parametrów produktów składowych.

',
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
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/genealogy.svg',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          3 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Prices',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Ceny',
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
                 'tooltip' => 'TKW lub cena zakupu',
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
                 'html' => '<h1>Cena bazowa</h1>

Suma cen bazowych produktów',
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
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/sales_performance.svg',
             'labelWidth' => 200,
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
