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
    public function __construct(
        Context $context,
        protected \Magento\Store\Model\Information $storeInfo,
        protected \Magento\Store\Model\Store $store,
        array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getPhone() {
        return $this->storeInfo->getStoreInformationObject($this->store)->getPhone();
    }
}
