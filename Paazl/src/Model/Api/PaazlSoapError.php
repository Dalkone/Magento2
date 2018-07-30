<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Api;

class PaazlSoapError
{
    /** @var array $paazlErrors */
    static $paazlErrors = [
        "1000" 	=>	"The submitted hash is incorrect.",
        "1001" 	=>	"The submitted webshop ID is unknown.",
        "1002" 	=>	"The submitted reference is unknown.",
        "1003" 	=>	"An order with this reference already exists. When you receive this message, you may choose updateOrder instead of orderRequest.",
        "1004" 	=>	"The zipcode + house number combination is incorrect. This may be due to an incorrect zipcode or due to a house number that does not exist for this zipcode.",
        "1005" 	=>	"The status of this order is unknown. Please contact the Paazl Helpdesk.",
        "1006" 	=>	"No total amount for the order is submitted.",
        "1007" 	=>	"The total amount submitted is in the wrong format (e.g. non-numeric)",
        "1008" 	=>	"A unknown country code is submitted.",
        "1009" 	=>	"The assured amount is not submitted.",
        "1010" 	=>	"The assured amount is incorrect  (minimum of 500, maximum of 5000, multiple of 500)",
        "1011" 	=>	"The maximum amount of labels is incorrect.",
        "1012" 	=>	"Incorrect notification service for the servicepoint submitted. You are required to submit an email address (servicepointNotificationEmail) or a mobile phone number (servicepointNotificationMobile)",
        "1013" 	=>	"There are no shipping options available. Possibly no shipping options are assigned to you Paazl account. Another reason may be that the matrix configurations are incorrect.",
        "1014" 	=>	"The telephone number required for DPD ‘before 10.00’ and ‘12.00’ -shipments is not submitted",
        "1015" 	=>	"This shipment requires a valid customs value",
        "1016" 	=>	"Distributor replied with an error message",
        "1017" 	=>	"This type of shipment requires each product to be fully defined. This error code will be returned in case a Fedex or Dynalogic shipment did not come with the required package content values",
        "1018" 	=>	"This shipping option does not support date preference",
        "1019" 	=>	"Invalid date specified for this shipping option",
        "1020" 	=>	"Labels have already been generated for this (Dynalogic) order and cannot be regenerated. A Dynalogic shipment can only be pre-registered /printed once.",
        "1021" 	=>	"This order requires a non zero order weight",
        "1022" 	=>	"This order requires a description",
        "1023" 	=>	"Invalid license key supplied",
        "1024" 	=>	"This order cannot be changed",
        "1025" 	=>	"This order contains errors",
        "1026" 	=>	"Delivery Date module is inactive",
        "1027" 	=>	"This order requires an e-mail address",
        "1028" 	=>	"No such barcode",
        "1029" 	=>	"Labels for this shipment cannot be regenerated",
        "1030" 	=>	"Invalid or missing shipping option",
        "1031" 	=>	"No pickup request options available",
        "1032" 	=>	"Invalid or missing pickup window",
        "1033" 	=>	"This pickup contains errors",
        "1034" 	=>	"No such distributor",
        "1035" 	=>	"No such pickup request",
        "1036" 	=>	"No such pickup request option",
        "1037" 	=>	"This pickup cannot be cancelled",
        "1038" 	=>	"Missing parameter",
        "1039" 	=>	"No delivery estimates available",
        "1040" 	=>	"No shipping options available after filter",
        "1041" 	=>	"Invalid distributor",
        "1042" 	=>	"No compatible open shipment batch available",
        "1043" 	=>	"No such shipment batch",
        "1044" 	=>	"This shipment batch has already been closed",
        "1045" 	=>	"Ambiguous delivery estimate request",
        "1046" 	=>	"Package content determines package count",
        "1047" 	=>	"Invalid or missing changed since date",
        "1048" 	=>	"Upstream server error",
        "1049" 	=>	"Invalid destination",
        "1050" 	=>	"Invalid or missing service point code",
        "1051" 	=>	"Invalid or missing service point account number",
        "1052" 	=>	"No checkout session available for this order",
        "1053" 	=>	"Missing permission",
        "1054" 	=>	"No barcode given",
        "1055" 	=>	"No proof of delivery document available",
        "1056" 	=>	"Invalid daterange",
        "1057" 	=>	"Order changed by concurrent request",
        "1058" 	=>	"Unsupported operation"
    ];

    /**
     * @param $code
     * @return string
     */
    public static function getMessageByCode($code)
    {
        if(array_key_exists($code, self::$paazlErrors))
            return self::$paazlErrors[$code];
        else
            return "Unknown error (error code not available)";
    }
}
