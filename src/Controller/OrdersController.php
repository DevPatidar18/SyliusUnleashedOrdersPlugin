<?php
namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Bundle\OrderBundle\Controller\OrderController as BaseOrderController;
use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Enum\OrderStateEnum;
use Webmozart\Assert\Assert;

class OrderController extends BaseOrderController
{
    public function saveAction(Request $request): Response
    {
        $order = $this->getCurrentCart();
        $items = $order->getItems();
        $orderedProducts = [];
        foreach ($items as $item) {
            $product = $item->getProduct();
            $guid = $product->getGuid();
            $quantity = $item->getQuantity();
            $orderedProducts[] = [
                'product' => $product,
                'quantity' => $quantity,
                'guid' => $guid,
            ];
        }
        $this->setGuidsToOrder($order, $orderedProducts);
        $order->setUnleashedStatus(OrderStateEnum::PENDING);
        return parent::saveAction($request);
    }
    private function setGuidsToOrder($order, $orderedProducts)
    {
        foreach ($orderedProducts as $orderedProduct) {
            $guid = $orderedProduct['guid'];
            $quantity = $orderedProduct['quantity'];
            $order->setGuid($guid);
        }
    }
    public function thankYouAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $orderId = $request->getSession()->get('sylius_order_id', null);
        if (null === $orderId) {
            $options = $configuration->getParameters()->get('after_failure');
            return $this->redirectHandler->redirectToRoute(
                $configuration,
                $options['route'] ?? 'sylius_shop_homepage',
                $options['parameters'] ?? [],
            );
        }
        $request->getSession()->remove('sylius_order_id');
        $order = $this->repository->find($orderId);
        Assert::notNull($order);
        return $this->render(
            $configuration->getParameters()->get('template'),
            [
                'order' => $order,
            ],
        );
    }
}

