<?php


namespace DevStone\UsageCalculator\Api\Data;

interface UsageInterface
{
    const NAME = 'name';

    /**
     * Get usage_id
     * @return string|null
     */
    public function getId();

    /**
     * Set usage_id
     * @param string $usage_id
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface
     */
    public function setId($usageId);
    
    /**
     * Get list of product options
     *
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface[]|null
     */
    public function getOptions();

    /**
     * Set list of product options
     *
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null);

    /**
     * Set category_id
     * @param string $categoryId
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface
     */

    public function setCategoryId($categoryId);

    /**
     * Get category_id
     * @return string|null
     */

    public function getCategoryId();

}
