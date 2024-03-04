<?php
namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Service;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Enum\OrderStateEnum;

class UnleashedApiService
{
    private $orderRepository;
    private $entityManager;
    private $api;
    private $apiId;
    private $apiKey;

    public function __construct(OrderRepositoryInterface $orderRepository,EntityManagerInterface $entityManager, string $api = null, ?string $apiId = null, ?string $apiKey = null)
    {
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
        $this->api = $_ENV['UNLEASHED_API_URL'];
        $this->apiId = $_ENV['UNLEASHED_API_ID'] ?? null;
        $this->apiKey = $_ENV['UNLEASHED_API_KEY'] ?? null;

    }
    public function getSignature($request, $key)
    {
        return base64_encode(hash_hmac('sha256', $request, $key, true));
    }
    public function getCurl($id, $key, $signature, $endpoint, $requestUrl, $format)
    {
        $api = $this->api;
        $curl = curl_init($api . $endpoint . $requestUrl);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/$format",
            "Accept: application/$format",
            "api-auth-id: $id",
            "api-auth-signature: $signature"
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_HTTP200ALIASES, range(400, 599));
        return $curl;
    }
    public function get($id, $key, $endpoint, $request, $format)
    {
        $requestUrl = "";
        if (!empty($request)) $requestUrl = "?$request";
        try {
            $signature = $this->getSignature($request, $key);
            $curl = $this->getCurl($id, $key, $signature, $endpoint, $requestUrl, $format);
            $curl_result = curl_exec($curl);
            curl_close($curl);
            return $curl_result;
        } catch (\Exception $e) {
            error_log('Error: ' . $e->getMessage());
        }
    }
    public function post($id, $key, $endpoint, $format, $dataId, $data)
    {
        if (!isset($dataId, $data)) {
            return null;
        }
        try {
            $signature = $this->getSignature("", $key);
            $curl = $this->getCurl($id, $key, $signature, "$endpoint/$dataId", "", $format);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $curl_result = curl_exec($curl);
            curl_close($curl);
            return $curl_result;
        } catch (\Exception $e) {
            error_log('Error: ' . $e->getMessage());
        }
    }
    public function getJson($id, $key, $endpoint, $request)
    {
        return json_decode($this->get($id, $key, $endpoint, $request, "json"));
    }
    function postJson($id, $key, $endpoint, $dataId, $data)
    {
        return $this->post($id, $key, $endpoint, "json", $dataId, json_encode($data));
    }
    public function getSyliusOrders(): Response
    {
        $responses = [];
        $orders = $this->orderRepository->createQueryBuilder('o')
            ->where('o.guid != :emptyGuid')
            ->andWhere('o.unleashedStatus = :pendingStatus')
            ->setParameter('emptyGuid', '')
            ->setParameter('pendingStatus', 'Pending')
            ->getQuery()
            ->getResult();

        if(empty($orders)){
            $responses[] = ['success' => 'Not orders found'];
        }

        $ordersData = [];
        $linenumber = 0;
        foreach ($orders as $order) {

            $orderData = [
                "OrderNumber" => 'SO-' . $order->getNumber(),
                "OrderDate" => $order->getCreatedAt()->format('Y-m-d'),
                "RequiredDate" => $order->getCreatedAt()->format('Y-m-d'),
                "OrderStatus" => 'Parked',
                "Customer" => [
                    "CustomerCode" => "EMWAR",
                    "CustomerName" => "Emmanuel's Discount Warehouse"
                ],
                "DeliveryInstruction" => $order->getNotes(),
                "Currency" => [
                    "CurrencyCode" => $order->getCurrencyCode(),
                    "Guid" => $order->getGuid()
                ],
                "ExchangeRate" => 0.989200,
                "Tax" => [
                    "TaxCode" => "V.A.T.",
                    "TaxRate" => 0.002
                ],
                'DeliveryContact' => [
                   "EmailAddress"=>$order->getCustomer()->getEmail(),
                   "FirstName"=>$order->getShippingAddress()->getFirstName() ,
                   "LastName"=>$order->getShippingAddress()->getLastName() ,
                   "MobilePhone"=>"" ,
                   "OfficePhone"=>"" ,
                   "PhoneNumber"=>$order->getShippingAddress()->getPhoneNumber() ,
                ],
                "TaxRate" => 0.002,
                "XeroTaxCode" => "V.A.T.",
                "SubTotal" => $order->getItemsTotal() / 100,
                "TaxTotal" => $order->getTaxTotal() / 100,
                "Total" => ($order->getItemsTotal() + $order->getTaxTotal()) / 100,
                "Guid" => $order->getGuid()
            ];
            foreach ($order->getItems() as $orderItem) {
                $product = $orderItem->getVariant()->getProduct();
                $unitPrice = number_format($orderItem->getUnitPrice() / 100, 2, '.', '');
                $lineTotal = $orderItem->getTotal() / 100;
                $SalesOrderLines = [
                    "LineNumber" => ++$linenumber,
                    "Product" => [
                        "Guid" => $product->getGuid(),
                        "ProductCode" => $product->getCode()
                    ],
                    "OrderQuantity" => $orderItem->getQuantity(),
                    "UnitPrice" => floatval($unitPrice),
                    "LineTotal" => floatval($lineTotal),
                    "TaxRate" => 0.002,
                    "LineTax" => $orderItem->getTaxTotal() / 100,
                    "XeroTaxCode" => "V.A.T.",
                    "Guid" => $this->generateGuid(),
                    "SerialNumbers" => [
                        [
                            "Identifier" => "9"
                        ]
                    ],
                    "BatchNumbers" => [
                        [
                            "Number" => $order->getNumber(),
                            "Quantity" => $orderItem->getQuantity(),
                        ]
                    ]
                ];
                $orderData['SalesOrderLines'][] = $SalesOrderLines;
            }
            $ordersData[] = $orderData;
            foreach ($ordersData as $orderData) {
                $subtotal = 0;
                $response = $this->postJson($this->apiId, $this->apiKey, 'SalesOrders', $orderData['Guid'], $orderData);
                $responseData = json_decode($response, true);
                $orderId = $order->getId();
                $order = $this->orderRepository->find($orderId);
                if($order){
                    $order->setUnleashedStatus(OrderStateEnum::SENT);
                    $this->entityManager->persist($order);
                    $this->entityManager->flush();
                }
                if (isset($responseData['Items'])) {
                    $responses[] = $responseData['Items'][0]['Description'];

                } else {
                    $responses[] = ['success' => 'Order are successfuly uploaded in unleashed'];
                }
            }
        }
        return new JsonResponse($responses);

    }
    function generateGuid()
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
