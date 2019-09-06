<?php

namespace DevStone\UsageCalculator\Block\Usage\View\Options\Type;

/**
 * Product options text type block
 *
 * @api
 */
class Text extends \DevStone\UsageCalculator\Block\Usage\View\Options\AbstractOptions
{
    /**
     * Returns default value to show in text input
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
    }
}
