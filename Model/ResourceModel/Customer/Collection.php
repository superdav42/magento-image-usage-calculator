<?php

namespace DevStone\UsageCalculator\Model\ResourceModel\Customer;

/**
 * Class Collection
 * @package DevStone\UsageCalculator\Model\ResourceModel\Customer
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{

    /**
     * @return $this|\Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->columns(
                ['name' => "CONCAT(e.firstname, ' ', e.lastname)"]
            )->joinLeft(
                ['ca' => 'customer_address_entity'],
                'e.entity_id = ca.parent_id',
                [
                    'ca.company as company'
                ]
            )->group(
                'ca.parent_id'
            );
        return $this;
    }
}
