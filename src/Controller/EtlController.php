<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etl', name: 'etl')]
class EtlController extends FrontendController
{
    #[Route('/packageserie', name: '_packagesserie')]
    public function packageCarrierAction(): Response
    {
        $response = new StreamedResponse(function ()
        {
            DataObject::setHideUnpublished(false);

            $packages = new DataObject\Package\Listing();
            $packages->setCondition("`ObjectType` = 'SKU'");
            $packages = $packages->load();

            $fp = fopen('php://output', 'w');

            foreach ($packages as $package) {
                $cnt = 0;

                if($package->getCarriers())
                {
                    foreach ($package->getCarriers() as $carrier)
                    {
                        $cnt = $carrier->getQuantity();
                        break;
                    }
                }

                fputcsv($fp, [
                    $package->getId(),
                    $cnt
                ]);
            }

            fclose($fp);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="packages.csv"');

        return $response;
    }

    #[Route('/field/{id}/{field}', name: '_field')]
    public function getField(Request $request): Response
    {
        DataObject::setHideUnpublished(false);

        $id = (int)$request->get('id');
        $field = ucfirst($request->get('field'));

        $obj = DataObject::getById($id);
        if(!$obj)
        {
            return new Response('Not found', 404);
        }

        try
        {
            $classId = $obj->getClassId();
            $classDef = ClassDefinition::getById($classId);
            $fieldDef = $classDef->getFieldDefinition($field);
            $getter = 'get' . ucfirst($field);

            if (!method_exists($obj, $getter)) {
                throw new \Exception("Getter {$getter} does not exist");
            }

            if($fieldDef instanceof DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation or
                $fieldDef instanceof DataObject\ClassDefinition\Data\AdvancedManyToManyRelation or
                $fieldDef instanceof DataObject\ClassDefinition\Data\ManyToManyObjectRelation)
            {
                throw new \Exception("Getter {$getter} has to few parameters (3 expected)");
            }

            $data = $obj->$getter();

            return new Response($data, Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new Response("", Response::HTTP_OK);
        }
    }

    #[Route('/field/{id}/{field}/{arg}', name: '_field_arg')]
    public function getFieldArg(Request $request): Response
    {
        DataObject::setHideUnpublished(false);

        $id = (int)$request->get('id');
        $field = ucfirst($request->get('field'));
        $arg = $request->get('arg');

        $obj = DataObject::getById($id);
        if(!$obj)
        {
            return new Response('Not found', 404);
        }

        try
        {
            $classId = $obj->getClassId();
            $classDef = ClassDefinition::getById($classId);
            $fieldDef = $classDef->getFieldDefinition($field);
            $getter = 'get' . ucfirst($field);

            if (!method_exists($obj, $getter)) {
                throw new \Exception("Getter {$getter} does not exist");
            }

            if($fieldDef instanceof DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation or
                $fieldDef instanceof DataObject\ClassDefinition\Data\AdvancedManyToManyRelation or
                $fieldDef instanceof DataObject\ClassDefinition\Data\ManyToManyObjectRelation)
            {
                throw new \Exception("Getter {$getter} has to few parameters (3 expected)");
            }

            $data = $obj->$getter($arg);

            return new Response($data, Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new Response("", Response::HTTP_OK);
        }
    }

    #[Route('/field/{id}/{field}/{arg}/{arg1}', name: '_field_arg2')]
    public function getRelationData(Request $request): Response
    {
        DataObject::setHideUnpublished(false);

        $id = (int)$request->get('id');
        $field = ucfirst($request->get('field'));
        $nth = (int)$request->get('arg');
        $arg1 = $request->get('arg1');

        $obj = DataObject::getById($id);
        if(!$obj)
        {
            return new Response('Not found', 404);
        }

        try
        {
            $classId = $obj->getClassId();
            $classDef = ClassDefinition::getById($classId);
            $fieldDef = $classDef->getFieldDefinition($field);
            $getter = 'get' . ucfirst($field);

            if (!method_exists($obj, $getter)) {
                throw new \Exception("Getter {$getter} does not exist");
            }

            if(!($fieldDef instanceof DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation or
                $fieldDef instanceof DataObject\ClassDefinition\Data\AdvancedManyToManyRelation or
                $fieldDef instanceof DataObject\ClassDefinition\Data\ManyToManyObjectRelation))
            {
                throw new \Exception("Getter {$getter} has to many parameters");
            }

            $li = $obj->$getter()[$nth];

            if(!$li)
            {
                return new Response('Not found', 404);
            }

            try
            {
                $innerGetter = 'get' . ucfirst($arg1);
                $data = $li->$innerGetter();

                return new Response($data, Response::HTTP_OK);
            }
            catch (\Exception $e)
            {
                $innerGetter = 'get' . ucfirst($arg1);

                $data = $li->getElement()->$innerGetter();
                return new Response($data, Response::HTTP_OK);
            }
        }
        catch (\Exception $e)
        {
            return new Response("", Response::HTTP_OK);
        }
    }
}
