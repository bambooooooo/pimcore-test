<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DeepLService;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:usage', description: 'Show APIs usage')]
class ApiUsageCommand extends AbstractCommand
{
    public function __construct(private DeepLService $deepLService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->deeplUsage();

        return Command::SUCCESS;
    }

    private function deeplUsage()
    {
        $usage = $this->deepLService->usage();
        $taken = $usage->character->count;
        $limit = $usage->character->limit;
        $percent = $taken * 100 / $limit;

        $renew = $this->deepLService->renewDate();
        $renewIn = $renew->diff(new \DateTime())->days + 1;

        $this->writeInfo("[DEEPL] Usage: $taken / $limit ($percent%). Renew in {$renewIn} days ({$renew->format('d.m.y')}).");
    }
}
