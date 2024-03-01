<?php

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class SubmitOrdersToUnleashedCommand extends Command
{
    protected static $defaultName = 'forgelabsuk:unleashed:submit-orders';
    private $orderRepository;

    public function __construct(EntityRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setDescription('Submit orders to Unleashed')
            ->setHelp('This command submits orders from Sylius to Unleashed.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $orders = $this->orderRepository->findAll();
        foreach ($orders as $order) {
            dd($orders);
            // $output->writeln('Processing order number: ' . $order->getNumber());
        }

        return Command::SUCCESS;
    }

}
