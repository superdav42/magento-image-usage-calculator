<?php

namespace DevStone\UsageCalculator\Plugin\Block\Product;

class ListProduct
{

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {

        if (\DevStone\ImageProducts\Model\Product\Type::TYPE_ID !== $product->getTypeId()) {
            return $proceed($product);
        }

        $priceText = 'Calculate Price';

        return $priceText;
    }
}
