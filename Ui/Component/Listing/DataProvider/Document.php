<?php
/**
 * Document
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Ui\Component\Listing\DataProvider;

/**
 * Class Document
 * @package DevStone\UsageCalculator\Ui\Component\Listing\DataProvider
 */
class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }
}
