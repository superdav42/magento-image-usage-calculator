<?php

namespace DevStone\UsageCalculator\Model\Usage\Option\Validator;

use DevStone\UsageCalculator\Model\Usage\Option;

class Text extends DefaultValidator
{
    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     */
    protected function validateOptionValue(Option $option)
    {
        return parent::validateOptionValue($option);
    }
}
