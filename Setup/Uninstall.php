<?php

/**
 * Uninstall.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */
namespace DevStone\UsageCalculator\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @var array
     */
    protected $tablesToUninstall = [
        UsageSetup::ENTITY_TYPE_CODE . '_entity',
        UsageSetup::ENTITY_TYPE_CODE . '_eav_attribute',
        UsageSetup::ENTITY_TYPE_CODE . '_entity_datetime',
        UsageSetup::ENTITY_TYPE_CODE . '_entity_decimal',
        UsageSetup::ENTITY_TYPE_CODE . '_entity_int',
        UsageSetup::ENTITY_TYPE_CODE . '_entity_text',
        UsageSetup::ENTITY_TYPE_CODE . '_entity_varchar',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_entity',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_entity',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_eav_attribute',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_entity_datetime',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_entity_decimal',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_entity_int',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_entity_text',
        UsageCategorySetup::ENTITY_TYPE_CODE . '_entity_varchar',
        'devstone_downloadable_image_size',
    ];

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context) //@codingStandardsIgnoreLine
    {
        $setup->startSetup();

        foreach ($this->tablesToUninstall as $table) {
            if ($setup->tableExists($table)) {
                $setup->getConnection()->dropTable($setup->getTable($table));
            }
        }

        $setup->endSetup();
    }
}
