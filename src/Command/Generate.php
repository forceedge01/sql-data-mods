<?php

namespace Genesis\SQLExtensionWrapper\Command;

use Behat\Testwork\Cli\Controller;
use Genesis\SQLExtensionWrapper\Service\DataModGeneratorService;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Generate implements Controller
{
    /**
     * Configures command to be executable by the controller.
     *
     * @param SymfonyCommand $command
     */
    public function configure(SymfonyCommand $command)
    {
        $command->addOption(
            '--dm-generate',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Generate a datamod. You can re use this flag to generate multiple at once.'
        );
        $command->addOption(
            '--dm-path',
            null,
            InputOption::VALUE_REQUIRED,
            'The path to use for data mods.',
            './features/bootstrap/DataMod/'
        );
        $command->addOption(
            '--dm-namespace',
            null,
            InputOption::VALUE_OPTIONAL,
            'The namespace to use. Must end with leading double backslash.',
            '\\DataMod'
        );
        $command->addOption(
            '--dm-connection',
            null,
            InputOption::VALUE_OPTIONAL,
            'The connection to use to auto generate field mapping for a data mod. Use 0 for first connection.'
        );
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
        if ($option = $input->getOption('dm-generate')) {
            self::generateTables(
                $option,
                $input->getOption('dm-path'),
                $input->getOption('dm-namespace'),
                $input->getOption('dm-connection')
            );
        }
    }

    private static function generateTables(array $generate, $path, $namespace, $connection = null)
    {
        DataModGeneratorService::confirmGenerate($generate, $path, $namespace, $connection);
        DataModGeneratorService::generate($generate, $path, $namespace, $connection);
        exit;
    }
}
