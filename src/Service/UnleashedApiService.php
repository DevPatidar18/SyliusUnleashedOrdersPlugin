<?php
namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Service;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class UnleashedApiService
{
    private $orderRepository;
    private $api;
    private $apiId;
    private $apiKey;
    public function __construct(OrderRepositoryInterface $orderRepository,string $api = null, ?string $apiId = null, ?string $apiKey = null)
    {
        $this->orderRepository = $orderRepository;
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
    function postJson($id, $key, $endpoint, $dataId, $data) {
        return $this->post($id, $key, $endpoint, "json", $dataId, json_encode($data));
    }

    public function getSyliusOrders(): Response
    {
        // Skip GUIDs for orders already processed
        $skipGuids = ['5d006b63-268a-423a-9645-4390a81ae97a', '995c7e00-2345-4925-8b7c-0d62ad30c859','0838f999-c326-4ef8-910b-8d05033d6ea4','139b7d9b-96bd-4a83-95f7-97da26de19cc','ea8de568-b2c9-4734-a578-8747e63bb402'];

        $builder = $this->orderRepository->createQueryBuilder('o')
            ->where('o.guid != :emptyGuid')
            ->setParameter('emptyGuid', '');

        foreach ($skipGuids as $index => $skipGuid) {
            $builder->andWhere("o.guid != :skipGuid{$index}")
                ->setParameter("skipGuid{$index}", $skipGuid);
        }

        $orders = $builder->getQuery()->getResult();

        $ordersData = [];

        $unleashedOrders = $this->getJson($this->apiId, $this->apiKey, "SalesOrders", "");

        if (isset($unleashedOrders->Items)) {
            $existingOrderGuids = array_column($unleashedOrders->Items, 'Guid');
        } else {
            $existingOrderGuids = [];
        }
        $linenumber = 0;

        foreach ($orders as $order) {

            // Check if the order GUID is not in the existing order GUIDs
            if (in_array($order->getGuid(), $existingOrderGuids)) {
                // Iterate over each item in the order

                foreach ($order->getItems() as $orderItem) {
                    $product = $orderItem->getVariant()->getProduct();
                    $unitPrice = number_format($orderItem->getUnitPrice() / 100, 2, '.', '');
                    $lineTotal = $orderItem->getTotal() / 100;


                    $orderData = [
                        "SalesOrderLines" => [
                            [
                                "LineNumber" => ++$linenumber,
                                "Product" => [
                                    "Guid" => $order->getGuid(),
                                    "ProductCode" => $product->getCode()
                                ],
                                "OrderQuantity" => $orderItem->getQuantity(),
                                "UnitPrice" => floatval($unitPrice),
                                "LineTotal" => floatval($lineTotal),
                                "TaxRate" => 0.002,
                                "LineTax" => $orderItem->getTaxTotal() / 100,
                                "XeroTaxCode" => "V.A.T.",
                                "Guid" => $order->getGuid(),
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
                            ]
                        ],
                        "OrderNumber" => 'SO-'.$order->getNumber(),
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
                        "TaxRate" => 0.002,
                        "XeroTaxCode" => "V.A.T.",
                        "SubTotal" => $order->getItemsTotal() / 100,
                        "TaxTotal" => $order->getTaxTotal() / 100,
                        "Total" => ($order->getItemsTotal() + $order->getTaxTotal()) / 100,
                        "Guid" => $order->getGuid()
                    ];
                    $ordersData[] = $orderData;
                }

            }
        }

        try {
            $responses = [];

            foreach ($ordersData as &$orderData) {
                $subtotal = 0;

                foreach ($orderData['SalesOrderLines'] as $line) {
                    $lineTotal = $line['OrderQuantity'] * $line['UnitPrice'];
                    $subtotal += $lineTotal;
                }

                $taxTotal = $orderData['TaxTotal'];
                $invoiceTotal = $subtotal + $taxTotal;
                $orderData['SubTotal'] = $subtotal;
                $orderData['Total'] = $invoiceTotal;

//                $response = $this->postJson($this->apiId, $this->apiKey, 'SalesOrders', $orderData['Guid'], $orderData);
                $responseData = json_decode($response, true);

                if ($responseData && isset($responseData['Items'])) {
                    $responses[] = $responseData['Items'][0]['Description'];
                } else {
                    $responses[] = ['error' => 'Invalid or missing response'];
                }
            }

            return new JsonResponse($responses);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to post JSON to Unleashed: ' . $e->getMessage());
        }
    }



    function generateGuid(): string
    {
        // Generate a random hexadecimal number (8 characters)
        $part1 = bin2hex(random_bytes(4)); // 4 bytes = 8 hexadecimal characters

        // Generate three random hexadecimal numbers (each 4 characters)
        $part2 = bin2hex(random_bytes(2)); // 2 bytes = 4 hexadecimal characters
        $part3 = bin2hex(random_bytes(2));
        $part4 = bin2hex(random_bytes(2));

        // Generate a random hexadecimal number (12 characters)
        $part5 = bin2hex(random_bytes(6)); // 6 bytes = 12 hexadecimal characters

        // Concatenate parts with dashes
        $guid = sprintf('%s-%s-%s-%s-%s', $part1, $part2, $part3, $part4, $part5);

        return $guid;
    }




}
