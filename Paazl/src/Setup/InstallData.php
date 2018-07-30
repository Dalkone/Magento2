<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Setup;

use Paazl\Shipping\Setup\PaazlSetup;
use Paazl\Shipping\Setup\PaazlSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var PaazlSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Init
     *
     * @param PaazlSetupFactory $eavSetupFactory
     */
    public function __construct(PaazlSetupFactory $eavSetupFactory,
\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
\Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
\Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeRepository = $attributeRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        /** @var PaazlSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $groupName = 'Paazl';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Default');

        // Create group
        $eavSetup->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', $groupName, 62);

        foreach($eavSetup->getAttributeList() as $attributeInfo) {
            // Create attributes
            $attribute = $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeInfo['attributeCode'],
                [
                    'group' => $groupName,
                    'sort_order' => 40,
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $attributeInfo['label'],
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );
        }

        // create Customer attributes
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(
            'customer_address'
        );
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);


        if ($this->isAttributeAllowedForImport($customerEntity, 'street_name')) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(
                                $customerEntity,
                                'street_name'
                            )
                                ->addData(
                                    [
                                        'attribute_set_id'   => $attributeSetId,
                                        'attribute_group_id' => $attributeGroupId,
                                        'used_in_forms'      => [
                                            'adminhtml_customer_address',
                                            'customer_address_edit',
                                            'customer_register_address'
                                        ],
                                    ]
                                );
                            $attribute->save();
            $customerSetup->addAttribute(
                'customer_address',
                'street_name',
                [
                    'type'             => 'varchar',
                    'label'            => 'Street Name',
                    'input'            => 'text',
                    'required'         => true,
                    'visible'          => true,
                    'visible_on_front' => true,
                    'user_defined'     => true,
                    'position'       => 76,
                    'system'           => 0,
                ]
            );
        }


        if ($this->isAttributeAllowedForImport($customerEntity, 'house_number')) {
            $attribute = $customerSetup->getEavConfig()
                ->getAttribute(
                    'customer_address',
                    'house_number'
                )
                ->addData(
                    [
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => [
                            'adminhtml_customer_address',
                            'customer_address_edit',
                            'customer_register_address'
                        ],
                    ]
                );
            $attribute->save();
            $customerSetup->addAttribute(
                'customer_address',
                'house_number',
                [
                    'type'             => 'varchar',
                    'label'            => 'House Number',
                    'input'            => 'text',
                    'required'         => true,
                    'visible'          => true,
                    'visible_on_front' => true,
                    'user_defined'     => true,
                    'position'       => 74,
                    'system'           => 0,
                ]
            );
        }


        if ($this->isAttributeAllowedForImport($customerEntity, 'house_number_addition')) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(
                'customer_address',
                'house_number_addition'
            )
                ->addData(
                    [
                        'attribute_set_id'   => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms'      => [
                            'adminhtml_customer_address',
                            'customer_address_edit',
                            'customer_register_address'
                        ],
                    ]
                );
            $attribute->save();
            $customerSetup->addAttribute(
                'customer_address',
                'house_number_addition',
                [
                    'type'             => 'varchar',
                    'label'            => 'House Number Addition',
                    'input'            => 'text',
                    'required'         => false,
                    'visible'          => true,
                    'visible_on_front' => true,
                    'user_defined'     => true,
                    'position'       => 75,
                    'system'           => 0,
                ]
            );

            if ($this->isAttributeAllowedForImport($customerEntity, 'house_number', true)) {
                $attribute = $customerSetup->getEavConfig()
                    ->getAttribute(
                        'customer_address',
                        'house_number'
                    )
                    ->addData(
                        [
                            'validate_rules'   => serialize([
                                'input_validation' => 'numeric',
                            ]),
                        ]
                    );
                $attribute->save();
            }
        }

        // @todo Need to do a reindex and clear cache. Maybe add to the readme?
    }


    /**
     * @param $customerEntity
     * @param $attributeCode
     * @param $existingAllowed
     *
     * @return bool
     */
    protected function isAttributeAllowedForImport($customerEntity, $attributeCode, $existingAllowed = false)
    {
        try {
            $this->attributeRepository->get($customerEntity, $attributeCode);
            if ($existingAllowed) {
                return true;
            }
            return false;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $allowed = true;
        }
        foreach (explode(',', $this->scopeConfig->getValue('exclude/' . $attributeCode)) as $v) {
            try {
                $this->attributeRepository->get($customerEntity, trim($v));
                $allowed = false;
                break;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $allowed = true;
            }
        }
        return $allowed;
    }
}
