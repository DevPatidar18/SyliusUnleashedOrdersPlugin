<?php

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\EventListener;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\Order as OrderInterface;

class CustomOrderCreationListener
{
    public function onCreate(ResourceControllerEvent $event): void
    {
        $order = $event->getSubject();
        if (!$order instanceof OrderInterface) {
            return;
        }

        // Custom logic to generate and store GUID
        $this->generateAndStoreGuid($order);

        // Additional custom logic as needed
    }

    private function generateAndStoreGuid(OrderInterface $order): void
    {
        $guid = uniqid(); // Generate GUID
        $order->setGuid($guid); // Set GUID on the order
    }
}
