<?php

namespace DevStone\UsageCalculator\Model\AdminOrder;

class Create extends \Magento\Sales\Model\AdminOrder\Create
{
    /**
     * @param $item
     * @return array|mixed
     */
    protected function _prepareOptionsForRequest($item)
    {
        $usageOptions = $item->getOptionByCode('usage_options');
        if($usageOptions && $usageOptions->hasData('value')) {
            return json_decode($usageOptions->getValue(), true);
        }
        return parent::_prepareOptionsForRequest($item);
    }
}
