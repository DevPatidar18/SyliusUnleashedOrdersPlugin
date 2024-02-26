<?php
namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RunMigrationsCommand extends Command
{
    protected static $defaultName = 'ForgeLabsUk:sylius-unleashed-orders-plugin:migrate';

    protected function configure()
    {
        $this->setDescription('Run migrations for ForgeLabsUk Sylius Unleashed Orders Plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $diffProcess = new Process(['php', 'bin/console', 'doctrine:migrations:diff']);
        $diffProcess->run();

        if (!$diffProcess->isSuccessful()) {
            throw new ProcessFailedException($diffProcess);
        }

        $migrateProcess = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
        $migrateProcess->run();

        if (!$migrateProcess->isSuccessful()) {
            throw new ProcessFailedException($migrateProcess);
        }

        $output->writeln('<info>Migrations executed successfully for ForgeLabsUk Sylius Unleashed Orders Plugin.</info>');
        
        return Command::SUCCESS;
    }
}
