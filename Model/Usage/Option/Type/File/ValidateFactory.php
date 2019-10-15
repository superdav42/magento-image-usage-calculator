<?php
namespace DevStone\UsageCalculator\Model\Usage\Option\Type\File;

class ValidateFactory
{
    /**
     * @return \Zend_Validate
     */
    public function create()
    {
        return new \Zend_Validate();
    }
}
