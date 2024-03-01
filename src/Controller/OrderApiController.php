<?php

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderApiController extends AbstractController
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    public function getOrders(): Response
    {
        $orders = $this->orderRepository->findAll();

        $ordersData = [];
        foreach ($orders as $order) {

            $orderData = [
                'id' => $order->getId(),
                'number' => $order->getNumber(),
                'checkoutCompletedAt' => $order->getCheckoutCompletedAt()->format('Y-m-d H:i:s'),
                'notes' => $order->getNotes(),
                'itemsTotal' => $order->getItemsTotal(),
                'adjustmentsTotal' => $order->getAdjustmentsTotal(),
                'total' => $order->getTotal(),
                'state' => $order->getState(),
                'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
                'customer' => [
                    'id' => $order->getCustomer()->getId(),
                    'firstName' => $order->getCustomer()->getFirstName(),
                    'lastName' => $order->getCustomer()->getLastName(),
                    'phoneNumber' => $order->getCustomer()->getPhoneNumber(),
                    'createdAt' => $order->getCustomer()->getUpdatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $order->getCustomer()->getUpdatedAt()->format('Y-m-d H:i:s'),
                    'gender' => $order->getCustomer()->getGender(),
                    'birthday' => $order->getCustomer()->getBirthday() ? $order->getCustomer()->getBirthday()->format('Y-m-d') : null,
                    'customerGroup' => $order->getCustomer()->getGroup() ? $order->getCustomer()->getGroup(): null,
                    'defaultAddress' => $order->getCustomer()->getDefaultAddress() ? $order->getCustomer()->getDefaultAddress(): null,
                    'totalOrders' => $order->getCustomer()->getOrders()->count(),

                ],
                'currencyCode' => $order->getCurrencyCode(),
                'localeCode' => $order->getLocaleCode(),
                'checkoutState' => $order->getCheckoutState(),
                'paymentState' => $order->getPaymentState(),
                'shippingState' => $order->getShippingState(),
                'tokenValue' => $order->getTokenValue(),
                'customerIp' => $order->getCustomerIp(),
                'createdByGuest' => $order->isCreatedByGuest(),
                'guid' => $order->getGuid(),
                'unleashedStatus' => $order->getUnleashedStatus(),
                // Add more properties as needed
            ];

            // Optionally, you can push each order's data into the main array
            $ordersData[] = $orderData;
        }

        return new JsonResponse($ordersData);
    }


}
