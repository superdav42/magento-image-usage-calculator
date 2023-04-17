<?php
/**
 * Info
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Block\Catalog\Product;

use Magento\Catalog\Block\Product\Context;

Class Info extends \Magento\Framework\View\Element\Template
{
    protected \Magento\Store\Model\Information $storeInfo;

    protected \Magento\Store\Model\Store $store;

    public function __construct(
        Context $context,
        \Magento\Store\Model\Information $storeInfo,
        \Magento\Store\Model\Store $store,
        array $data = [])
    {
        $this->storeInfo = $storeInfo;
        $this->store = $store;
        parent::__construct($context, $data);
    }

    public function getPhone() {
        return $this->storeInfo->getStoreInformationObject($this->store)->getPhone();
    }
}
