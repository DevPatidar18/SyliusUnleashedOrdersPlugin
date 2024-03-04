<?php

namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Service\UnleashedApiService;
use Symfony\Component\Console\Style\SymfonyStyle;


class SubmitOrdersToUnleashedCommand extends Command
{
    protected static $defaultName = 'forgelabsuk:unleashed:submit-orders';
    private $UnleashedApiService;
    public function __construct(UnleashedApiService $unleashedApiService)
    {
        $this->UnleashedApiService = $unleashedApiService;
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

        $response = $this->UnleashedApiService->getSyliusOrders();
        $data = json_decode($response->getContent(), true);

        $io = new SymfonyStyle($input, $output);
        if (isset($data[0]['success']) && $data[0]['success'] === 'Not orders found') {
            $io->success('No orders found.');
        } elseif (isset($data[0]['success']) && $data[0]['success'] === 'Order are successfuly uploaded in unleashed') {
            $io->success('Orders successfully uploaded to Unleashed.');
        }

        return Command::SUCCESS;
    }

}
