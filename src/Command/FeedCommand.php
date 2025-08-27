<?php

namespace App\Command;

use App\Feed;
use App\Feed\XmlFeedMeb24;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Offer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "feed:generate", description: "Generate product feed for given offer and template")]
class FeedCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $offerId = (int)$input->getArgument("offer_id");
        $offer = Offer::getById($offerId);

        if(!$offer)
        {
            $this->writeError("Offer not found");
            return Command::INVALID;
        }

        $referenceOfferId = (int)$input->getOption("reference_offer_id");
        $referenceOffer = Offer::getById($referenceOfferId);

        $cname = $input->getArgument("template");
        if(!class_exists($cname))
        {
            $this->writeError("Template class [" . $cname . "] not found");
            return Command::INVALID;
        }

        $schema = explode("\\", $cname);
        $schema = end($schema);

        $fw = new $cname($offer, $referenceOffer);

        $outputFilename = $input->getOption('output_file_name') ?? "feed-" . $schema . "-" . $offer->getId();

        $progressBar = new ProgressBar($output, $fw->total);
        $progressBar->start();

        $fw->setStatus(function ($current, $total) use ($progressBar) {
            if($current % 10 == 0)
                $progressBar->advance();
        });

        $tmp = tempnam(sys_get_temp_dir(), 'feed_') . "." . $fw->extension();
        $s = fopen($tmp, 'w+b');
        $fw->write($s);

        $progressBar->finish();
        echo PHP_EOL;

        $this->saveToAsset($tmp, $outputFilename, $fw->contentType(), $fw->extension());

        unlink($tmp);

        return Command::SUCCESS;
    }

    protected function configure():void
    {
        $this->addArgument("offer_id", InputArgument::REQUIRED, "Offer ID");
        $this->addArgument("template", InputArgument::OPTIONAL, "Template (e.g. App\Feed\XmlFeedMeb24)", "App\Feed\XmlFeedMeb24");
        $this->addOption("output_file_name", "o", InputOption::VALUE_OPTIONAL, "Output file name");
        $this->addOption("reference_offer_id", "r", InputOption::VALUE_OPTIONAL, "Reference offer ID");
    }

    private function saveToAsset(string $sourceFile, string $fname, string $mimeType, string $extension, string $folder = "/STOCKS"):void
    {
        $dir = Asset::getByPath($folder);
        if(!$dir)
        {
            $dir = new Asset();
            $dir->setKey(ltrim($folder, "/"));
            $dir->setParentId(1);
            $dir->setType('folder');
            $dir->save();
        }

        $fullPath = $folder . "/" . $fname . '.' . $extension;
        $asset = Asset::getByPath($fullPath);

        if(!$asset)
        {
            $asset = new Asset();
            $asset->setKey($fname . "." . $extension);
            $asset->setType('document');
            $asset->setMimeType($mimeType);
            $asset->setParentId($dir->getId());
        }

        $asset->setData(file_get_contents($sourceFile));
        $asset->save();

        $this->writeInfo("Saved in $fullPath");
    }
}
