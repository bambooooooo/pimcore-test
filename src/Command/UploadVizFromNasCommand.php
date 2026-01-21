<?php

namespace App\Command;

use App\Message\VizAssignMessage;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Pimcore\Model\Element\DuplicateFullPathException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Messenger\MessageBusInterface;
use Pimcore\Model\Tool\SettingsStore;

#[AsCommand('assets:viz:upload', 'Upload assets from specific NAS location')]
class UploadVizFromNasCommand extends AbstractCommand
{
    public function __construct(private readonly MessageBusInterface $bus, private readonly string $viz_root_path)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newSync = date('Y-m-d H:i:s');

        $storeKey = 'renders_last_sync';
        $rootDir = 'renders';
        $scope = 'app';

        $parent = Asset::getByPath($this->viz_root_path);
        if(!$parent) {
            $parent = new Asset\Folder();
            $parent->setPath($this->viz_root_path);
            $parent->save();
        }

        if(!file_exists($rootDir))
        {
            $this->writeError("NAS file $rootDir does not exist or it has been incorrectly mounted");
            return 1;
        }

        $lastSync = SettingsStore::get($storeKey, $scope)?->getData() ?? date('2025-12-01 00:00:00');

        $this->writeInfo("Sync from: $lastSync");

        $finder = new Finder();
        $finder
            ->files()
            ->name('/\.(jpg|jpeg|png|webp)$/i')
            ->date('>= ' . $lastSync)
            ->in($rootDir);

        foreach ($finder as $file) {

            try
            {
                $path = $file->getRealPath();
                $filename = basename($path);

                $output->write("$path...");

                $im = Asset::getByPath($parent->getFullPath() . "/" . $filename);

                if(!$im)
                {
                    $im = new Asset\Image();
                    $im->setParent($parent);
                    $im->setKey(basename($path));
                }

                $im->setData(file_get_contents($path));
                $im->save();

                $this->bus->dispatch(new VizAssignMessage($im->getId()));

                $this->writeInfo("Ok");
            }
            catch (DuplicateFullPathException $e) {
                $this->writeError($e->getMessage());
            }
        }

        SettingsStore::set($storeKey, $newSync, SettingsStore::TYPE_STRING, $scope);

        return 0;
    }
}
