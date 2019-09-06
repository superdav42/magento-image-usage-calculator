<?php

/**
 * Category.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Category extends AbstractModel implements IdentityInterface, \DevStone\UsageCalculator\Api\Data\CategoryInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'devstone_usagecalculator_category';

    /**
     * @var string
     */
    protected $_cacheTag = 'devstone_usagecalculator_category';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'devstone_usagecalculator_category';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\DevStone\UsageCalculator\Model\ResourceModel\Category::class);
    }
    
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Save from collection data
     *
     * @param array $data
     * @return $this|bool
     */
    public function saveCollection(array $data)
    {
        if (isset($data[$this->getId()])) {
            $this->addData($data[$this->getId()]);
            $this->getResource()->save($this);
        }
        return $this;
    }
    
    public function setName($name) {
        $this->setData(self::NAME, $name);
        return $this;
    }
    
    public function getName()
    {
        return $this->getData(self::NAME);
    }
}
