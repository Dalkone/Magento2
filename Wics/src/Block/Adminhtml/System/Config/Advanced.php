<?php
namespace WICS\Connector\Block\Adminhtml\System\Config;

use \Magento\Framework\Module\ResourceInterface;
use \WICS\Connector\Helper\ConfigHelper;

class Advanced extends \Magento\Config\Block\System\Config\Form\Field {
    /**
     * @var ModuleResource
     */
    protected $moduleResource;

    protected $configHelper;

    /**
     * Advanced constructor.
     *
     * @param ResourceInterface $moduleResource
     */
    public function __construct(
        \Magento\Framework\Module\ResourceInterface $moduleResource,
        ConfigHelper $configHelper
    ){
        $this->moduleResource = $moduleResource;
        $this->configHelper = $configHelper;
    }

    /**
     * Render fieldset html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        return $this->_decorateRowHtml($element, "<td class='label'><label for='wics_connector_general_api_dataversion'>
            DB version:</label></td><td class='value' style='vertical-align: bottom; font-weight: bold;'>
            ".$this->moduleResource->getDataVersion('WICS_Connector')."</td></tr><tr>
            <td class='label'><label for='wics_connector_general_api_schemaversion'>
            Module version:</label></td><td class='value' style='vertical-align: bottom; font-weight: bold;'>
            ".$this->configHelper->getModuleVersion('WICS_Connector')."</td>");
    }
}
