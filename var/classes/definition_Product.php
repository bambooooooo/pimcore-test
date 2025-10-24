<?php

/**
 * Inheritance: yes
 * Variants: no
 * Title: Produkt
 * Towar, który można sprzedać
 *
 * Fields Summary:
 * - localizedfields [localizedfields]
 * -- Name [input]
 * -- Summary [wysiwyg]
 * -- Infographics [imageGallery]
 * -- Desc1 [wysiwyg]
 * -- Desc2 [wysiwyg]
 * -- Desc3 [wysiwyg]
 * -- Desc4 [wysiwyg]
 * - Group [manyToOneRelation]
 * - Model [input]
 * - Width [quantityValue]
 * - Height [quantityValue]
 * - Depth [quantityValue]
 * - Mass [quantityValue]
 * - COO [country]
 * - CN [input]
 * - GPC [input]
 * - PKWIU [input]
 * - Manufacturer [manyToOneRelation]
 * - Suppliers [manyToManyRelation]
 * - Serie [manyToOneRelation]
 * - Groups [manyToManyObjectRelation]
 * - Parameters [classificationstore]
 * - ParametersAllegro [classificationstore]
 * - GoogleCategory [select]
 * - BasePrice [quantityValue]
 * - Price [advancedManyToManyObjectRelation]
 * - Pricing [advancedManyToManyObjectRelation]
 * - Images [imageGallery]
 * - Photos [imageGallery]
 * - Video [video]
 * - Packages [advancedManyToManyObjectRelation]
 * - PackageCount [numeric]
 * - PackagesMass [quantityValue]
 * - PackagesVolume [quantityValue]
 * - SerieSize [numeric]
 * - LoadCarriers [manyToManyRelation]
 * - Quality [numeric]
 * - Description [fieldcollections]
 * - Barcode [input]
 * - Codes [objectbricks]
 * - Documents [manyToManyRelation]
 * - Instruction [manyToOneRelation]
 * - InstructionUS [manyToOneRelation]
 * - ps_megstyl_pl [booleanSelect]
 * - ps_megstyl_pl_parent [manyToOneRelation]
 * - ps_megstyl_pl_id [numeric]
 * - sgt [checkbox]
 * - BaselinkerCatalog [advancedManyToManyObjectRelation]
 * - Image [image]
 * - ObjectType [select]
 * - Ean [input]
 * - MPN [input]
 * - ImagesModel [imageGallery]
 */

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'Product',
   'name' => 'Product',
   'title' => 'Produkt',
   'description' => 'Towar, który można sprzedać',
   'creationDate' => NULL,
   'modificationDate' => 1761285879,
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
         'width' => 0,
         'height' => 0,
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
                     'name' => 'System',
                     'type' => NULL,
                     'region' => '',
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

Pełna nazwa produktu. Nazwa w językach obcych tłumaczona jest automatycznie w momencie publikacji produktu.',
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
                             'columnLength' => 100,
                             'regex' => '',
                             'regexFlags' =>
                            array (
                            ),
                             'unique' => false,
                             'showCharCount' => true,
                             'width' => 520,
                             'defaultValueGenerator' => '',
                          )),
                          1 =>
                          \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
                             'name' => 'Summary',
                             'title' => 'Summary',
                             'tooltip' => 'Podsumowanie produktu

Zwykle zwarty, techniczny opis z najważniejszymi parametrami

Rozważyć opcję automatycznego generowania po uzupełnieniu wymaganych danych',
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
                             'toolbarConfig' => '',
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
                              \Pimcore\Model\DataObject\ClassDefinition\Data\ImageGallery::__set_state(array(
                                 'name' => 'Infographics',
                                 'title' => 'Infographics',
                                 'tooltip' => 'Infografiki dla danego języka',
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
                     'layout' => '',
                     'border' => false,
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/tools.svg',
                     'labelWidth' => 120,
                     'labelAlign' => 'left',
                  )),
                  1 =>
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Model',
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                         'name' => 'Group',
                         'title' => 'Group',
                         'tooltip' => 'Grupa podstawowa

Kategoria podstawowa, do której należy produkt',
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
                            'classes' => 'Group',
                          ),
                        ),
                         'displayMode' => 'grid',
                         'pathFormatterClass' => '',
                         'assetInlineDownloadAllowed' => false,
                         'assetUploadPath' => '',
                         'allowToClearRelation' => false,
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                         'name' => 'Model',
                         'title' => 'Model',
                         'tooltip' => 'Model produktu

