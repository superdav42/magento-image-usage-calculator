<?php

/**
 * Usage.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Model;

use DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface;
use DevStone\UsageCalculator\Api\Data\UsageInterface;
use DevStone\UsageCalculator\Model\Usage\Option\ReadHandler;
use DevStone\UsageCalculator\Model\Usage\Option\SaveHandler;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\Action\Collection;
use Magento\Rule\Model\Condition\Combine;

/**
 * Class Usage
 * @package DevStone\UsageCalculator\Model
 */
class Usage extends \Magento\Rule\Model\AbstractModel implements IdentityInterface, UsageInterface
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

    public function __construct(
        protected CombineFactory $combineFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null
    ){
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data, $extensionFactory, $customAttributeFactory, $serializer);
    }

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
     * @return UsageCustomOptionInterface[]|null
     */
    public function getOptions()
    {
        return $this->getData('options');
    }

    /**
     * @param UsageCustomOptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        $this->setData('options', $options);
        return $this;
    }

    /**
     * @return AbstractModel|void
     */
    public function afterSave()
    {
        parent::afterSave();

        $saveHandler = ObjectManager::getInstance()
            ->get(SaveHandler::class);

        $saveHandler->execute($this);
    }

    /**
     * @return AbstractModel|void
     */
    public function afterLoad()
    {
        parent::afterLoad();

        $readHandler = ObjectManager::getInstance()
            ->get(ReadHandler::class);

        $readHandler->execute($this);
    }

    /**
     * Set category_id
     * @param string $categoryId
     * @return UsageInterface
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData('category_id', $categoryId);
    }

    /**
     * Get category_id
     * @return string|null
     */
    public function getCategoryId()
    {
        return $this->getData('category_id');
    }


    /**
     * Prepare data before saving
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSave()
    {
        // Serialize conditions
        if ($this->getConditions()) {
            $con = $this->serializer->serialize($this->getConditions()->asArray());
            $this->setConditionsSerialized($this->serializer->serialize($this->getConditions()->asArray()));
            $this->_conditions = null;
        }


        parent::beforeSave();
        return $this;
    }

    public function getActions()
    {
        return null;
    }

    /**
     * Set rule combine conditions model
     *
     * @param Combine $conditions
     * @return $this
     */
    public function setConditions($conditions)
    {
        $this->_conditions = $conditions;
        return $this;
    }


    /**
     * Reset rule combine conditions
     *
     * @param null|Combine $conditions
     * @return $this
     */
    protected function _resetConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setConditions($conditions);

        return $this;
    }

    /**
     * Getter for rule conditions collection
     *
     * @return Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }


    /**
     * Initialize rule model data from array
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        if (isset($arr['conditions'])) {
            $con = $this->getConditions();
            $test = $this->getConditions()->setConditions([]);
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }

        return $this;
    }


    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into \DateTime.
     *
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (($key === 'conditions') && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                $this->setData($key, $value);
            }
        }

        return $arr;
    }

    /**
     * Get actions field set id.
     *
     * @param string $formName
     * @return string
     * @since 100.1.0
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return Collection
     */
    public function getActionsInstance(){
        return null;
    }
}
