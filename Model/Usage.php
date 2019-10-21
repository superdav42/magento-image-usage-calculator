<?php

/**
 * Usage.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Usage extends AbstractModel implements IdentityInterface, \DevStone\UsageCalculator\Api\Data\UsageInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'devstone_usagecalculator_usage';

    /**
     * @var string
     */
    protected $_cacheTag = 'devstone_usagecalculator_usage';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'devstone_usagecalculator_usage';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('DevStone\UsageCalculator\Model\ResourceModel\Usage');
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
    
    /**
     * Get all options of usage
     *
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface[]|null
     */
    public function getOptions()
    {
        return $this->getData('options');
    }

    /**
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        $this->setData('options', $options);
        return $this;
    }
    
    public function afterSave() 
    {
        parent::afterSave();
        
        $saveHandler = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\DevStone\UsageCalculator\Model\Usage\Option\SaveHandler::class);
        
        $saveHandler->execute($this);
    }
    
    public function afterLoad() {
        parent::afterLoad();
        
        $readHandler = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\DevStone\UsageCalculator\Model\Usage\Option\ReadHandler::class);
        
        $readHandler->execute($this);
    }

    /**
     * Set category_id
     * @param string $categoryId
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData('category_id',$categoryId);
    }

    /**
     * Get category_id
     * @return string|null
     */
    public function getCategoryId()
    {
        return $this->getData('category_id');
    }
}
