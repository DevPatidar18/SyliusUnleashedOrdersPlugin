<?php
namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Bundle\OrderBundle\Controller\OrderController as BaseOrderController;
use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Enum\OrderStateEnum;
use Webmozart\Assert\Assert;
use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Service\UnleashedApiService;

class OrderController extends BaseOrderController
{

    private $unleashedApiService;

    public function setUnleashedApiService(UnleashedApiService $unleashedApiService)
    {
        $this->unleashedApiService = $unleashedApiService;
    }

    public function saveAction(Request $request): Response
    {
        $order = $this->getCurrentCart();
        $order->setGuid($this->generateGuid());
        $order->setUnleashedStatus(OrderStateEnum::PENDING);
        return parent::saveAction($request);
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
    function generateGuid(): string
    {
        $part1 = bin2hex(random_bytes(4));
        $part2 = bin2hex(random_bytes(2));
        $part3 = bin2hex(random_bytes(2));
        $part4 = bin2hex(random_bytes(2));
        $part5 = bin2hex(random_bytes(6));
        $guid = sprintf('%s-%s-%s-%s-%s', $part1, $part2, $part3, $part4, $part5);
        return $guid;
    }

}

