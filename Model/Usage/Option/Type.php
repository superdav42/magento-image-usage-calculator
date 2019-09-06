<?php

namespace DevStone\UsageCalculator\Model\Usage\Option;

/**
 * @codeCoverageIgnore
 */
class Type extends \Magento\Framework\Model\AbstractExtensibleModel implements
        \DevStone\UsageCalculator\Api\Data\UsageCustomOptionTypeInterface
{
    /**#@+
     * Constants
     */
    const KEY_LABEL = 'label';
    const KEY_CODE = 'code';
    const KEY_GROUP = 'group';
    /**#@-*/

    /**
     * Get option type label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getData(self::KEY_LABEL);
    }

    /**
     * Get option type code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * Get option type group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->getData(self::KEY_GROUP);
    }

    /**
     * Set option type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    /**
     * Set option type code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set option type group
     *
     * @param string $group
     * @return $this
     */
    public function setGroup($group)
    {
        return $this->setData(self::KEY_GROUP, $group);
    }

    /**
     * {@inheritdoc}
     *
     * @return \DevStone\UsageCalculator\Api\Data\UsageCustomOptionTypeExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \DevStone\UsageCalculator\Api\Data\UsageCustomOptionTypeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
