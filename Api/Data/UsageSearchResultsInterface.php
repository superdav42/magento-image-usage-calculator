<?php


namespace DevStone\UsageCalculator\Api\Data;

interface UsageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Usage list.
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \DevStone\UsageCalculator\Api\Data\UsageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
