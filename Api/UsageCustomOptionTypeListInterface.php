<?php
namespace DevStone\UsageCalculator\Api;

/**
 * @api
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
