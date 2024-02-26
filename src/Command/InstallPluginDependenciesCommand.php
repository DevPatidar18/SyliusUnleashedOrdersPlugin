<?php

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;


class InstallPluginDependenciesCommand extends Command
{
	protected static $defaultName = 'ForgeLabsUk:install-dependencies';
    protected function configure()
    {
        $this->setDescription('Install all Dependencies for ForgeLabsUk Sylius Unleashed Orders Plugin.');
    }
	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process(['composer', 'show', '-i', 'forgelabsuk/sylius-unleashed-products-plugin']);
	    $process->run();

	    if (!$process->isSuccessful()) {
	        // Install sylius-unleashed-products-plugin
	        $installProcess = new Process(['composer', 'require', 'forgelabsuk/sylius-unleashed-products-plugin']);
	        $installProcess->run();

	        if (!$installProcess->isSuccessful()) {
	            $output->writeln('<error>Error occurred while installing sylius-unleashed-products-plugin.</error>');
	            return Command::FAILURE;
	        }
	    } else {
	        $output->writeln('<info>sylius-unleashed-products-plugin is already installed.</info>');
	    }

	    $output->writeln('<info>Dependencies installed successfully.</info>');
	    return Command::SUCCESS;
    }
}