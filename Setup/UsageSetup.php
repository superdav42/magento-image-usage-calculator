<?php
/**
 * UsageSetup
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Setup;

use Magento\Eav\Setup\EavSetup;

/**
 * @codeCoverageIgnore
 */
class UsageSetup extends EavSetup
{
    /**
     * Entity type for Usage EAV attributes
     */
    const ENTITY_TYPE_CODE = 'devstone_usage';

    /**
     * Retrieve Entity Attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getAttributes()
    {
        return [
            'name' => [
                'type' => 'varchar',
                'label' => 'Name',
                'input' => 'text',
                'sort_order' => 1,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
            ],
            'is_active' => [
                'type' => 'int',
                'label' => 'Is Active',
                'input' => 'select',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'sort_order' => 10,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
            ],
            'terms' => [
                'type' => 'text',
                'label' => 'Description',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 4,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'group' => 'General Information',
            ],
            'price' => [
                'type' => 'decimal',
                'label' => 'Price',
                'input' => 'price',
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                'sort_order' => 1,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General',
            ],
            'max_usage' => [
                'type' => 'int',
                'label' => 'Max Usage',
                'input' => 'text',
                'sort_order' => 1,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
            ]
        ];
    }

    /**
     * Retrieve default entities: usage
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $categoryAttributes = $this->getAttributes();
        unset($categoryAttributes['price']);

        $entities = [
            self::ENTITY_TYPE_CODE => [
                'entity_model' => \DevStone\UsageCalculator\Model\ResourceModel\Usage::class,
                'attribute_model' => \DevStone\UsageCalculator\Model\ResourceModel\Eav\Attribute::class,
                'table' => self::ENTITY_TYPE_CODE . '_entity',
                'increment_model' => null,
                'additional_attribute_table' => self::ENTITY_TYPE_CODE . '_eav_attribute',
                'entity_attribute_collection' => \DevStone\UsageCalculator\Model\ResourceModel\Attribute\Collection::class,
                'attributes' => $this->getAttributes(),
            ],
        ];
        return $entities;
    }
}
