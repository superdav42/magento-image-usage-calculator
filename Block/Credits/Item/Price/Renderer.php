<?php

namespace DevStone\UsageCalculator\Block\Credits\Item\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\CreditMemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Weee\Model\Tax as WeeeDisplayConfig;

/**
 * Item price render block
 */
class Renderer extends \Magento\Weee\Block\Item\Price\Renderer
{

    public function formatPrice($price)
    {
        $productOptions = $this->item->getOptionByCode( 'required_credits' );

        if ($productOptions && is_numeric($productOptions->getValue())) {
            $price = (float) $productOptions->getValue();
            return '<span class="price">' . $price . ' Download Credit' . ($price > 1 ? 's' : '').'</span>';
        } elseif( 0 == $price ) {
            return '<span class="price">'.__('Free').'</span>';
        }
        return parent::formatPrice($price);
    }
}
