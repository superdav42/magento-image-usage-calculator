<?php


namespace DevStone\UsageCalculator\Api\Data;

interface CategorySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Usage list.
     * @return \DevStone\UsageCalculator\Api\Data\CategoryInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \DevStone\UsageCalculator\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
