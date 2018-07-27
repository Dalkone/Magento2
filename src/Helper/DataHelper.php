<?php
namespace WICS\Connector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class DataHelper
 * @package WICS\Connector\Helper
 */
class DataHelper extends AbstractHelper {
    public function splitStreet($streetStr = "") {
        $pattern = '/^(\d*[\wäöüß\d \'\-\.]+)[,\s]+(\d+)\s*([\wäöüß\d\-\/]*)$/i';
        $matchResult = preg_match($pattern, $streetStr, $aMatch);

        $street = (isset($aMatch[1])) ? $aMatch[1] : "";
        $number = (isset($aMatch[2])) ? $aMatch[2] : "";
        $numberAddition = (isset($aMatch[3])) ? $aMatch[3] : "";

        return array("street" => $street, "number" => $number, "numberAddition" => $numberAddition );
    }
}