Projekt bazowy, na podstawie którego powstał dany produkt.

Przykładowo:
- ASPEN-03-BM, ASPEN-03-CAS, ASPEN-03-CM powstały na podstawie modelu ASPEN-03
- CARO-01-*, DALI-01-*, FINIQ-01-* to produkty powstałe na podstawie wspólnej bryły (modelu) RTV-TYP-01',
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'Width',
                         'title' => 'Width',
                         'tooltip' => 'Szerokość

Szerokość gabarytowa produktu',
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
                         'name' => 'Height',
                         'title' => 'Height',
                         'tooltip' => 'Wysokość

Wysokość gabarytowa produktu',
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'Depth',
                         'title' => 'Depth',
                         'tooltip' => 'Głębokość

Głębokość gabarytowa produktu',
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
                      5 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'Mass',
                         'title' => 'Mass',
                         'tooltip' => 'Waga

Waga netto produktu - bez opakowania',
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
                      6 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Country::__set_state(array(
                         'name' => 'COO',
                         'title' => 'Country Of Origin',
                         'tooltip' => 'Kraj pochodzenia produktu',
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
                         'columnLength' => 190,
                         'dynamicOptions' => false,
                         'defaultValueGenerator' => '',
                         'width' => '',
                         'optionsProviderType' => NULL,
                         'optionsProviderClass' => NULL,
                         'optionsProviderData' => NULL,
                         'restrictTo' => 'CN,PL',
                      )),
                      7 =>
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
                         'regex' => '^\\d{4}(\\s?\\d{2}(\\s?\\d{2})?)?$',
                         'regexFlags' =>
                        array (
                        ),
                         'unique' => false,
                         'showCharCount' => false,
                         'width' => '',
                         'defaultValueGenerator' => '',
                      )),
                      8 =>
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
                         'regex' => '^\\d{8}$',
                         'regexFlags' =>
                        array (
                        ),
                         'unique' => false,
                         'showCharCount' => false,
                         'width' => '',
                         'defaultValueGenerator' => '',
                      )),
                      9 =>
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
                         'regex' => '^(\\d{2})(\\.\\d{2}){0,3}$',
                         'regexFlags' =>
                        array (
                        ),
                         'unique' => false,
                         'showCharCount' => false,
                         'width' => '',
                         'defaultValueGenerator' => '',
                      )),
                      10 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                         'name' => 'Manufacturer',
                         'title' => 'Manufacturer',
                         'tooltip' => 'Manufacturer

Producent',
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
                            'classes' => 'User',
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
                      11 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation::__set_state(array(
                         'name' => 'Suppliers',
                         'title' => 'Suppliers',
                         'tooltip' => 'Dostawcy produktu lub części składowych (elementów) produktu',
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
                            'classes' => 'User',
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
                  2 =>
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
                         'title' => 'Groups',
                         'tooltip' => 'Grupy

Grupy, do których przypisany jest produkt.',
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
                                 'title' => 'Parameters',
                                 'tooltip' => 'Parametry

Parametry produktu podzielone na kolekcje i grupy.',
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
                                 'invisible' => false,
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
                  3 =>
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
                         'title' => 'Base Price',
                         'tooltip' => 'Cena bazowa

Cena zakupu produktu u producenta lub techniczny koszt wytworzenia przy własnej produkcji',
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
                          1 => 'USD',
                          2 => 'EUR',
                          3 => 'GBP',
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
                         'tooltip' => 'Wycena

Wycena produktu lub innej usługi związanej z produktem, np. transport',
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
                         'visibleFields' => 'fullpath,Countries',
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
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  4 =>
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
                         'title' => 'Images (2000 x 2000)',
                         'tooltip' => 'Zdjęcia produktu, zwykle na białym tle',
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
                         'height' => 240,
                         'width' => 240,
                      )),
                      1 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ImageGallery::__set_state(array(
                         'name' => 'Photos',
                         'title' => 'Photos (3127 x 2000 or 2000 x 2000)',
                         'tooltip' => 'Aranżacje',
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
                         'height' => 240,
                         'width' => 375,
                      )),
                      2 =>
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
                          \Pimcore\Model\DataObject\ClassDefinition\Data\ImageGallery::__set_state(array(
                             'name' => 'Infographics',
                             'title' => 'Infographics',
                             'tooltip' => 'Infografiki dla danego języka',
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
                      3 =>
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
                  5 =>
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
                         'name' => 'Packages',
                         'title' => 'Packages',
                         'tooltip' => 'Paczki

Paczki produktu',
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
                         'visibleFields' => 'key,Mass,Depth,Height,Width,Barcode,Volume',
                         'allowToCreateNewObject' => false,
                         'allowToClearRelation' => true,
                         'optimizedAdminLoading' => false,
                         'enableTextSelection' => false,
                         'visibleFieldDefinitions' =>
                        array (
                        ),
                         'width' => '',
                         'height' => '',
                         'allowedClassId' => 'Package',
                         'columns' =>
                        array (
                          0 =>
                          array (
                            'type' => 'number',
                            'position' => 1,
                            'key' => 'Quantity',
                            'label' => 'Quantity',
                            'value' => '1',
                          ),
                        ),
                         'columnKeys' =>
                        array (
                          0 => 'Quantity',
                        ),
                         'enableBatchEdit' => true,
                         'allowMultipleAssignments' => false,
                      )),
                      1 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
                         'name' => 'PackageCount',
                         'title' => 'Package Count',
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
                      2 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'PackagesMass',
                         'title' => 'Packages Mass',
                         'tooltip' => 'Masa paczek

