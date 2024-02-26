<?php

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Process\Process;

class PluginInstallationListener implements EventSubscriberInterface
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $pluginInstalled = $this->checkIfPluginIsInstalled();

        if ($pluginInstalled) {
            $this->addRoutesToConfiguration();
        }
    }

    private function checkIfPluginIsInstalled(): bool
    {
      
        return true;
    }

   private function addRoutesToConfiguration()
	{
	    // Execute migrations:diff
	    $diffProcess = new Process(['php', 'bin/console', 'doctrine:migrations:diff']);
	    $diffProcess->run();

	    if (!$diffProcess->isSuccessful()) {
	        throw new \RuntimeException('Error occurred while generating migration files.');
	    }

	    // Execute migrations:migrate
	    $migrateProcess = new Process(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
	    $migrateProcess->run();

	    if (!$migrateProcess->isSuccessful()) {
	        throw new \RuntimeException('Error occurred while running migrations.');
	    }

	}

}
