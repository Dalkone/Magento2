<?php
namespace WICS\Connector\Helper\Connectors;

use Magento\Catalog\Model\ProductRepository;
use Magento\Bundle\Model\OptionRepository;
use Magento\Framework\App\Helper\Context;
use WICS\Connector\Helper\ConfigHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use WICS\Connector\Helper\DataHelper;

class HagemanConnector extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var OrderRepositoryInterface
     */                                              
    protected $orderRepository;

    /**
     * @var DataHelper
     */
    protected $data;

    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var errors
     */
    protected $errors;

    /**
     * HagemanConnector constructor.
     * @param Context $context
     * @param ConfigHelper $config
     * @param OrderRepositoryInterface $orderRepository
     * @param DataHelper $data
     * @param ProductRepository $productRepository
     * @param OptionRepository $optionRepository
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        Context $context,
        ConfigHelper $config,
        OrderRepositoryInterface $orderRepository,
        DataHelper $data,
        ProductRepository $productRepository,
        OptionRepository $optionRepository
    ) {
        $this->context = $context;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->data = $data;
        $this->productRepository = $productRepository;
        $this->optionRepository = $optionRepository;
        $this->errors = [];
        parent::__construct($context);
    }

    /**
     * Get the connector label
     *
     * @return string
     */
    public function getLabel() {
        return __("Hageman API");
    }

    /**
     * Login to the API
     *
     * @return bool|mixed true or http response code
     */
    public function login()
    {
        $result = $this->call([
            "administration" => $this->config->get(ConfigHelper::API_CLIENT),
            "type" => "administrations",
            "method" => "fetch"
        ]);

        if(empty($result->error)) {
            $this->_logger->debug($this->getLabel() . ": Login success");
            return true;
        } else {
            $this->_logger->debug($this->getLabel() . ": Login failed");
            return false;
        }
    }

    /**
     * Send the order to the HAGEMAN API
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool|mixed
     */
    public function putOrder(\Magento\Sales\Model\Order $order)
    {
        $orderLines = [];

        /** @var $item \Magento\Sales\Api\Data\OrderItemInterface */
        foreach ( $order->getAllVisibleItems() as $item ) {

            switch ( $item->getProductType() ) {
                case "configurable":
                    $options = $item->getProductOptions();
                    $curProduct = $item->getProduct() ?? $this->productRepository->getById($item->getProductId(), false);

                    $orderLines[] = [
                        "OrdItemNum" => $options["simple_sku"],
                        "OrdQtyStck" => (int) $item->getQty_ordered(),
                        "NetUntPric" => $item->getPrice() ?? 0,
                        "SalUnit" => $curProduct->getHu() ?? "ST"
                    ];
                    break;

                case "bundle":
                    $options = $item->getProductOptions();
                    $curProduct = $item->getProduct() ?? $this->productRepository->getById($item->getProductId(), false);
                    $optionIds = [];

                    /* transform the array with option values to always be an array */
                    foreach($options["info_buyRequest"]["bundle_option"] as $bKey => $bOption) {
                        if(is_array($bOption)){
                            $optionIds[$bKey] = $bOption;
                        } else {
                            $optionIds[$bKey] = [$bOption];
                        }
                    }

                    foreach($options["bundle_options"] as $option) {
                        $optionToSku = [];
                        try {
                            $optionProducts = $this->optionRepository->get($curProduct->getSku(), $option["option_id"])->getProductLinks();

                            foreach ($optionProducts as $_product) {
                                $optionToSku[$_product["id"]] = $_product["sku"];
                            }
                        } catch (\Exception $e) {
                            $order->setWics_status("error")
                                ->addStatusToHistory($order->getStatus(), __("Failed to sent order to %1. Error: ", $this->getLabel()) . "Bundle product ". $e->getMessage());
                            $this->orderRepository->save($order);

                            return false;
                        }

                        foreach($option["value"] as $_key => $_item) {
                            $curId = $optionIds[$option["option_id"]][$_key];
                            $orderLines[] = [
                                "OrdItemNum" => $optionToSku[$curId],
                                "OrdQtyStck" => (int) $_item["qty"] * $item->getQty_ordered(),
                                "NetUntPric" => $item->getPrice() ?? 0,
                                "SalUnit" => $curProduct->getHu() ?? "ST"
                            ];
                        }
                    }
                    break;

                default:
                    $curProduct = $item->getProduct() ?? $this->productRepository->getById($item->getProductId(), false);
                    $orderLines[] = [
                        "OrdItemNum" => $item->getSku(),
                        "OrdQtyStck" => (int)$item->getQty_ordered(),
                        "NetUntPric" => $item->getPrice() ?? 0,
                        "SalUnit" => $curProduct->getHu() ?? "ST"
                    ];
                    break;
            }
        }
        
        $this->_logger->debug($this->getLabel() . ": Orderlines -- " . json_encode($orderLines));

        $shipAddr = $order->getShippingAddress();
        $shipaddrParts = [
            "street" => !empty($shipAddr->getStreetLine(1)) ? $shipAddr->getStreetLine(1) : $shipAddr->getStreet(),
            "number" => "", "numberAddition" => ""
        ];
        if(strtolower($shipAddr->getCountryId()) == "nl") {
            $shipaddrParts = $this->data->splitStreet($shipAddr->getStreetLine(1));
        }

        $invAddr = $order->getBillingAddress();
        $invAddrParts = [
            "street" => !empty($invAddr->getStreetLine(1)) ? $invAddr->getStreetLine(1) : $invAddr->getStreet(),
            "number" => "", "numberAddition" => ""
        ];
        if(strtolower($invAddr->getCountryId()) == "nl") {
            $invAddrParts = $this->data->splitStreet($invAddr->getStreetLine(1));
        }

        /* Set invoice address */
        if ($invAddr->getCompany() != "" ) {
            $invName = $invAddr->getCompany();
            $invContact = str_replace("  "," ", $invAddr->getFirstName() . " " . $invAddr->getMiddleName() . " " . $invAddr->getLastName());
        } else {
            $invName = str_replace("  "," ",$invAddr->getFirstName() . " " . $invAddr->getMiddleName() . " " . $invAddr->getLastName());
            $invContact = "";
        }

        if ($invAddr->getRegionCode() != "") {
            $invRegion = $invAddr->getRegionCode();
        } else {
            $invRegion = $invAddr->getRegion();
        }

        $invoiceAddress = [
            "AdrType" => "FA",
            "DelvZip" => $invAddr->getPostcode(),
            "DelvStrNum" => $invAddrParts["number"],
            "StreetNumExt" => $invAddrParts["numberAddition"],
            "DelvName" => $invName,
            "NameLast" => !empty($invContact) ? $invContact : "",
            "DelvStreet" => $invAddrParts["street"],
            "DelvSubCntry" => $invRegion == null ? "" : $invRegion,
            "DelvCity" => $invAddr->getCity(),
            "DelvAddress2" => $invAddr->getStreetLine(2),
            "EMailAddr" => $invAddr->getEmail(),
            "PhoneNum" => $invAddr->getTelephone(),
            "DelvCntry" => $invAddr->getCountryId(),
            "language" => "NL"
        ];

        /* Set shipping address */
        if($shipAddr->getCompany() != "" ) {
            $shipName = $shipAddr->getCompany();
            $shipContact = str_replace("  "," ", $shipAddr->getFirstName() . " " . $shipAddr->getMiddleName() . " " . $shipAddr->getLastName());
        } else {
            $shipName = str_replace("  "," ", $shipAddr->getFirstName() . " " . $shipAddr->getMiddleName() . " " . $shipAddr->getLastName());
            $shipContact = "";
        }

        if ( $shipAddr->getRegionCode() != "") {
            $shpRegion = $shipAddr->getRegionCode();
        } else {
            $shpRegion = $shipAddr->getRegion();
        }

        $shippingAddress = [
            "AdrType" => "20",
            "DelvZip" => $shipAddr->getPostcode(),
            "DelvStrNum" => $shipaddrParts["number"],
            "StreetNumExt" => $shipaddrParts["numberAddition"],
            "DelvName" => $shipName,
            "NameLast" => !empty($shipContact) ? $shipContact : "",
            "DelvStreet" => $shipaddrParts["street"],
            "DelvSubCntry" => $shpRegion == null ? "" : $shpRegion,
            "DelvCity" => $shipAddr->getCity(),
            "DelvAddress2" => $shipAddr->getStreetLine(2),
            "EMailAddr" => $shipAddr->getEmail(),
            "PhoneNum" => $shipAddr->getTelephone(),
            "DelvCntry" => $shipAddr->getCountryId(),
            "language" => "NL"
        ];
        
        /* Build post */
        $post = [
            "OrdCustRef" => $order->getIncrementId(),
            "OrdCustRe2" => "",
            "Orddate"   => date("Y-m-d"),
            "ReqDlvDat"   => date("Y-m-d", strtotime("+1 day")),
//            "DelvCond" => "CIF",   // For future purposes?
            "PurOrgNum" => $this->config->get(ConfigHelper::WEBSHOP_ID),
            "ShpLabelService" => "8000000",
            "MemoText" => $order->getCustomerNote() == null ? "" : $order->getCustomerNote(),
            "ttOrdDlvAd" => [
                $invoiceAddress,
                $shippingAddress
            ],
            "ttOrdDtl" => $orderLines
        ];
        
        /* Paazl Check */
        $shippingMethod = $order->getShippingMethod() ?? "";
        $checksum = explode("_", strtolower($shippingMethod));
        if(in_array($checksum[0], ["paazl", "paazlp"])) {
            $post["PaazlFrontEnd"] = "true";
            $post["PaazlUpdate"] = "true";
            
            unset($checksum[0]);
            $shippingMethod = implode("_", $checksum);
            $shipmentMethod = $this->layer("shipment_method", ["method" => $shippingMethod]);
            $post["Shipper"] = $shipmentMethod[0]["shipper"] ?? "";
            $post["DelvMode"] = $shipmentMethod[0]["delivery_mode"] ?? "";
        } 

        /* Set post data */
        $postData = [
            "administration" => $this->config->get(ConfigHelper::API_CLIENT),
            "type" => "orders",
            "method" => "maintain",
            "action" => "create",
            "entries" => [
                "ttOrdMst" => [
                    $post
                ]
            ]
        ];
//        echo json_encode($postData); die();

        $response = $this->call($postData);
        if ($response)
        {
            if (empty($response->error) ) { //Order processed successful.
                
                if($response->result) {
                    $order->setWics_status("processing")
                        ->addStatusToHistory($order->getStatus(), __("Order sent to %1.", $this->getLabel()));
                    $this->orderRepository->save($order);

                    return true;
                }

            } else { //Soft error occurred, store error feedback in the order.
                $isReferenceError = false;
                
                /* check for oneliner error */
                $response->error = is_array($response->error) ? $response->error : array($response->error);
                if(count($response->error) == 1) {
                    /* Check for unique error */
                    foreach($response->error as $err) {
                        if(str_ireplace(array("unique", "uniek"), "", $err) != $err) {
                            $isReferenceError = true;
                        } 
                    } 
                }
                
                if($isReferenceError) {
                    $order->setWics_status("processing")
                        ->addStatusToHistory($order->getStatus(), __("Order found by %1.", $this->getLabel()));
                    $this->orderRepository->save($order);
                } else {
                    //$order->setStatus("wics_failed") //Removed order status update since order status is a separate column since v1.0.0.
                    $order->setWics_status("error")
                        ->addStatusToHistory($order->getStatus(), __("Failed to sent order to %1.", $this->getLabel()));
                    $this->orderRepository->save($order);

                    $this->errors = $response->error;
                }
                $this->_logger->debug($this->getLabel() . ": Wics error -- " . json_encode($response->error));

                return $isReferenceError;
            }

        } else { //Hard error occurred, probably server downtime?
            //$order->setStatus("wics_failed") //Removed order status update since order status is a separate column since v1.0.0.
            $order->setWics_status("pending")
                ->addStatusToHistory($order->getStatus(), __("Failed to sent order to %1. Scheduled for retry.", $this->getLabel()));
            $this->orderRepository->save($order);
            return false;
        }
    }

    /**
     * Fetch shipment slip for order
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool|mixed
     */
    public function getOrderShipment(\Magento\Sales\Model\Order $order)
    {
        $response = $this->call([
            "administration" => $this->config->get(ConfigHelper::API_CLIENT),
            "type" => "orders",
            "method" => "fetch",
            "options" => [
                "RequestParams" => [
                    "CurrentPage" => 1,
                    "PageNumber" => 1,
                    "PageSize" => 1
                ],
                "Filters" => [
                    [
                        "Order" => 1,
                        "TableName" => "ttOrdMst",
                        "FieldName" => "OrdCustRef",
                        "Operator" => "IsEqual",
                        "FilterValue" => $order->getIncrementId()
                    ]
                ],
                "Sorts" => [
                    "Order" => 1,
                    "TableName" => "ttOrdMst",
                    "FieldName" => "Orddate",
                    "Direction" => "desc"
                ]
            ]
        ]);
        
        if($response)
        {
            if(empty($response->error)) {
                if(count($response->result) > 0) {
                    if(isset($response->result[0]->ttColloMst)) {
                        $response = $response->result[0];
                        $response->ttColloMst = is_array($response->ttColloMst) ? $response->ttColloMst : [$response->ttColloMst];
                        $shipment = $response->ttColloMst[0];

                        $this->_logger->debug($this->getLabel() . ": Shipment for order #".$order->getIncrementId()." fetched successful.");

                        //Update the wics order status
                        $order->setWicsStatus("shipped")
                            ->addStatusToHistory($order->getStatus(), __($this->getLabel() . ": Shipment imported"));
                        $this->orderRepository->save($order);
                        
                        $shipmentMethod = $this->layer("shipment_method", ["delivery_mode" => $response->DelvMode]);
                        $trackData = array(
                            "carrier_code" => $shipmentMethod[0]["shipper"] ?? $order->getShippingMethod(),
                            "title" => $shipmentMethod[0]["description"] ?? $order->getShippingMethod(),
                            "number" => $shipment->TrackTraceURL
                        );

                        if ($this->config->get(ConfigHelper::TRACKING_TYPE) == "code" ) {
                            $trackData["number"] =  $shipment->LabelCode;
                        }

                        return $trackData;
                    } else { //No tracking data yet
                        $this->_logger->debug($this->getLabel() . ": No shipment found for order #".$order->getIncrementId());
                    }
                } else { //No order found matching given reference, try to create
                    $this->putOrder($order);
                }
            } else { //Soft error occurred, store error feedback in the order.
                $this->_logger->debug($this->getLabel() . ": No shipment found for order #".$order->getIncrementId());
            }
        } else { //Hard error occurred, probably server downtime?
            $this->_logger->debug($this->getLabel() . ": Error occurred while fetching shipment for order #".$order->getIncrementId() . " Response: " . print_r($response, true));
        }
        
        return false;
    }

    /**
     * Get array of stock items from WICS
     *
     * @return array
     */
    public function getStock()
    {
        $pageCount = 1;
        $hasPages = true;
        $items = [];
        
        while($hasPages) {
            $response = $this->call([
                "administration" => $this->config->get(ConfigHelper::API_CLIENT),
                "type" => "stock",
                "method" => "fetch",
                "options" => [
                    "RequestParams" => [
                        "CurrentPage" => $pageCount,
                        "PageNumber" => $pageCount,
                        "PageSize" => 100
                    ]
                ]
            ]);
            
            if(empty($response->error) && !empty($response->result)) {
                foreach($response->result as $result) {
                    $itemDetails = [];
                    $physicalStock = 0;
                    if(isset($result->ttStockMst)) {
                        $result->ttStockMst = is_array($result->ttStockMst) && isset($result->ttStockMst[0]) ? $result->ttStockMst : [$result->ttStockMst];
                        foreach($result->ttStockMst as $stock) {
                            $itemDetails[] = (object) [
                                "warehouse" => isset($stock->WHouse[0]) ? (string) $stock->WHouse[0] : (isset($stock->WHouse) ? (string) $stock->WHouse : ""),
                                "salable" => 0,
                                "physical" => (int) $stock->FysStock,
                                "incoming" => 0
                            ];
                            $physicalStock += (int) $stock->FysStock;
                        }
                    }
                    
                    $items[] = (object) [
                        "id" => $result->ItemMstId,
                        "article_code" => $result->WhsCustItem,
                        "article_description" => $result->DescTxt1,
                        "total" => (object) [
                            "salable" => (int) $result->FATPCALC,
                            "physical" => $physicalStock,
                            "incoming" => 0
                        ],
                        "details" => $itemDetails
                    ];
                }
                
                $pageCount++;
            } else {
                $hasPages = false;
            }
        }
        
        return $items;
    }

    /**
     * Execute call to HAGEMAN API
     *
     * @param string $endpoint
     * @param array|null $request
     * @param string $method
     *
     * @return bool|mixed
     */
    public function getErrors()
    {        
        return $this->errors;
    }

    /**
     * Execute call to HAGEMAN LAYER
     */
    private function layer($request, $option = [])
    {
        if($request == "shipment_method") {
            $db = new \mysqli(
                $this->config->get(ConfigHelper::LAYER_HOST) ?? "phpmyadmin.fulfilmentservices.nl",
                $this->config->get(ConfigHelper::LAYER_USER) ?? "hageadman",
                $this->config->get(ConfigHelper::LAYER_PASSWORD) ?? "H4g3m4ns4f3!", 
                $this->config->get(ConfigHelper::LAYER_DATABASE) ?? "hageman_wics",
                $this->config->get(ConfigHelper::LAYER_PORT) ?? 3306
            );
            
            $where = [];
            foreach($option as $key => $value) {
                $where[] = "`$key` = '$value'";
            }
            $query = sprintf(
                "select * from `v2017_shipper_method`%s",
                !empty($where) ? " where " . implode(" and ", $where) : ""
            );
            $result = $db->query($query);
            
            if($result) {
                $array = [];
                while($row = $result->fetch_assoc()) {
                    $array[] = $row;
                }
                return !empty($array) ? $array : false;
            }
        }
        
        return false;
    }

    /**
     * Execute call to HAGEMAN API
     *
     * @param string $endpoint
     * @param array|null $request
     * @param string $method
     *
     * @return bool|mixed
     */
    private function call($request)
    {
        $request = json_encode($request);
        
        $ch = curl_init($this->config->get(ConfigHelper::API_URL));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Environment: " . $this->config->get(ConfigHelper::API_ENVIRONMENT),
            "Username: " . $this->config->get(ConfigHelper::API_KEY),
            "Password: " . $this->config->get(ConfigHelper::API_SECRET),
            "Content-Length: " . strlen($request))
        );

        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        
        /* Error logging for debug mode */
        if(isset($response->error) && !empty($response->error)) {
            $response->error = is_array($response->error) ? $response->error : [$response->error];
            $this->_logger->debug($this->getLabel() . ": Call error -- " . implode(" -- ", $response->error));
        }
        
        return $response;
    }
}