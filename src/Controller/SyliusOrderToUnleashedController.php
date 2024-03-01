<?php

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Service\UnleashedApiService;


class SyliusOrderToUnleashedController extends AbstractController
{
    private $UnleashedApiService;
    public function __construct(UnleashedApiService $unleashedApiService)
    {
        $this->UnleashedApiService = $unleashedApiService;
    }
    public function SyliusOrdersToUnleashedAction(): Response
    {
        $response = $this->UnleashedApiService->getSyliusOrders();
        return $response;
    }


}
