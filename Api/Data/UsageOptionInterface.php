<?php

namespace DevStone\UsageCalculator\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Usage option interface
 * @api
 */
interface UsageOptionInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes
    );
}
