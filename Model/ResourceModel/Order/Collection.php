<?php

namespace DevStone\UsageCalculator\Model\ResourceModel\Order;

/**
 * Class Collection
 * @package DevStone\UsageCalculator\Model\ResourceModel\Order
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    /**
     * @return $this|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['oi' => 'sales_order_item'],
                'e.entity_id = oi.order_id',
                [
                    'ca.product_options as product_options'
                ]
            )->group(
                'e.entity_id'
            );
        return $this;
    }
}
