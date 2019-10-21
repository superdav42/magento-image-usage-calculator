<?php

namespace DevStone\UsageCalculator\Model;


/**
 * Class UsageCustomer
 * @package DevStone\UsageCalculator\Model
 */
class UsageCustomer extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{

    /**
     *
     */
    const CACHE_TAG = \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE.'_customer';

    /**
     * @var string
     */
    protected $_cacheTag = \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE.'_customer';

    /**
     * @var string
     */
    protected $_eventPrefix = \DevStone\UsageCalculator\Setup\UsageSetup::ENTITY_TYPE_CODE.'_customer';


    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}