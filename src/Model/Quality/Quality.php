<?php

namespace App\Model\Quality;

use \Pimcore\Model\DataObject;
use \Pimcore\Model\DataObject\Product;
use \Pimcore\Model\DataObject\ProductSet;

class Quality
{

    /**
     * Returns object quality description splitted by required and optional metrics
     *
     * @param DataObject $obj
     * @return array|null
     */
    public function getQuality(DataObject $obj): array|null
    {
        if($obj instanceof Product) {
            return $this->getProductQuality($obj);
        }

        if($obj instanceof ProductSet) {
            return $this->getProductSetQuality($obj);
        }

        return null; // when object type is not supported
    }

    public function getProductQuality(Product $object): array
    {
        $required = [];
        $optional = [];

        $required[] = [
            "text" => "Pełna nazwa PL",
            "goal" => 1,
            "actual" => $object->getName("pl") ? 1 : 0
        ];

        $required[] = [
            "text" => "Pełna nazwa EN",
            "goal" => 1,
            "actual" => $object->getName("en") ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis PL",
            "goal" => 1,
            "actual" => $object->getSummary("pl") ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis EN",
            "goal" => 1,
            "actual" => $object->getSummary("en") ? 1 : 0
        ];

        $languages = \Pimcore\Tool::getValidLanguages();

        $nameRestLanguages = 1;
        $descRestLanguages = 1;

        foreach ($languages as $lang) {
            if(!$object->getName($lang)) {
                $nameRestLanguages = 0;
            }

            if(!$object->getSummary($lang)) {
                $descRestLanguages = 0;
            }
        }

        $optional[] = [
            "text" => "Pełna nazwa (pozostałe języki)",
            "goal" => 1,
            "actual" => $nameRestLanguages
        ];

        $optional[] = [
            "text" => "Opis (pozostałe języki)",
            "goal" => 1,
            "actual" => $descRestLanguages
        ];

        $required[] = [
            "text" => "Zdjęcie główne",
            "goal" => 1,
            "actual" => $object->getImage() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kod EAN",
            "goal" => 1,
            "actual" => $object->getEan() ? 1 : 0
        ];

        $optional[] = [
            "text" => "Kod u dostawcy",
            "goal" => 1,
            "actual" => $object->getMPN() ? 1 : 0
        ];

        $required[] = [
            "text" => "Zdjęcia dla modelu produktu",
            "goal" => 1,
            "actual" => $object->getImagesModel() ? Min(count($object->getImagesModel()->getItems()), 1)  : 0
        ];

        $required[] = [
            "text" => "Status",
            "goal" => 1,
            "actual" => $object->getStatus() ? 1 : 0
        ];

        $required[] = [
            "text" => "Typ produktu",
            "goal" => 1,
            "actual" => $object->getObjectType() ? 1 : 0
        ];

        $required[] = [
            "text" => "Grupa podstawowa",
            "goal" => 1,
            "actual" => ($object->getGroup()) ? 1 : 0
        ];

        $required[] = [
            "text" => "Model",
            "goal" => 1,
            "actual" => ($object->getModel()) ? 1 : 0
        ];

        $required[] = [
            "text" => "Wymiary - Szerokość",
            "goal" => 1,
            "actual" => ($object->getWidth() and $object->getWidth()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Wymiary - Wysokość",
            "goal" => 1,
            "actual" => ($object->getHeight() and $object->getHeight()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Wymiary - Głębokość",
            "goal" => 1,
            "actual" => ($object->getDepth() and $object->getDepth()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Waga",
            "goal" => 1,
            "actual" => ($object->getMass() and $object->getMass()->getValue()) ? 1 : 0
        ];

        $required[] = [
            "text" => "Kraj pochodzenia",
            "goal" => 1,
            "actual" => $object->getCOO() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kod CN",
            "goal" => 1,
            "actual" => $object->getCN() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kod GPC",
            "goal" => 1,
            "actual" => $object->getGPC() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kod PKWiU",
            "goal" => 1,
            "actual" => $object->getPKWIU() ? 1 : 0
        ];

        $required[] = [
            "text" => "Producent",
            "goal" => 1,
            "actual" => $object->getManufacturer() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kolekcja",
            "goal" => 1,
            "actual" => $object->getSerie() ? 1 : 0
        ];

        $required[] = [
            "text" => "Grupy",
            "goal" => 1,
            "actual" => ($object->getGroups() && count($object->getGroups()) > 1) ? 1 : 0
        ];

        $required[] = [
            "text" => "Parametry",
            "goal" => 1,
            "actual" => ($object->getParameters() and $object->getParameters()->getGroups()) ? 1 : 0
        ];

        $allParametersCount = 0;
        $filledParametersCount = 0;

        foreach ($object->getParameters()->getGroups() as $group) {
            foreach ($group->getKeys() as $gkey) {
                $allParametersCount += 1;
                $filledParametersCount += $gkey->getValue() ? 1 : 0;
            }
        }

        $required[] = [
            "text" => "Parametry - wartości",
            "goal" => $allParametersCount,
            "actual" => $filledParametersCount
        ];

        $required[] = [
            "text" => "Parametry Allegro",
            "goal" => 1,
            "actual" => $object->getParametersAllegro() && count($object->getParametersAllegro()->getGroups()) ? 1 : 0
        ];

        $allParametersAllegroCount = 0;
        $filledParametersAllegroCount = 0;

        foreach ($object->getParametersAllegro()->getGroups() as $group) {
            foreach ($group->getKeys() as $gkey) {
                $allParametersAllegroCount += 1;
                $filledParametersAllegroCount += $gkey->getValue() ? 1 : 0;
            }
        }

        $required[] = [
            "text" => "Parametry allegro - wartości",
            "goal" => $allParametersAllegroCount,
            "actual" => $filledParametersAllegroCount
        ];

        $optional[] = [
            "text" => "Kategoria Google",
            "goal" => 1,
            "actual" => $object->getGoogleCategory() ? 1 : 0
        ];

        $required[] = [
            "text" => "Cena bazowa",
            "goal" => 1,
            "actual" => ($object->getBasePrice() and $object->getBasePrice()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Ceny",
            "goal" => 1,
            "actual" => ($object->getPrice() and count($object->getPrice()) > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Zdjęcia",
            "goal" => 1,
            "actual" => $object->getImages() ? Min(count($object->getImages()->getItems()), 1)  : 0
        ];

        $required[] = [
            "text" => "Aranżacje",
            "goal" => 1,
            "actual" => $object->getPhotos() ? Min(count($object->getPhotos()->getItems()), 1)  : 0
        ];

        $required[] = [
            "text" => "Film",
            "goal" => 1,
            "actual" => $object->getVideo() ? 1 : 0
        ];

        if($object->getPackages())
        {
            $required[] = [
                "text" => "Paczki",
                "goal" => 1,
                "actual" => (count($object->getPackages()) > 0) ? 1 : 0
            ];
        }

        $required[] = [
            "text" => "Paczki - ilość",
            "goal" => 1,
            "actual" => ($object->getPackageCount() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Ilość seryjna",
            "goal" => 1,
            "actual" => ($object->getSerieSize() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 1",
            "goal" => 1,
            "actual" => ($object->getDesc1("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 2",
            "goal" => 1,
            "actual" => ($object->getDesc2("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 3",
            "goal" => 1,
            "actual" => ($object->getDesc3("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 4",
            "goal" => 1,
            "actual" => ($object->getDesc4("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Instrukcja",
            "goal" => 1,
            "actual" => ($object->getInstruction()) ? 1 : 0
        ];

        $required[] = [
            "text" => "Instrukcja US",
            "goal" => 1,
            "actual" => ($object->getInstructionUS()) ? 1 : 0
        ];

        $required[] = [
            "text" => "Dokumenty",
            "goal" => 1,
            "actual" => ($object->getDocuments() && count($object->getDocuments())) ? 1 : 0
        ];

        return [
            "required" => $required,
            "optional" => $optional,
        ];
    }

    public function getProductSetQuality(ProductSet $object): array
    {
        $required = [];

        $required[] = [
            "text" => "Pełna nazwa PL",
            "goal" => 1,
            "actual" => $object->getName("pl") ? 1 : 0
        ];

        $required[] = [
            "text" => "Pełna nazwa EN",
            "goal" => 1,
            "actual" => $object->getName("en") ? 1 : 0
        ];

        $languages = \Pimcore\Tool::getValidLanguages();

        $nameRestLanguages = 1;

        foreach ($languages as $lang) {
            if(!$object->getName($lang)) {
                $nameRestLanguages = 0;
                break;
            }
        }

        $optional[] = [
            "text" => "Pełna nazwa (pozostałe języki)",
            "goal" => 1,
            "actual" => $nameRestLanguages
        ];

        $required[] = [
            "text" => "Wymiary - Szerokość",
            "goal" => 1,
            "actual" => ($object->getWidth() and $object->getWidth()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Wymiary - Wysokość",
            "goal" => 1,
            "actual" => ($object->getHeight() and $object->getHeight()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Wymiary - Głębokość",
            "goal" => 1,
            "actual" => ($object->getDepth() and $object->getDepth()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Zdjęcie główne",
            "goal" => 1,
            "actual" => $object->getImage() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kod EAN",
            "goal" => 1,
            "actual" => $object->getEan() ? 1 : 0
        ];

        $required[] = [
            "text" => "Zdjęcia dla modelu produktu",
            "goal" => 1,
            "actual" => $object->getImagesModel() ? Min(count($object->getImagesModel()->getItems()), 1)  : 0
        ];

        $required[] = [
            "text" => "Kod CN",
            "goal" => 1,
            "actual" => $object->getCN() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kod GPC",
            "goal" => 1,
            "actual" => $object->getGPC() ? 1 : 0
        ];

        $required[] = [
            "text" => "Kod PKWiU",
            "goal" => 1,
            "actual" => $object->getPKWIU() ? 1 : 0
        ];

        $required[] = [
            "text" => "Paczki - ilość",
            "goal" => 1,
            "actual" => ($object->getPackageCount() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Ilość seryjna",
            "goal" => 1,
            "actual" => ($object->getSerieSize() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Zdjęcia",
            "goal" => 1,
            "actual" => $object->getImages() ? Min(count($object->getImages()->getItems()), 1)  : 0
        ];

        $required[] = [
            "text" => "Film",
            "goal" => 1,
            "actual" => $object->getVideo() ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 1",
            "goal" => 1,
            "actual" => ($object->getDesc1("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 2",
            "goal" => 1,
            "actual" => ($object->getDesc2("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 3",
            "goal" => 1,
            "actual" => ($object->getDesc3("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Opis 4",
            "goal" => 1,
            "actual" => ($object->getDesc4("pl") > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Kolekcja",
            "goal" => 1,
            "actual" => $object->getSerie() ? 1 : 0
        ];

        $required[] = [
            "text" => "Parametry",
            "goal" => 1,
            "actual" => ($object->getParameters() and $object->getParameters()->getGroups()) ? 1 : 0
        ];

        $allParametersCount = 0;
        $filledParametersCount = 0;

        foreach ($object->getParameters()->getGroups() as $group) {
            foreach ($group->getKeys() as $gkey) {
                $allParametersCount += 1;
                $filledParametersCount += $gkey->getValue() ? 1 : 0;
            }
        }

        $required[] = [
            "text" => "Parametry - wartości",
            "goal" => $allParametersCount,
            "actual" => $filledParametersCount
        ];

        $required[] = [
            "text" => "Parametry Allegro",
            "goal" => 1,
            "actual" => $object->getParametersAllegro() && count($object->getParametersAllegro()->getGroups()) ? 1 : 0
        ];

        $allParametersAllegroCount = 0;
        $filledParametersAllegroCount = 0;

        foreach ($object->getParametersAllegro()->getGroups() as $group) {
            foreach ($group->getKeys() as $gkey) {
                $allParametersAllegroCount += 1;
                $filledParametersAllegroCount += $gkey->getValue() ? 1 : 0;
            }
        }

        $required[] = [
            "text" => "Parametry allegro - wartości",
            "goal" => $allParametersAllegroCount,
            "actual" => $filledParametersAllegroCount
        ];

        $optional[] = [
            "text" => "Kategoria Google",
            "goal" => 1,
            "actual" => $object->getGoogleCategory() ? 1 : 0
        ];

        $required[] = [
            "text" => "Cena bazowa",
            "goal" => 1,
            "actual" => ($object->getBasePrice() and $object->getBasePrice()->getValue() > 0) ? 1 : 0
        ];

        $required[] = [
            "text" => "Ceny",
            "goal" => 1,
            "actual" => ($object->getPrice() and count($object->getPrice()) > 0) ? 1 : 0
        ];

        return [
            "required" => $required,
            "optional" => $optional,
        ];
    }
}


