<?php

namespace DevStone\UsageCalculator\Api;

/**
 * @api
 */
interface UsageCustomOptionRepositoryInterface
{
    /**
     * Get the list of custom options for a specific usage
     *
     * @param string $sku
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface[]
     */
    public function getList($sku);

    /**
     * @param \DevStone\UsageCalculator\Api\Data\UsageInterface $usage
     * @param bool $requiredOnly
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface[]
     */
    public function getUsageOptions(
        \DevStone\UsageCalculator\Api\Data\UsageInterface $usage,
        $requiredOnly = false
    );

    /**
     * Get custom option for a specific usage
     *
     * @param string $sku
     * @param int $optionId
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface
     */
    public function get($sku, $optionId);

    /**
     * Delete custom option from usage
     *
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface $option
     * @return bool
     */
    public function delete(\DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface $option);

    /**
     * Duplicate product options
     *
     * @param \DevStone\UsageCalculator\Api\Data\UsageInterface $product
     * @param \DevStone\UsageCalculator\Api\Data\UsageInterface $duplicate
     * @return mixed
     * @since 101.0.0
     */
    public function duplicate(
        \DevStone\UsageCalculator\Api\Data\UsageInterface $product,
        \DevStone\UsageCalculator\Api\Data\UsageInterface $duplicate
    );

    /**
     * Save Custom Option
     *
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface $option
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface
     */
    public function save(\DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface $option);

    /**
     * @param string $sku
     * @param int $optionId
     * @return bool
     */
    public function deleteByIdentifier($sku, $optionId);
}
