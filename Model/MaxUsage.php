<?php

namespace DevStone\UsageCalculator\Model;


class MaxUsage extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     *
     */
    const CACHE_TAG = \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE . '_limit';

    /**
     * @var string
     */
    protected $_cacheTag = \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE . '_limit';

    /**
     * @var string
     */
    protected $_eventPrefix = \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE . '_limit';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\DevStone\UsageCalculator\Model\ResourceModel\MaxUsage::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
