<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pimcore\Model\DataObject\Classificationstore;
use Pimcore\Model\DataObject\Classificationstore\GroupConfig;
use Pimcore\Model\DataObject\Classificationstore\KeyGroupRelation;
use Pimcore\Model\DataObject\Classificationstore\StoreConfig;
use App\Service\AllegroService;

#[AsCommand(
    name: 'allegro:update:category',
    description: 'Update classification store Allegro with given category',
)]
class AllegroUpdateCategoryCommand extends AbstractCommand
{
    public function __construct(private readonly AllegroService $allegro)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('category_id', InputArgument::REQUIRED, "Allegro category Id");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $categoryId = $input->getArgument('category_id');

        $this->writeInfo('Updating Category #' . $categoryId . '...');

        $categoryPath = $this->getAllegroCategoryPath($categoryId);
        $nameParts = preg_split("/\//", $categoryPath);
        $categoryName = end($nameParts);

        $this->writeInfo('Category.Name: ' . $categoryName . '.');
        $this->writeInfo('Category.Path: ' . $categoryPath . '.');

        $this->allegroSyncClassificationStore($categoryId, $categoryPath, $categoryName);

        return Command::SUCCESS;
    }

    private function allegroSyncClassificationStore($categoryId, $categoryPath, $categoryName): void
    {
        $cStore = StoreConfig::getByName("Allegro");
        if (!$cStore) {
            $cStore = new StoreConfig();
            $cStore->setName("Allegro");
            $cStore->setDescription("Allegro.pl parameters");
            $cStore->save();
        }

        $cGroup = GroupConfig::getByName($categoryPath, $cStore->getId());

        if (!$cGroup)
        {
            $cGroup = new GroupConfig();
            $cGroup->setStoreId($cStore->getId());
            $cGroup->setName($categoryPath);
            $cGroup->setDescription($categoryName);
            $cGroup->save();
        }

        $res = $this->allegro->request('GET', '/sale/categories/' . $categoryId . "/parameters")->toArray();
        $this->writeInfo('Found ' . count($res['parameters']) . ' parameters...');
        foreach ($res["parameters"] as $parameter)
        {
            $kname = $parameter['name'] . " - " . $parameter['id'];
            $k = Classificationstore\KeyConfig::getByName($kname, $cStore->getId());
            if (!$k)
            {
                $k = new Classificationstore\KeyConfig();
            }

            $kChanged = false;

            $k->setStoreId($cStore->getId());
            $k->setName($kname);
            $k->setTitle($parameter['name']);
            $k->setEnabled(true);

            switch ($parameter['type']) {
                case 'integer':
                {
                    $def = new \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric();
                    $def->setInteger(true);

                    if ($parameter['restrictions']) {
                        if ($parameter['restrictions']['min'])
                            $def->setMinValue($parameter['restrictions']['min']);

                        if ($parameter['restrictions']['max'])
                            $def->setMaxValue($parameter['restrictions']['max']);
                    }

                    break;
                }
                case 'float':
                {
                    $def = new \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric();
                    $def->setInteger(false);

                    break;
                }
                case 'string':
                {
                    $def = new \Pimcore\Model\DataObject\ClassDefinition\Data\Input();

                    if ($parameter['restrictions']) {
                        if ($parameter['restrictions']['maxLength']) {
                            $def->setColumnLength($parameter['restrictions']['maxLength']);
                        }
                    }

                    break;
                }
                case 'dictionary':
                {
                    $def = new \Pimcore\Model\DataObject\ClassDefinition\Data\Select();

                    $options = [];
                    foreach ($parameter['dictionary'] as $option) {
                        $options[] = [
                            'key' => $option['value'],
                            'value' => $option['id']
                        ];
                    }

                    $def->setOptions($options);

                    break;
                }
            }

            $def->setName($kname);

            $def->setTitle($parameter['name']);
            $def->setMandatory($parameter['required']);

            if($parameter['unit'])
            {
                if($k->getDescription() ?? "" != $parameter['unit'])
                {
                    $k->setDescription($parameter['unit']);
                    $kChanged = true;
                }
            }
            else
            {
                if($k->getDescription() && $k->getDescription() != "")
                {
                    $k->setDescription("");
                    $kChanged = true;
                }
            }

            $defJson = json_encode($def);

            if($k->getDefinition() != $defJson || $kChanged)
            {
                $k->setDefinition($defJson);
                $k->setType($def->getFieldType());
                $k->save();
            }

            $r = KeyGroupRelation::getByGroupAndKeyId($cGroup->getId(), $k->getId());

            if ($r) {
                continue;
            }

            $keyRel = new KeyGroupRelation();
            $keyRel->setGroupId($cGroup->getId());
            $keyRel->setKeyId($k->getId());
            $keyRel->save();
        }

        $this->writeInfo('Done.');
    }

    private function getAllegroCategoryPath(string $id)
    {
        $parts = $this->getAllegroCategoryPathChunks($id);
        $parts = array_reverse($parts);

        return implode('/', $parts);
    }

    private function getAllegroCategoryPathChunks(string $id, array $parts = []): array
    {
        $res = $this->allegro->request('GET', '/sale/categories/' . $id)->toArray();
        $parts[] = $res['name'];

        if(count($parts) == 1 && !$res['leaf'])
        {
            throw new \InvalidArgumentException("Given category is not a leaf category. Product can not belong to category that is not a category tree leaf.");
        }

        if($res['parent'] && $res['parent']['id'])
        {
            return $this->getAllegroCategoryPathChunks($res['parent']['id'], $parts);
        }

        return $parts;
    }
}
