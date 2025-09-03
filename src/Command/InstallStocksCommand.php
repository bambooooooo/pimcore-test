<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand('stocks:install', "Installing stocks tables")]
class InstallStocksCommand extends Command
{
    public function __construct(private readonly Connection $db)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('action', InputArgument::OPTIONAL, "i|u", "i");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');

        if($action === 'i') {
            $this->db->executeStatement("CREATE TABLE IF NOT EXISTS `stocks` (
                o_id INT NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                PRIMARY KEY (o_id))");

            $output->write('<info>Stocks table created.</info>');
        }
        else if($action === 'u') {
            $this->db->executeStatement("DROP TABLE IF EXISTS `stocks`");
            $output->writeln('<info>Stocks table deleted.</info>');
        }
        else
        {
            $output->writeln('<error>Unknown action: '.$action.'</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
