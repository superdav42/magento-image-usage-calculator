<?php

namespace DevStone\UsageCalculator\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface UsageCustomOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Product text options group.
     */
    const OPTION_GROUP_TEXT = 'text';

    /**
     * Usage file options group.
     */
    const OPTION_GROUP_FILE = 'file';

    /**
     * Usage select options group.
     */
    const OPTION_GROUP_SELECT = 'select';

    /**
     * Usage date options group.
     */
    const OPTION_GROUP_DATE = 'date';

    /**
     * Usage field option type.
     */
    const OPTION_TYPE_FIELD = 'field';

    /**
     * Usage area option type.
     */
    const OPTION_TYPE_AREA = 'area';

    /**
     * Usage file option type.
     */
    const OPTION_TYPE_FILE = 'file';

    /**
     * Usage drop-down option type.
     */
    const OPTION_TYPE_DROP_DOWN = 'drop_down';

    /**
     * Usage radio option type.
     */
    const OPTION_TYPE_RADIO = 'radio';

    /**
     * Usage checkbox option type.
     */
    const OPTION_TYPE_CHECKBOX = 'checkbox';

    /**
     * Usage multiple option type.
     */
    const OPTION_TYPE_MULTIPLE = 'multiple';

    /**
     * Usage date option type.
     */
    const OPTION_TYPE_DATE = 'date';

    /**
     * Usage datetime option type.
     */
    const OPTION_TYPE_DATE_TIME = 'date_time';

    /**
     * Usage time option type.
     */
    const OPTION_TYPE_TIME = 'time';

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

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
     * Get option type
     *
     * @return string
     */
    public function getType();

    /**
     * Set option type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

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
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRequire();

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequire($isRequired);

    /**
     * Get price
     *
     * @return float|null
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
     * @return string|null
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
     * @param string $help
     * @return $this
     */
    public function setHelp($help);

    /**
     * @return string|null
     */
    public function getHelp();


    /**
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionValuesInterface[]|null
     */
    public function getValues();

    /**
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionValuesInterface[] $values
     * @return $this
     */
    public function setValues(array $values = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \DevStone\UsageCalculator\Api\Data\UsageCustomOptionExtensionInterface $extensionAttributes
    );
}