Łączna masa paczek',
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
                      3 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\QuantityValue::__set_state(array(
                         'name' => 'PackagesVolume',
                         'title' => 'Packages Volume',
                         'tooltip' => 'Objętość paczek

Łączna objętość paczek',
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
                  6 =>
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
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
                      1 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections::__set_state(array(
                         'name' => 'Description',
                         'title' => 'Description',
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
                         'allowedTypes' =>
                        array (
                          0 => 'Image',
                          1 => 'ImageText',
                          2 => 'ImageWideo',
                          3 => 'TextImage',
                          4 => 'Text',
                          5 => 'TextWideo',
                          6 => 'WideoImage',
                          7 => 'Wideo',
                          8 => 'WideoText',
                        ),
                         'lazyLoading' => true,
                         'maxItems' => NULL,
                         'disallowAddRemove' => false,
                         'disallowReorder' => false,
                         'collapsed' => false,
                         'collapsible' => false,
                         'border' => false,
                      )),
                      2 =>
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
                  7 =>
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Codes',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Codes',
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
                         'title' => 'Barcode',
                         'tooltip' => 'Kod kreksowy

Domyślny kod kreskowy produktu tworzony na podstawie jego unikalnego identyfikatora (id z tabeli objects)',
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks::__set_state(array(
                         'name' => 'Codes',
                         'title' => 'Additional codes',
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
                         'allowedTypes' =>
                        array (
                          0 => 'IndexAgata',
                          1 => 'IndexMirjan24',
                        ),
                         'maxItems' => NULL,
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
                     'icon' => '/UI/barcode.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  8 =>
                  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                     'name' => 'Documents',
                     'type' => NULL,
                     'region' => NULL,
                     'title' => 'Documents',
                     'width' => '',
                     'height' => '',
                     'collapsible' => false,
                     'collapsed' => false,
                     'bodyStyle' => '',
                     'datatype' => 'layout',
                     'children' =>
                    array (
                      0 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation::__set_state(array(
                         'name' => 'Documents',
                         'title' => 'Documents',
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
                         'assetInlineDownloadAllowed' => true,
                         'assetUploadPath' => '',
                         'allowToClearRelation' => true,
                         'objectsAllowed' => false,
                         'assetsAllowed' => true,
                         'assetTypes' =>
                        array (
                          0 =>
                          array (
                            'assetTypes' => 'document',
                          ),
                        ),
                         'documentsAllowed' => false,
                         'documentTypes' =>
                        array (
                        ),
                         'enableTextSelection' => false,
                         'width' => '',
                         'height' => '',
                      )),
                      1 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                         'name' => 'Instruction',
                         'title' => 'Instruction',
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
                         'displayMode' => 'grid',
                         'pathFormatterClass' => '',
                         'assetInlineDownloadAllowed' => false,
                         'assetUploadPath' => '/INSTRUKCJE',
                         'allowToClearRelation' => true,
                         'objectsAllowed' => false,
                         'assetsAllowed' => true,
                         'assetTypes' =>
                        array (
                          0 =>
                          array (
                            'assetTypes' => 'document',
                          ),
                        ),
                         'documentsAllowed' => false,
                         'documentTypes' =>
                        array (
                        ),
                         'width' => '',
                      )),
                      2 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
                         'name' => 'InstructionUS',
                         'title' => 'Instruction US',
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
                         'displayMode' => 'grid',
                         'pathFormatterClass' => '',
                         'assetInlineDownloadAllowed' => false,
                         'assetUploadPath' => '/INSTRUKCJE',
                         'allowToClearRelation' => true,
                         'objectsAllowed' => false,
                         'assetsAllowed' => true,
                         'assetTypes' =>
                        array (
                          0 =>
                          array (
                            'assetTypes' => 'document',
                          ),
                        ),
                         'documentsAllowed' => false,
                         'documentTypes' =>
                        array (
                        ),
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
                     'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/file.svg',
                     'labelWidth' => 100,
                     'labelAlign' => 'left',
                  )),
                  9 =>
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
                             'labelWidth' => 90,
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
             'name' => 'System',
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
                         'tooltip' => 'Zdjęcie

Główne zdjęcie produktu, najczęściej z przeźroczystym tłem',
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
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
                         'name' => 'ObjectType',
                         'title' => 'Product type',
                         'tooltip' => 'Typ produktu

Wyróżnia się następujące typy produktu:

- VIRTUAL - Obiekt wirtualny, który nie może być sprzedany (brakuje mu ukonkretnień), natomiast pomaga w grupowaniu produktów i ułatwia uzupełnianie danych dzięki dziedziczeniu

- MODEL - Obiekt abstrakcyjny, który stanowi Produkt w rozumieniu wariantowości. Zawiera wspólne cechy produktów końcowych, które można opublikować końcowemu klientowi

- SKU - realizacja produktu, którą można składować w magazynie, jednak nie jest przeznaczona do bezpośredniej sprzedaży, ponieważ nie stanowi produktu pełnowartościowego, którym zainteresowany jest klient

- ACTUAL - Konkretna realizacja produktu. Można go sprzedać lub kupić, nie zawiera danych abstrakcyjnych.',
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
                            'key' => 'ACTUAL',
                            'value' => 'ACTUAL',
                          ),
                          1 =>
                          array (
                            'key' => 'VIRTUAL',
                            'value' => 'VIRTUAL',
                          ),
                          2 =>
                          array (
                            'key' => 'MODEL',
                            'value' => 'MODEL',
                          ),
                          3 =>
                          array (
                            'key' => 'SKU',
                            'value' => 'SKU',
                          ),
                        ),
                         'defaultValue' => 'ACTUAL',
                         'columnLength' => 190,
                         'dynamicOptions' => false,
                         'defaultValueGenerator' => '',
                         'width' => 400,
                         'optionsProviderType' => 'configure',
                         'optionsProviderClass' => '',
                         'optionsProviderData' => '',
                      )),
                      2 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                         'name' => 'Ean',
                         'title' => 'Ean',
                         'tooltip' => 'Kod EAN

Globalny identyfikator produktu (GTIN) z portalu mojegs1.pl.

Kod mozna przypisać za pomocą przycisku w górnym menu.',
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
                      3 =>
                      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                         'name' => 'MPN',
                         'title' => 'MPN',
                         'tooltip' => 'MPN - Manufacturer Product Number

Kod producenta',
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
                         'tooltip' => 'Zdjęcia wspólne dla modelu produktu

Przykładowo: rysunek z wymiarami',
                         'mandatory' => false,
                         'noteditable' => false,
                         'index' => false,
                         'locked' => false,
                         'style' => 'margin: 0;',
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
             'layout' => '',
             'border' => false,
             'icon' => '',
             'labelWidth' => 120,
             'labelAlign' => 'top',
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
   'icon' => '/UI/square-red.svg',
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
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => false,
    ),
    'search' =>
    array (
      'id' => true,
      'key' => true,
      'path' => true,
      'published' => false,
      'modificationDate' => true,
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
