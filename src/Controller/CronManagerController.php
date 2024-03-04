<?php
declare(strict_types=1);

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Controller;
use ForgeLabsUk\SyliusUnleashedProductsPlugin\Controller\CommandController as BaseCommandController;
use ForgeLabsUk\SyliusUnleashedProductsPlugin\Entity\Command;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

class CronManagerController extends BaseCommandController
{
    private $entityManager;
    private $orderRepository;

    public function __construct(EntityManagerInterface $entityManager,OrderRepositoryInterface $orderRepository,)
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;

    }
    public function CommandGridAction(Request $request)
    {
        $commandRepository = $this->entityManager->getRepository(Command::class);
        $commands = $commandRepository->findAll();

        $orders = $this->orderRepository->createQueryBuilder('o')
            ->where('o.guid != :emptyGuid')
            ->andWhere('o.unleashedStatus = :pendingStatus')
            ->setParameter('emptyGuid', '')
            ->setParameter('pendingStatus', 'Pending')
            ->getQuery()
            ->getResult();
        if(empty($orders)){
            $OrdersStatus = 0;
        }else{
            $count = count($orders);
            $OrdersStatus = "{$count} order(s) available for upload to Unleashed sales orders.";

        }
        return $this->render('@ForgeLabsUkSyliusUnleashedOrdersPlugin/grid.html.twig', [
            'commands' => $commands,
            'ordersstatus' => $OrdersStatus,
        ]);
    }
}
