<?php

namespace DevStone\UsageCalculator\Api\Data;

/**
 * @api
 */
interface UsageCustomOptionValuesInterface
{
    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * Get Option type id
     *
     * @return int|null
     */
    public function getOptionTypeId();

    /**
     * Set Option type id
     *
     * @param int $optionTypeId
     * @return int|null
     */
    public function setOptionTypeId($optionTypeId);
}
