<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DevStone\UsageCalculator\Api;

/**
 * @api
 * @since 100.0.2
 */
interface UsageCustomOptionTypeListInterface
{
    /**
     * Get custom option types
     *
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionTypeInterface[]
     */
    public function getItems();
}
