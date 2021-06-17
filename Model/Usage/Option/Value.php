<?php

namespace DevStone\UsageCalculator\Model\Usage\Option;

use DevStone\UsageCalculator\Model\Usage;
use DevStone\UsageCalculator\Model\Usage\Option;
use Magento\Framework\Model\AbstractModel;

/**
 * Usage option select type model
 *
 * @api
 * @method int getOptionId()
 * @method \DevStone\UsageCalculator\Model\Usage\Option\Value setOptionId(int $value)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Value extends AbstractModel implements \DevStone\UsageCalculator\Api\Data\UsageCustomOptionValuesInterface
{
    /**
     * Option type percent
     */
    const TYPE_PERCENT = 'percent';

    /**#@+
     * Constants
     */
    const KEY_TITLE = 'title';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_PRICE = 'price';
    const KEY_PRICE_TYPE = 'price_type';
    const KEY_OPTION_TYPE_ID = 'option_type_id';
    /**#@-*/

    /**#@-*/
    protected $_values = [];

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Option
     */
    protected $_option;

    /**
     * Value collection factory
     *
     * @var \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\Value\CollectionFactory
     */
    protected $_valueCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\Value\CollectionFactory $valueCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_valueCollectionFactory = $valueCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\Value::class);
    }

    /**
     * @codeCoverageIgnoreStart
     * @param mixed $value
     * @return $this
     */
    public function addValue($value)
    {
        $this->_values[] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->_values = $values;
        return $this;
    }

    /**
     * @return $this
     */
    public function unsetValues()
    {
        $this->_values = [];
        return $this;
    }

    /**
     * @param Option $option
     * @return $this
     */
    public function setOption(Option $option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * @return $this
     */
    public function unsetOption()
    {
        $this->_option = null;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Option
     */
    public function getOption()
    {
        return $this->_option;
    }

    /**
     * @param Usage $usage
     * @return $this
     */
    public function setProduct(Usage $usage)
    {
        $this->_usage = $usage;
        return $this;
    }

    //@codeCoverageIgnoreEnd

    /**
     * @return Usage
     */
    public function getUsage()
    {
        if (is_null($this->_usage)) {
            $this->_usage = $this->getOption()->getUsage();
        }
        return $this->_usage;
    }

    /**
     * @return $this
     */
    public function saveValues()
    {
        foreach ($this->getValues() as $value) {
            $this->setData(
                $value
            )->setData(
                'option_id',
                $this->getOption()->getId()
            )->setData(
                'store_id',
                0,  //$this->getOption()->getStoreId() for some reason this is always null so it don't work. TODO fix if we want contry specific prices.
            );

            if ($this->getData('is_delete') == '1') {
                if ($this->getId()) {
                    $this->deleteValues($this->getId());
                    $this->delete();
                }
            } else {
                $this->save();
            }
        }
        //eof foreach()
        return $this;
    }

    /**
     * Return price. If $flag is true and price is percent
     *  return converted percent to price
     *
     * @param bool $flag
     * @return float|int
     */
    public function getPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == self::TYPE_PERCENT) {
            $basePrice = $this->getOption()->getUsage()->getPrice();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }

    /**
     * Return regular price.
     *
     * @return float|int
     */
    public function getRegularPrice()
    {
        if ($this->getPriceType() == self::TYPE_PERCENT) {
            $basePrice = $this->getOption()->getUsage()->getPrice();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }

    /**
     * Enter description here...
     *
     * @param Option $option
     * @return \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\Value\Collection
     */
    public function getValuesCollection(Option $option)
    {
        $collection = $this->_valueCollectionFactory->create()->addFieldToFilter(
            'option_id',
            $option->getId()
        )->getValues(
            $option->getStoreId()
        );

        return $collection;
    }

    /**
     * @param array $optionIds
     * @param int $option_id
     * @param int $store_id
     * @return \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\Value\Collection
     */
    public function getValuesByOption($optionIds, $option_id, $store_id)
    {
        $collection = $this->_valueCollectionFactory->create()->addFieldToFilter(
            'option_id',
            $option_id
        )->getValuesByOption(
            $optionIds,
            $store_id
        );

        return $collection;
    }

    /**
     * @param int $option_id
     * @return $this
     */
    public function deleteValue($option_id)
    {
        $this->getResource()->deleteValue($option_id);
        return $this;
    }

    /**
     * @param int $option_type_id
     * @return $this
     */
    public function deleteValues($option_type_id)
    {
        $this->getResource()->deleteValues($option_type_id);
        return $this;
    }

    /**
     * Duplicate usage options value
     *
     * @param int $oldOptionId
     * @param int $newOptionId
     * @return $this
     */
    public function duplicate($oldOptionId, $newOptionId)
    {
        $this->getResource()->duplicate($this, $oldOptionId, $newOptionId);
        return $this;
    }

    /**
     * Get option title
     *
     * @return string
     * @codeCoverageIgnoreStart
     */
    public function getTitle()
    {
        return $this->_getData(self::KEY_TITLE);
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_getData(self::KEY_SORT_ORDER);
    }

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType()
    {
        return $this->_getData(self::KEY_PRICE_TYPE);
    }



    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getOptionTypeId()
    {
        return $this->_getData(self::KEY_OPTION_TYPE_ID);
    }

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::KEY_PRICE_TYPE, $priceType);
    }



    /**
     * Set Option type id
     *
     * @param int $optionTypeId
     * @return int|null
     */
    public function setOptionTypeId($optionTypeId)
    {
        return $this->setData(self::KEY_OPTION_TYPE_ID, $optionTypeId);
    }

    //@codeCoverageIgnoreEnd
}
