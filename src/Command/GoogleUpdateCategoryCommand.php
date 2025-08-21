<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pimcore\Model\DataObject\SelectOptions;

#[AsCommand(
    name: 'google:category:update',
    description: 'Updates Google product categories from given main groups'
)]
class GoogleUpdateCategoryCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('categories',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'List of Google product categories',
            ['Sprzęt > Hydraulika > Armatura wodociągowa i części >', 'Meble >']);

        $this->addUsage('google:category:update "Sprzęt > Hydraulika > Armatura wodociągowa i części >" "Meble >"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selectedGroups = $input->getArgument('categories');

        $so = SelectOptions\Config::getById('GoogleCategory');
        if(!$so)
        {
            $so = new SelectOptions\Config();
            $so->setId('GoogleCategory');
            $so->save();
        }

        $startCount = count($so->getSelectOptions());

        $raw = explode("\n", file_get_contents('https://www.google.com/basepages/producttype/taxonomy-with-ids.pl-PL.txt'));

        if(empty($raw))
        {
            $this->writeError("No category found");
            return Command::FAILURE;
        }

        $data = [];
        $first = true;
        foreach($raw as $line)
        {
            if($first || !strpos($line, "-"))
            {
                $first = false;
                continue;
            }

            $chunks = explode("-", $line);
            $id = trim($chunks[0]);
            $category = trim($chunks[1]);

            $found = false;
            foreach($selectedGroups as $sg)
            {
                if(str_starts_with($category, $sg))
                {
                    $found = true;
                    break;
                }
            }

            if(!$found)
                continue;

            $data[] = [$id, $category];
        }

        $leafs = [];

        $leafs[] = [
            'value' => end($data)[0],
            'label' => end($data)[1],
            'name' => "_" . end($data)[0]
        ];

        for($i = count($data) - 2; $i >= 1; $i--)
        {
            if(str_starts_with($data[$i][1], $data[$i-1][1]))
            {
                $leafs[] = [
                    'value' => $data[$i][0],
                    'label' => $data[$i][1],
                    'name' => "_" . $data[$i][0]
                ];
            }
        }

        $categories = array_reverse($leafs);

        $so->setSelectOptionsFromData($categories);
        $so->save();

        $endCount = count($so->getSelectOptions());
        $added = $endCount - $startCount;

        $this->writeInfo("Options: {$startCount} => {$endCount}. {$added} added.");

        return Command::SUCCESS;
    }
}
