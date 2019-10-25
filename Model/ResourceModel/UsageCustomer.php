<?php

namespace DevStone\UsageCalculator\Model\ResourceModel;

/**
 * Class UsageCustomer
 * @package DevStone\UsageCalculator\Model\ResourceModel
 */
class UsageCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE . '_customer',
            'entity_id'
        );
    }
}