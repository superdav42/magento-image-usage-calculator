<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DevStone\UsageCalculator\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface UsageCustomerSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get UsageCustomer list.
     * @return UsageCustomerInterface[]
     */
    public function getItems();

    /**
     * Set entity_id list.
     * @param UsageCustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
