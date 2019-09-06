<?php


namespace DevStone\UsageCalculator\Api\Data;

interface SizeInterface
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
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface
     */
    public function setName($name);

}
