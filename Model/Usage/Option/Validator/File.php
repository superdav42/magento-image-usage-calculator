<?php

namespace DevStone\UsageCalculator\Model\Usage\Option\Validator;

use DevStone\UsageCalculator\Model\Usage\Option;

class File extends DefaultValidator
{
    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     */
    protected function validateOptionValue(Option $option)
    {
        $result = parent::validateOptionValue($option);
        return $result && !$this->isNegative($option->getImageSizeX()) && !$this->isNegative($option->getImageSizeY());
    }
}
