<?php

namespace DevStone\UsageCalculator\Model\ResourceModel;


/**
 * Class MaxUsage
 * @package DevStone\UsageCalculator\Model\ResourceModel
 */
class MaxUsage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE . '_limit',
            'usage_id'
        );
        $this->_isPkAutoIncrement = false;
    }
}
