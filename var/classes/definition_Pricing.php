<?php

/**
 * Inheritance: yes
 * Variants: no
 * Title: Pricing
 * Wycena
 *
 * Fields Summary:
 * - Restrictions [objectbricks]
 * - UseBasePrice [checkbox]
 * - Rules [fieldcollections]
 */

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'Parcel',
   'name' => 'Pricing',
   'title' => 'Pricing',
   'description' => 'Wycena',
   'creationDate' => NULL,
   'modificationDate' => 1748689791,
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
             'name' => 'Restrictions',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Restrictions',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks::__set_state(array(
                 'name' => 'Restrictions',
                 'title' => 'Restrictions',
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
                  0 => 'MaxPackageLength',
                  1 => 'MaxPackageSideLengthSum',
                  2 => 'MaxPackageWeight',
                  3 => 'LoadCarriers',
                  4 => 'MinimalProfit',
                  5 => 'SelectedGroups',
                  6 => 'BasePrice',
                  7 => 'ProductDimensions',
                  8 => 'ProductCOO',
                  9 => 'MinimalPercentageProfit',
                  10 => 'MinimalMarkup',
                ),
                 'maxItems' => NULL,
                 'border' => false,
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
                 'html' => '<h1>Ograniczenia</h1>

Wymagania, które musi spełnić produkt, aby wycena była możliwa',
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
             'icon' => '/bundles/pimcoreadmin/img/flat-color-icons/ruler.svg',
             'labelWidth' => 100,
             'labelAlign' => 'left',
          )),
          1 => 
          \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
             'name' => 'Pricing',
             'type' => NULL,
             'region' => NULL,
             'title' => 'Pricing',
             'width' => '',
             'height' => '',
             'collapsible' => false,
             'collapsed' => false,
             'bodyStyle' => '',
             'datatype' => 'layout',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Checkbox::__set_state(array(
                 'name' => 'UseBasePrice',
                 'title' => 'Use Base Price',
                 'tooltip' => 'Czy użyć w ceny bazowej jako podstawa obliczeń? W przeciwnym wypadku użyta zostanie wartość 0,00 zł',
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
                 'defaultValue' => 1,
                 'defaultValueGenerator' => '',
              )),
              1 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections::__set_state(array(
                 'name' => 'Rules',
                 'title' => 'Rules',
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
                  0 => 'Factor',
                  1 => 'Surcharge',
                  2 => 'ParcelMassVolume',
                  3 => 'Pricing',
                  4 => 'ParcelVolume',
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
                 'html' => '<h1>Reguły</h1>

Kroki służące do wyznaczenia końcowej ceny usługi',
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
             'labelWidth' => 180,
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
   'icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGNpcmNsZSBmaWxsPSIjMjhBMDUwIiBjeD0iOCIgY3k9IjgiIHI9IjciLz4NCjxjaXJjbGUgZmlsbD0iIzI4NzhGMCIgY3g9IjE2IiBjeT0iMTYiIHI9IjciLz4NCjxnPg0KCTxwYXRoIGZpbGw9IiNGRkZGRkYiIGQ9Ik05LDkuN2MwLTAuMy0wLjEtMC41LTAuMi0wLjZDOC42LDguOSw4LjQsOC44LDgsOC42UzcuNCw4LjQsNy4xLDguM1M2LjYsOCw2LjQsNy44QzYuMyw3LjYsNi4xLDcuNCw2LDcuMg0KCQlDNS45LDcsNS45LDYuNyw1LjksNi40YzAtMC42LDAuMi0xLDAuNS0xLjRzMC44LTAuNiwxLjQtMC42di0xaDAuOHYxLjFjMC42LDAuMSwxLDAuMywxLjQsMC43czAuNSwwLjksMC41LDEuNkg5DQoJCWMwLTAuNS0wLjEtMC44LTAuMi0xQzguNiw1LjYsOC40LDUuNSw4LjEsNS41Yy0wLjMsMC0wLjUsMC4xLTAuNiwwLjJTNy4zLDYuMSw3LjMsNi40YzAsMC4yLDAuMSwwLjQsMC4yLDAuNnMwLjQsMC4zLDAuOCwwLjUNCgkJYzAuNCwwLjIsMC43LDAuMywxLDAuNHMwLjUsMC4zLDAuNiwwLjVzMC4zLDAuNCwwLjQsMC42czAuMSwwLjUsMC4xLDAuOGMwLDAuNi0wLjIsMS0wLjUsMS4zcy0wLjgsMC41LTEuNCwwLjZ2MUg3Ljd2LTENCgkJQzcsMTEuNiw2LjUsMTEuNCw2LjEsMTFjLTAuMy0wLjQtMC41LTEtMC41LTEuNkg3YzAsMC40LDAuMSwwLjcsMC4zLDAuOWMwLjIsMC4yLDAuNCwwLjMsMC44LDAuM2MwLjMsMCwwLjUtMC4xLDAuNy0wLjINCgkJUzksMTAsOSw5Ljd6Ii8+DQo8L2c+DQo8Zz4NCgk8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMTYuOSwxN2gtMS42YzAsMC41LDAuMSwwLjgsMC40LDEuMWMwLjIsMC4yLDAuNiwwLjQsMS4xLDAuNGMwLjMsMCwwLjctMC4xLDAuOS0wLjJsMC4yLDEuMg0KCQljLTAuNCwwLjEtMC44LDAuMS0xLjMsMC4xYy0wLjksMC0xLjUtMC4yLTItMC43cy0wLjgtMS4xLTAuOC0xLjlIMTN2LTAuN2gwLjh2LTAuNkgxM1YxNWgwLjhjMC4xLTAuOCwwLjMtMS41LDAuOS0xLjkNCgkJYzAuNS0wLjUsMS4yLTAuNywyLjEtMC43YzAuMywwLDAuNywwLjEsMS4yLDAuMmwtMC4yLDEuMmMtMC4zLTAuMS0wLjYtMC4yLTAuOS0wLjJjLTAuOSwwLTEuNCwwLjUtMS41LDEuNEgxN3YwLjdoLTEuNnYwLjZIMTdWMTcNCgkJSDE2Ljl6Ii8+DQo8L2c+DQo8L3N2Zz4NCg==',
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
    0 => 
    \Pimcore\Model\DataObject\ClassDefinition\Data\Countrymultiselect::__set_state(array(
       'name' => 'Countries',
       'title' => 'Countries',
       'tooltip' => 'Kraje

Kraj, w którym obowiązuje usługa
',
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
          'key' => 'Afghanistan',
          'value' => 'AF',
        ),
        1 => 
        array (
          'key' => 'Albania',
          'value' => 'AL',
        ),
        2 => 
        array (
          'key' => 'Algeria',
          'value' => 'DZ',
        ),
        3 => 
        array (
          'key' => 'American Samoa',
          'value' => 'AS',
        ),
        4 => 
        array (
          'key' => 'Andorra',
          'value' => 'AD',
        ),
        5 => 
        array (
          'key' => 'Angola',
          'value' => 'AO',
        ),
        6 => 
        array (
          'key' => 'Anguilla',
          'value' => 'AI',
        ),
        7 => 
        array (
          'key' => 'Antarctica',
          'value' => 'AQ',
        ),
        8 => 
        array (
          'key' => 'Antigua & Barbuda',
          'value' => 'AG',
        ),
        9 => 
        array (
          'key' => 'Argentina',
          'value' => 'AR',
        ),
        10 => 
        array (
          'key' => 'Armenia',
          'value' => 'AM',
        ),
        11 => 
        array (
          'key' => 'Aruba',
          'value' => 'AW',
        ),
        12 => 
        array (
          'key' => 'Australia',
          'value' => 'AU',
        ),
        13 => 
        array (
          'key' => 'Austria',
          'value' => 'AT',
        ),
        14 => 
        array (
          'key' => 'Azerbaijan',
          'value' => 'AZ',
        ),
        15 => 
        array (
          'key' => 'Bahamas',
          'value' => 'BS',
        ),
        16 => 
        array (
          'key' => 'Bahrain',
          'value' => 'BH',
        ),
        17 => 
        array (
          'key' => 'Bangladesh',
          'value' => 'BD',
        ),
        18 => 
        array (
          'key' => 'Barbados',
          'value' => 'BB',
        ),
        19 => 
        array (
          'key' => 'Belarus',
          'value' => 'BY',
        ),
        20 => 
        array (
          'key' => 'Belgium',
          'value' => 'BE',
        ),
        21 => 
        array (
          'key' => 'Belize',
          'value' => 'BZ',
        ),
        22 => 
        array (
          'key' => 'Benin',
          'value' => 'BJ',
        ),
        23 => 
        array (
          'key' => 'Bermuda',
          'value' => 'BM',
        ),
        24 => 
        array (
          'key' => 'Bhutan',
          'value' => 'BT',
        ),
        25 => 
        array (
          'key' => 'Bolivia',
          'value' => 'BO',
        ),
        26 => 
        array (
          'key' => 'Bosnia & Herzegovina',
          'value' => 'BA',
        ),
        27 => 
        array (
          'key' => 'Botswana',
          'value' => 'BW',
        ),
        28 => 
        array (
          'key' => 'Bouvet Island',
          'value' => 'BV',
        ),
        29 => 
        array (
          'key' => 'Brazil',
          'value' => 'BR',
        ),
        30 => 
        array (
          'key' => 'British Indian Ocean Territory',
          'value' => 'IO',
        ),
        31 => 
        array (
          'key' => 'British Virgin Islands',
          'value' => 'VG',
        ),
        32 => 
        array (
          'key' => 'Brunei',
          'value' => 'BN',
        ),
        33 => 
        array (
          'key' => 'Bulgaria',
          'value' => 'BG',
        ),
        34 => 
        array (
          'key' => 'Burkina Faso',
          'value' => 'BF',
        ),
        35 => 
        array (
          'key' => 'Burundi',
          'value' => 'BI',
        ),
        36 => 
        array (
          'key' => 'Cambodia',
          'value' => 'KH',
        ),
        37 => 
        array (
          'key' => 'Cameroon',
          'value' => 'CM',
        ),
        38 => 
        array (
          'key' => 'Canada',
          'value' => 'CA',
        ),
        39 => 
        array (
          'key' => 'Cape Verde',
          'value' => 'CV',
        ),
        40 => 
        array (
          'key' => 'Caribbean Netherlands',
          'value' => 'BQ',
        ),
        41 => 
        array (
          'key' => 'Cayman Islands',
          'value' => 'KY',
        ),
        42 => 
        array (
          'key' => 'Central African Republic',
          'value' => 'CF',
        ),
        43 => 
        array (
          'key' => 'Chad',
          'value' => 'TD',
        ),
        44 => 
        array (
          'key' => 'Chile',
          'value' => 'CL',
        ),
        45 => 
        array (
          'key' => 'China',
          'value' => 'CN',
        ),
        46 => 
        array (
          'key' => 'Christmas Island',
          'value' => 'CX',
        ),
        47 => 
        array (
          'key' => 'Cocos (Keeling) Islands',
          'value' => 'CC',
        ),
        48 => 
        array (
          'key' => 'Colombia',
          'value' => 'CO',
        ),
        49 => 
        array (
          'key' => 'Comoros',
          'value' => 'KM',
        ),
        50 => 
        array (
          'key' => 'Congo - Brazzaville',
          'value' => 'CG',
        ),
        51 => 
        array (
          'key' => 'Congo - Kinshasa',
          'value' => 'CD',
        ),
        52 => 
        array (
          'key' => 'Cook Islands',
          'value' => 'CK',
        ),
        53 => 
        array (
          'key' => 'Costa Rica',
          'value' => 'CR',
        ),
        54 => 
        array (
          'key' => 'Croatia',
          'value' => 'HR',
        ),
        55 => 
        array (
          'key' => 'Cuba',
          'value' => 'CU',
        ),
        56 => 
        array (
          'key' => 'Curaçao',
          'value' => 'CW',
        ),
        57 => 
        array (
          'key' => 'Cyprus',
          'value' => 'CY',
        ),
        58 => 
        array (
          'key' => 'Czechia',
          'value' => 'CZ',
        ),
        59 => 
        array (
          'key' => 'Côte d’Ivoire',
          'value' => 'CI',
        ),
        60 => 
        array (
          'key' => 'Denmark',
          'value' => 'DK',
        ),
        61 => 
        array (
          'key' => 'Djibouti',
          'value' => 'DJ',
        ),
        62 => 
        array (
          'key' => 'Dominica',
          'value' => 'DM',
        ),
        63 => 
        array (
          'key' => 'Dominican Republic',
          'value' => 'DO',
        ),
        64 => 
        array (
          'key' => 'Ecuador',
          'value' => 'EC',
        ),
        65 => 
        array (
          'key' => 'Egypt',
          'value' => 'EG',
        ),
        66 => 
        array (
          'key' => 'El Salvador',
          'value' => 'SV',
        ),
        67 => 
        array (
          'key' => 'Equatorial Guinea',
          'value' => 'GQ',
        ),
        68 => 
        array (
          'key' => 'Eritrea',
          'value' => 'ER',
        ),
        69 => 
        array (
          'key' => 'Estonia',
          'value' => 'EE',
        ),
        70 => 
        array (
          'key' => 'Eswatini',
          'value' => 'SZ',
        ),
        71 => 
        array (
          'key' => 'Ethiopia',
          'value' => 'ET',
        ),
        72 => 
        array (
          'key' => 'Falkland Islands',
          'value' => 'FK',
        ),
        73 => 
        array (
          'key' => 'Faroe Islands',
          'value' => 'FO',
        ),
        74 => 
        array (
          'key' => 'Fiji',
          'value' => 'FJ',
        ),
        75 => 
        array (
          'key' => 'Finland',
          'value' => 'FI',
        ),
        76 => 
        array (
          'key' => 'France',
          'value' => 'FR',
        ),
        77 => 
        array (
          'key' => 'French Guiana',
          'value' => 'GF',
        ),
        78 => 
        array (
          'key' => 'French Polynesia',
          'value' => 'PF',
        ),
        79 => 
        array (
          'key' => 'French Southern Territories',
          'value' => 'TF',
        ),
        80 => 
        array (
          'key' => 'Gabon',
          'value' => 'GA',
        ),
        81 => 
        array (
          'key' => 'Gambia',
          'value' => 'GM',
        ),
        82 => 
        array (
          'key' => 'Georgia',
          'value' => 'GE',
        ),
        83 => 
        array (
          'key' => 'Germany',
          'value' => 'DE',
        ),
        84 => 
        array (
          'key' => 'Ghana',
          'value' => 'GH',
        ),
        85 => 
        array (
          'key' => 'Gibraltar',
          'value' => 'GI',
        ),
        86 => 
        array (
          'key' => 'Greece',
          'value' => 'GR',
        ),
        87 => 
        array (
          'key' => 'Greenland',
          'value' => 'GL',
        ),
        88 => 
        array (
          'key' => 'Grenada',
          'value' => 'GD',
        ),
        89 => 
        array (
          'key' => 'Guadeloupe',
          'value' => 'GP',
        ),
        90 => 
        array (
          'key' => 'Guam',
          'value' => 'GU',
        ),
        91 => 
        array (
          'key' => 'Guatemala',
          'value' => 'GT',
        ),
        92 => 
        array (
          'key' => 'Guernsey',
          'value' => 'GG',
        ),
        93 => 
        array (
          'key' => 'Guinea',
          'value' => 'GN',
        ),
        94 => 
        array (
          'key' => 'Guinea-Bissau',
          'value' => 'GW',
        ),
        95 => 
        array (
          'key' => 'Guyana',
          'value' => 'GY',
        ),
        96 => 
        array (
          'key' => 'Haiti',
          'value' => 'HT',
        ),
        97 => 
        array (
          'key' => 'Heard & McDonald Islands',
          'value' => 'HM',
        ),
        98 => 
        array (
          'key' => 'Honduras',
          'value' => 'HN',
        ),
        99 => 
        array (
          'key' => 'Hong Kong SAR China',
          'value' => 'HK',
        ),
        100 => 
        array (
          'key' => 'Hungary',
          'value' => 'HU',
        ),
        101 => 
        array (
          'key' => 'Iceland',
          'value' => 'IS',
        ),
        102 => 
        array (
          'key' => 'India',
          'value' => 'IN',
        ),
        103 => 
        array (
          'key' => 'Indonesia',
          'value' => 'ID',
        ),
        104 => 
        array (
          'key' => 'Iran',
          'value' => 'IR',
        ),
        105 => 
        array (
          'key' => 'Iraq',
          'value' => 'IQ',
        ),
        106 => 
        array (
          'key' => 'Ireland',
          'value' => 'IE',
        ),
        107 => 
        array (
          'key' => 'Isle of Man',
          'value' => 'IM',
        ),
        108 => 
        array (
          'key' => 'Israel',
          'value' => 'IL',
        ),
        109 => 
        array (
          'key' => 'Italy',
          'value' => 'IT',
        ),
        110 => 
        array (
          'key' => 'Jamaica',
          'value' => 'JM',
        ),
        111 => 
        array (
          'key' => 'Japan',
          'value' => 'JP',
        ),
        112 => 
        array (
          'key' => 'Jersey',
          'value' => 'JE',
        ),
        113 => 
        array (
          'key' => 'Jordan',
          'value' => 'JO',
        ),
        114 => 
        array (
          'key' => 'Kazakhstan',
          'value' => 'KZ',
        ),
        115 => 
        array (
          'key' => 'Kenya',
          'value' => 'KE',
        ),
        116 => 
        array (
          'key' => 'Kiribati',
          'value' => 'KI',
        ),
        117 => 
        array (
          'key' => 'Kuwait',
          'value' => 'KW',
        ),
        118 => 
        array (
          'key' => 'Kyrgyzstan',
          'value' => 'KG',
        ),
        119 => 
        array (
          'key' => 'Laos',
          'value' => 'LA',
        ),
        120 => 
        array (
          'key' => 'Latvia',
          'value' => 'LV',
        ),
        121 => 
        array (
          'key' => 'Lebanon',
          'value' => 'LB',
        ),
        122 => 
        array (
          'key' => 'Lesotho',
          'value' => 'LS',
        ),
        123 => 
        array (
          'key' => 'Liberia',
          'value' => 'LR',
        ),
        124 => 
        array (
          'key' => 'Libya',
          'value' => 'LY',
        ),
        125 => 
        array (
          'key' => 'Liechtenstein',
          'value' => 'LI',
        ),
        126 => 
        array (
          'key' => 'Lithuania',
          'value' => 'LT',
        ),
        127 => 
        array (
          'key' => 'Luxembourg',
          'value' => 'LU',
        ),
        128 => 
        array (
          'key' => 'Macao SAR China',
          'value' => 'MO',
        ),
        129 => 
        array (
          'key' => 'Madagascar',
          'value' => 'MG',
        ),
        130 => 
        array (
          'key' => 'Malawi',
          'value' => 'MW',
        ),
        131 => 
        array (
          'key' => 'Malaysia',
          'value' => 'MY',
        ),
        132 => 
        array (
          'key' => 'Maldives',
          'value' => 'MV',
        ),
        133 => 
        array (
          'key' => 'Mali',
          'value' => 'ML',
        ),
        134 => 
        array (
          'key' => 'Malta',
          'value' => 'MT',
        ),
        135 => 
        array (
          'key' => 'Marshall Islands',
          'value' => 'MH',
        ),
        136 => 
        array (
          'key' => 'Martinique',
          'value' => 'MQ',
        ),
        137 => 
        array (
          'key' => 'Mauritania',
          'value' => 'MR',
        ),
        138 => 
        array (
          'key' => 'Mauritius',
          'value' => 'MU',
        ),
        139 => 
        array (
          'key' => 'Mayotte',
          'value' => 'YT',
        ),
        140 => 
        array (
          'key' => 'Mexico',
          'value' => 'MX',
        ),
        141 => 
        array (
          'key' => 'Micronesia',
          'value' => 'FM',
        ),
        142 => 
        array (
          'key' => 'Moldova',
          'value' => 'MD',
        ),
        143 => 
        array (
          'key' => 'Monaco',
          'value' => 'MC',
        ),
        144 => 
        array (
          'key' => 'Mongolia',
          'value' => 'MN',
        ),
        145 => 
        array (
          'key' => 'Montenegro',
          'value' => 'ME',
        ),
        146 => 
        array (
          'key' => 'Montserrat',
          'value' => 'MS',
        ),
        147 => 
        array (
          'key' => 'Morocco',
          'value' => 'MA',
        ),
        148 => 
        array (
          'key' => 'Mozambique',
          'value' => 'MZ',
        ),
        149 => 
        array (
          'key' => 'Myanmar (Burma)',
          'value' => 'MM',
        ),
        150 => 
        array (
          'key' => 'Namibia',
          'value' => 'NA',
        ),
        151 => 
        array (
          'key' => 'Nauru',
          'value' => 'NR',
        ),
        152 => 
        array (
          'key' => 'Nepal',
          'value' => 'NP',
        ),
        153 => 
        array (
          'key' => 'Netherlands',
          'value' => 'NL',
        ),
        154 => 
        array (
          'key' => 'New Caledonia',
          'value' => 'NC',
        ),
        155 => 
        array (
          'key' => 'New Zealand',
          'value' => 'NZ',
        ),
        156 => 
        array (
          'key' => 'Nicaragua',
          'value' => 'NI',
        ),
        157 => 
        array (
          'key' => 'Niger',
          'value' => 'NE',
        ),
        158 => 
        array (
          'key' => 'Nigeria',
          'value' => 'NG',
        ),
        159 => 
        array (
          'key' => 'Niue',
          'value' => 'NU',
        ),
        160 => 
        array (
          'key' => 'Norfolk Island',
          'value' => 'NF',
        ),
        161 => 
        array (
          'key' => 'North Korea',
          'value' => 'KP',
        ),
        162 => 
        array (
          'key' => 'North Macedonia',
          'value' => 'MK',
        ),
        163 => 
        array (
          'key' => 'Northern Mariana Islands',
          'value' => 'MP',
        ),
        164 => 
        array (
          'key' => 'Norway',
          'value' => 'NO',
        ),
        165 => 
        array (
          'key' => 'Oman',
          'value' => 'OM',
        ),
        166 => 
        array (
          'key' => 'Pakistan',
          'value' => 'PK',
        ),
        167 => 
        array (
          'key' => 'Palau',
          'value' => 'PW',
        ),
        168 => 
        array (
          'key' => 'Palestinian Territories',
          'value' => 'PS',
        ),
        169 => 
        array (
          'key' => 'Panama',
          'value' => 'PA',
        ),
        170 => 
        array (
          'key' => 'Papua New Guinea',
          'value' => 'PG',
        ),
        171 => 
        array (
          'key' => 'Paraguay',
          'value' => 'PY',
        ),
        172 => 
        array (
          'key' => 'Peru',
          'value' => 'PE',
        ),
        173 => 
        array (
          'key' => 'Philippines',
          'value' => 'PH',
        ),
        174 => 
        array (
          'key' => 'Pitcairn Islands',
          'value' => 'PN',
        ),
        175 => 
        array (
          'key' => 'Poland',
          'value' => 'PL',
        ),
        176 => 
        array (
          'key' => 'Portugal',
          'value' => 'PT',
        ),
        177 => 
        array (
          'key' => 'Puerto Rico',
          'value' => 'PR',
        ),
        178 => 
        array (
          'key' => 'Qatar',
          'value' => 'QA',
        ),
        179 => 
        array (
          'key' => 'Romania',
          'value' => 'RO',
        ),
        180 => 
        array (
          'key' => 'Russia',
          'value' => 'RU',
        ),
        181 => 
        array (
          'key' => 'Rwanda',
          'value' => 'RW',
        ),
        182 => 
        array (
          'key' => 'Réunion',
          'value' => 'RE',
        ),
        183 => 
        array (
          'key' => 'Samoa',
          'value' => 'WS',
        ),
        184 => 
        array (
          'key' => 'San Marino',
          'value' => 'SM',
        ),
        185 => 
        array (
          'key' => 'Saudi Arabia',
          'value' => 'SA',
        ),
        186 => 
        array (
          'key' => 'Senegal',
          'value' => 'SN',
        ),
        187 => 
        array (
          'key' => 'Serbia',
          'value' => 'RS',
        ),
        188 => 
        array (
          'key' => 'Seychelles',
          'value' => 'SC',
        ),
        189 => 
        array (
          'key' => 'Sierra Leone',
          'value' => 'SL',
        ),
        190 => 
        array (
          'key' => 'Singapore',
          'value' => 'SG',
        ),
        191 => 
        array (
          'key' => 'Sint Maarten',
          'value' => 'SX',
        ),
        192 => 
        array (
          'key' => 'Slovakia',
          'value' => 'SK',
        ),
        193 => 
        array (
          'key' => 'Slovenia',
          'value' => 'SI',
        ),
        194 => 
        array (
          'key' => 'Solomon Islands',
          'value' => 'SB',
        ),
        195 => 
        array (
          'key' => 'Somalia',
          'value' => 'SO',
        ),
        196 => 
        array (
          'key' => 'South Africa',
          'value' => 'ZA',
        ),
        197 => 
        array (
          'key' => 'South Georgia & South Sandwich Islands',
          'value' => 'GS',
        ),
        198 => 
        array (
          'key' => 'South Korea',
          'value' => 'KR',
        ),
        199 => 
        array (
          'key' => 'South Sudan',
          'value' => 'SS',
        ),
        200 => 
        array (
          'key' => 'Spain',
          'value' => 'ES',
        ),
        201 => 
        array (
          'key' => 'Sri Lanka',
          'value' => 'LK',
        ),
        202 => 
        array (
          'key' => 'St. Barthélemy',
          'value' => 'BL',
        ),
        203 => 
        array (
          'key' => 'St. Helena',
          'value' => 'SH',
        ),
        204 => 
        array (
          'key' => 'St. Kitts & Nevis',
          'value' => 'KN',
        ),
        205 => 
        array (
          'key' => 'St. Lucia',
          'value' => 'LC',
        ),
        206 => 
        array (
          'key' => 'St. Martin',
          'value' => 'MF',
        ),
        207 => 
        array (
          'key' => 'St. Pierre & Miquelon',
          'value' => 'PM',
        ),
        208 => 
        array (
          'key' => 'St. Vincent & Grenadines',
          'value' => 'VC',
        ),
        209 => 
        array (
          'key' => 'Sudan',
          'value' => 'SD',
        ),
        210 => 
        array (
          'key' => 'Suriname',
          'value' => 'SR',
        ),
        211 => 
        array (
          'key' => 'Svalbard & Jan Mayen',
          'value' => 'SJ',
        ),
        212 => 
        array (
          'key' => 'Sweden',
          'value' => 'SE',
        ),
        213 => 
        array (
          'key' => 'Switzerland',
          'value' => 'CH',
        ),
        214 => 
        array (
          'key' => 'Syria',
          'value' => 'SY',
        ),
        215 => 
        array (
          'key' => 'São Tomé & Príncipe',
          'value' => 'ST',
        ),
        216 => 
        array (
          'key' => 'Taiwan',
          'value' => 'TW',
        ),
        217 => 
        array (
          'key' => 'Tajikistan',
          'value' => 'TJ',
        ),
        218 => 
        array (
          'key' => 'Tanzania',
          'value' => 'TZ',
        ),
        219 => 
        array (
          'key' => 'Thailand',
          'value' => 'TH',
        ),
        220 => 
        array (
          'key' => 'Timor-Leste',
          'value' => 'TL',
        ),
        221 => 
        array (
          'key' => 'Togo',
          'value' => 'TG',
        ),
        222 => 
        array (
          'key' => 'Tokelau',
          'value' => 'TK',
        ),
        223 => 
        array (
          'key' => 'Tonga',
          'value' => 'TO',
        ),
        224 => 
        array (
          'key' => 'Trinidad & Tobago',
          'value' => 'TT',
        ),
        225 => 
        array (
          'key' => 'Tunisia',
          'value' => 'TN',
        ),
        226 => 
        array (
          'key' => 'Turkey',
          'value' => 'TR',
        ),
        227 => 
        array (
          'key' => 'Turkmenistan',
          'value' => 'TM',
        ),
        228 => 
        array (
          'key' => 'Turks & Caicos Islands',
          'value' => 'TC',
        ),
        229 => 
        array (
          'key' => 'Tuvalu',
          'value' => 'TV',
        ),
        230 => 
        array (
          'key' => 'U.S. Outlying Islands',
          'value' => 'UM',
        ),
        231 => 
        array (
          'key' => 'U.S. Virgin Islands',
          'value' => 'VI',
        ),
        232 => 
        array (
          'key' => 'Uganda',
          'value' => 'UG',
        ),
        233 => 
        array (
          'key' => 'Ukraine',
          'value' => 'UA',
        ),
        234 => 
        array (
          'key' => 'United Arab Emirates',
          'value' => 'AE',
        ),
        235 => 
        array (
          'key' => 'United Kingdom',
          'value' => 'GB',
        ),
        236 => 
        array (
          'key' => 'United States',
          'value' => 'US',
        ),
        237 => 
        array (
          'key' => 'Uruguay',
          'value' => 'UY',
        ),
        238 => 
        array (
          'key' => 'Uzbekistan',
          'value' => 'UZ',
        ),
        239 => 
        array (
          'key' => 'Vanuatu',
          'value' => 'VU',
        ),
        240 => 
        array (
          'key' => 'Vatican City',
          'value' => 'VA',
        ),
        241 => 
        array (
          'key' => 'Venezuela',
          'value' => 'VE',
        ),
        242 => 
        array (
          'key' => 'Vietnam',
          'value' => 'VN',
        ),
        243 => 
        array (
          'key' => 'Wallis & Futuna',
          'value' => 'WF',
        ),
        244 => 
        array (
          'key' => 'Western Sahara',
          'value' => 'EH',
        ),
        245 => 
        array (
          'key' => 'Yemen',
          'value' => 'YE',
        ),
        246 => 
        array (
          'key' => 'Zambia',
          'value' => 'ZM',
        ),
        247 => 
        array (
          'key' => 'Zimbabwe',
          'value' => 'ZW',
        ),
        248 => 
        array (
          'key' => 'Åland Islands',
          'value' => 'AX',
        ),
      ),
       'maxItems' => NULL,
       'renderType' => 'tags',
       'dynamicOptions' => false,
       'defaultValue' => NULL,
       'height' => '',
       'width' => '',
       'defaultValueGenerator' => '',
       'optionsProviderType' => NULL,
       'optionsProviderClass' => NULL,
       'optionsProviderData' => NULL,
       'restrictTo' => '',
    )),
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
