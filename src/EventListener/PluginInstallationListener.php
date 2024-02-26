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
	    $routeFilePath = $this->projectDir . '/config/routes/unleashed_products_plugin.yaml';
	    $routeFileContent = <<<YAML
	# Routes for unleashed products plugin
	forge_labs_uk_unleashed_product_shop:
	  resource: "@ForgeLabsUkSyliusUnleashedProductsPlugin/Resources/config/shop_routing.yml"
	  prefix: /{_locale}
	  requirements:
	      locale: ^[A-Za-z]{2,4}(([A-Za-z]{4}|[0-9]{3}))?(_([A-Za-z]{2}|[0-9]{3}))?$

	forge_labs_uk_sylius_unleashed_product_admin:
	  resource: "@ForgeLabsUkSyliusUnleashedProductsPlugin/Resources/config/admin_routing.yml"
	  prefix: '/%sylius_admin.path_name%'
	YAML;

	    file_put_contents($routeFilePath, $routeFileContent);

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
