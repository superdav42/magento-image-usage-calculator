<?php
/**
 * Collection.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */
namespace DevStone\UsageCalculator\Model\ResourceModel\Size;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\DevStone\UsageCalculator\Model\Size::class, \DevStone\UsageCalculator\Model\ResourceModel\Size::class);
    }
}
