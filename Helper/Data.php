<?php

declare(strict_types=1);

namespace DevStone\UsageCalculator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    public function getCustomLicenseId(): int
    {
        return (int) $this->scopeConfig->getValue(
            'usage_cal/general/category_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
