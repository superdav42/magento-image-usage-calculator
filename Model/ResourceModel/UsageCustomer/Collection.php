<?php

namespace DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer;

/**
 * Class Collection
 * @package DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\DevStone\UsageCalculator\Model\UsageCustomer::class,
            \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer::class);
    }
}