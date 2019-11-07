<?php

namespace DevStone\UsageCalculator\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package DevStone\UsageCalculator\Setup
 */
class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            if (!$installer->tableExists(UsageSetup::ENTITY_TYPE_CODE . '_customer')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable(UsageSetup::ENTITY_TYPE_CODE . '_customer')
                )->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                    ->addColumn(
                        'usage_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Usage ID'
                    )
                    ->addColumn(
                        'customer_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Customer ID'
                    )
                    ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'Created At'
                    )->addColumn(
                        'updated_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                        'Updated At'
                    )->setComment('Devstone Custom License Customer Table');
                $installer->getConnection()->createTable($table);
            }
        }

        //Add Max Usage
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            if (!$installer->tableExists(UsageSetup::ENTITY_TYPE_CODE . '_limit')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable(UsageSetup::ENTITY_TYPE_CODE . '_limit')
                )->addColumn(
                    'usage_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => false,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,

                    ],
                    'Usage Id'
                )->addForeignKey(
                    $setup->getFkName(
                        UsageSetup::ENTITY_TYPE_CODE . '_limit',
                        'usage_id',
                        UsageSetup::ENTITY_TYPE_CODE . '_entity',
                        'entity_id'
                    ),
                    'usage_id',
                    $setup->getTable(UsageSetup::ENTITY_TYPE_CODE . '_entity'),
                    'entity_id'
                )->addColumn(
                    'max_usage',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true,
                        'unsigned' => true,
                    ],
                    'Max Usage'
                )->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                    ],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
                    ],
                    'Updated At'
                )->setComment('Devstone Max Usage Limit Table');
                $installer->getConnection()->createTable($table);
            }
        }
        $installer->endSetup();
    }
}
