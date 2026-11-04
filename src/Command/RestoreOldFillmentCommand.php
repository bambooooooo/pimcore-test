<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DeepLService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject;
use Pimcore\Model\Version;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'restore:old-fillment', description: 'Restore old fillment (styrofoam) from latest version that contains this fillment material.')]
class RestoreOldFillmentCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addArgument('objectId', InputArgument::REQUIRED, 'Object ID to restore');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectId = $input->getArgument('objectId');
        $obj = DataObject::getById($objectId);

        if(!($obj instanceof DataObject\Package))
        {
            $output->writeln("<error>Unsupported object type '{$obj->getClassId()}'</error>");
            return Command::FAILURE;
        }

        $versions = $obj->getVersions();

        $versions = array_reverse($versions);

        $cnt = count($versions);

        $found = false;

        foreach($versions as $version)
        {
            $data = $version->getData();

            foreach($data->getFillment() as $fg)
            {
                $material = strtoupper($fg["Material"]->getData() ?? "Styropian");
                $elements = $fg["Elements"]->getData();

                if(!str_contains($material, "PLASTER"))
                {
                    $output->writeln("Found other fillment '{$material}' in version {$version->getId()}");
                    $found = true;
                    break;
                }
            }

            if($found) {
                break;
            }
        }

        $output->writeln("<info>Restoring fillment from version {$version->getId()}...</info>");

        $restored = $version->getData();

        Version::disable();

        try {

            $fillment = $restored->getFillment();

            foreach($fillment as $fill)
            {
                $fill["Material"]->setData("Styropian");
            }

            $obj->setFillmentOld($fillment);

            $obj->save();

            Version::enable();
            $output->writeln("<info>Version {$version->getId()} restored.</info>");
            $obj->save(['versionNote' => 'RESTORE: styropian']);
        }
        catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }

        return Command::SUCCESS;
    }
}
