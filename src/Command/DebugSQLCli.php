<?php

namespace Genesis\SQLExtensionWrapper\Command;

use Behat\Testwork\Cli\Controller;
use Genesis\SQLExtension\Context\Debugger;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugSQLCli implements Controller
{
    /**
     * Configures command to be executable by the controller.
     *
     * @param SymfonyCommand $command
     */
    public function configure(SymfonyCommand $command)
    {
        $command->addOption('--debug-sql', null, InputOption::VALUE_NONE, 'Print out sql commands when executed.');
        $command->addOption('--debug-sql-all', null, InputOption::VALUE_NONE, 'Print out all activity when executed.');
    }

    /**
     * Executes controller.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('debug-sql')) {
            Debugger::enable(Debugger::MODE_SQL_ONLY);
        } elseif ($input->getOption('debug-sql-all')) {
            Debugger::enable(Debugger::MODE_ALL);
        }
    }
}
