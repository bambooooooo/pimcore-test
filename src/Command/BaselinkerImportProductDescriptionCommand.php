<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BaselinkerService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Baselinker;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'baselinker:product:description:import', description: 'Importing product descriptions (1,2,3,4) from baselinker')]
class BaselinkerImportProductDescriptionCommand extends AbstractCommand
{
    public function __construct(private BaselinkerService $baselinkerService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument("id", InputArgument::REQUIRED, "Product or ProductSet Id");
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $id = (int)$input->getArgument('id');
        $this->importDescriptions($id);

        return Command::SUCCESS;
    }

    private function importDescriptions(int $objId): void
    {
        $obj = DataObject::getById($objId);

        if(!($obj instanceof Product || $obj instanceof ProductSet))
        {
            $this->writeError('unsupported object');
            return;
        }

        if(!($obj->getBaselinkerCatalog() && $obj->getBaselinkerCatalog()[0]->getProductId()))
        {
            $this->writeError('no blk id');
            return;
        }

        $blkId = $obj->getBaselinkerCatalog()[0]->getProductId();

        /** @var DataObject\BaselinkerCatalog $catalog */
        $catalog = $obj->getBaselinkerCatalog()[0]->getElement();

        /** @var Baselinker $baselinker */
        $baselinker = $catalog->getBaselinker();

        $response = $this->baselinkerService->get($baselinker, 'getInventoryProductsData', [
            'inventory_id' => $catalog->getCatalogId(),
            'products' => [$blkId]
        ]);

        if(!array_key_exists($blkId, $response['products']))
        {
            $this->writeError('not found in blk');
            return;
        }

        $desc1 = $this->normalize($response['products'][$blkId]['text_fields']['description_extra1'] ?? "");
        $desc2 = $this->normalize($response['products'][$blkId]['text_fields']['description_extra2'] ?? "");
        $desc3 = $this->normalize($response['products'][$blkId]['text_fields']['description_extra3'] ?? "");
        $desc4 = $this->normalize($response['products'][$blkId]['text_fields']['description_extra4'] ?? "");

        $changed = false;
        if($obj->getDesc1("pl") != $desc1)
        {
            $obj->setDesc1($desc1, "pl");
            $changed = true;
        }

        if($obj->getDesc2("pl") != $desc2)
        {
            $obj->setDesc2($desc2, "pl");
            $changed = true;
        }

        if($obj->getDesc3("pl") != $desc3)
        {
            $obj->setDesc3($desc3, "pl");
            $changed = true;
        }

        if($obj->getDesc4("pl") != $desc4)
        {
            $obj->setDesc4($desc4, "pl");
            $changed = true;
        }

        if($changed)
        {
            $obj->save();
            $this->writeInfo("Description changed");
        }
        else
        {
            $this->writeComment("No change.");
        }
    }

    private function normalize($html) {

        $TAG_REPLACEMENT = ['strong' => 'b'];
        $TAG_ALLOWED = ['h1', 'h2', 'p', 'ul', 'ol', 'li', 'b'];
        $REMOVE_HTML_ATTRS = 'all';
        $REMOVE_SELF_CLOSING_TAGS = true;

        if($REMOVE_SELF_CLOSING_TAGS)
        {
            $html = preg_replace('/<\w+[^>]*\/>/', '', $html);
        }

        if($REMOVE_HTML_ATTRS)
        {
            $html = preg_replace('/<(\w+)(\s+[^>]*)?>/', '<$1>', $html);
        }

        foreach($TAG_REPLACEMENT as $tag => $replacement)
        {
            $html = str_replace("<" . $tag . ">", "<" . $replacement . ">", $html);
            $html = str_replace("</" . $tag . ">", "</" . $replacement . ">", $html);
        }

        $allowed = implode('|', $TAG_ALLOWED);
        $html = preg_replace('/<(?!\/?(' . $allowed . ')\b)[^>]+>/', '', $html);

        $html = preg_replace('/[ \t]+/', ' ', $html);

        $html = preg_replace('/^\s*[\r\n]/m', '', $html);

        $html = trim($html);

        return $html;
    }
}
