<?php
/**
 * installSchema.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */
namespace DevStone\UsageCalculator\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use DevStone\UsageCalculator\Setup\EavTablesSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var EavTablesSetupFactory
     */
    protected $eavTablesSetupFactory;

    /**
     * Init
     *
     * @internal param EavTablesSetupFactory $EavTablesSetupFactory
     */
    public function __construct(EavTablesSetupFactory $eavTablesSetupFactory)
    {
        $this->eavTablesSetupFactory = $eavTablesSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) //@codingStandardsIgnoreLine
    {
        $setup->startSetup();
        
        /** @var \DevStone\UsageCalculator\Setup\EavTablesSetup $eavTablesSetup */
        $eavTablesSetup = $this->eavTablesSetupFactory->create(['setup' => $setup]);
        
        $categoryTableName = 'devstone_usage_category';

        $categoryTable = $setup->getConnection()
            ->newTable($setup->getTable($categoryTableName))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false, 'default' => ''],
            'Category Name'
        )->addColumn(
            'terms',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10000,
            ['nullable' => false, 'default' => ''],
            'Category Terms'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Update Time'
        )->setComment('Usage Category Entity Table');
        
        $setup->getConnection()->createTable($categoryTable);
                
        $sizeTableName = 'devstone_downloadable_image_size';

        $sizeTable = $setup->getConnection()
            ->newTable($setup->getTable($sizeTableName))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Active'
        )->addColumn(
            'max_width',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'maximum width in pixels (0 unlimited)'
        )->addColumn(
            'max_height',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'maximum width in pixels (0 unlimited)'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false, 'default' => ''],
            'code to identify size'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Update Time'
        )->setComment('Downloadable image sizes');
        
        $setup->getConnection()->createTable($sizeTable);

        $tableName = UsageSetup::ENTITY_TYPE_CODE . '_entity';
        
        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->setComment('Usage Entity Table');
        $table->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Usage Category Id'
        )->addIndex(
            $setup->getIdxName($tableName, ['category_id']),
            ['category_id']
        )->addForeignKey(
            $setup->getFkName($tableName, 'category_id', $categoryTableName, 'entity_id'),
            'category_id',
            $setup->getTable($categoryTableName),
            'entity_id'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Active'
        );
        
        $table->addColumn(
            'size_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Usage Size Id'
        )->addIndex(
            $setup->getIdxName($tableName, ['size_id']),
            ['size_id']
        )->addForeignKey(
            $setup->getFkName($tableName, 'size_id', $sizeTableName, 'entity_id'),
            'size_id',
            $setup->getTable($sizeTableName),
            'entity_id'
        );

        $table->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Update Time'
        );

        $setup->getConnection()->createTable($table);

        $eavTablesSetup->createEavTables(UsageSetup::ENTITY_TYPE_CODE);
        

        /**
         * Create table 'devstone_usage_option'
         */
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option')
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option ID'
            )
            ->addColumn(
                'usage_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Usage ID'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => true, 'default' => null],
                'Type'
            )
            ->addColumn(
                'is_require',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Is Required'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addIndex(
                $setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option', ['usage_id']),
                ['usage_id']
            )
            ->addForeignKey(
                $setup->getFkName(UsageSetup::ENTITY_TYPE_CODE.'_option', 'usage_id', UsageSetup::ENTITY_TYPE_CODE.'_entity', 'entity_id'),
                'usage_id',
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Usage Option Table'
            );
        $setup->getConnection()
            ->createTable($table);

        /**
         * Create table 'devstone_usage_option_price'
         */
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_price')
            )
            ->addColumn(
                'option_price_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Price ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Price'
            )
            ->addColumn(
                'price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                7,
                ['nullable' => false, 'default' => 'fixed'],
                'Price Type'
            )
            ->addIndex(
                $setup->getIdxName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_price',
                    ['option_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option_price', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_price',
                    'option_id',
                    UsageSetup::ENTITY_TYPE_CODE.'_option',
                    'option_id'
                ),
                'option_id',
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(UsageSetup::ENTITY_TYPE_CODE.'_option_price', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Usage Option Price Table'
            );
        $setup->getConnection()
            ->createTable($table);

        /**
         * Create table 'devstone_usage_option_title'
         */
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_title')
            )
            ->addColumn(
                'option_title_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Title ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Title'
            )
            ->addIndex(
                $setup->getIdxName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_title',
                    ['option_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option_title', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_title',
                    'option_id',
                    UsageSetup::ENTITY_TYPE_CODE.'_option',
                    'option_id'
                ),
                'option_id',
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(UsageSetup::ENTITY_TYPE_CODE.'_option_title', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Usage Option Title Table'
            );
        $setup->getConnection()
            ->createTable($table);
		
		/**
         * Create table 'devstone_usage_option_help'
         */
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_help')
            )
            ->addColumn(
                'option_help_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Title ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'help',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                1024,
                ['nullable' => true, 'default' => null],
                'Help Text'
            )
            ->addIndex(
                $setup->getIdxName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_help',
                    ['option_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option_title', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_help',
                    'option_id',
                    UsageSetup::ENTITY_TYPE_CODE.'_option',
                    'option_id'
                ),
                'option_id',
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(UsageSetup::ENTITY_TYPE_CODE.'_option_help', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Usage Option Help Text Table'
            );
        $setup->getConnection()
            ->createTable($table);
		

        /**
         * Create table 'devstone_usage_option_type_value'
         */
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_type_value')
            )
            ->addColumn(
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Type ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option ID'
            )
            ->addColumn(
				'size_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => true, 'default' => 'NULL'],
				'Usage Option Size Id'
			)->addIndex(
				$setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option_type_value', ['size_id']),
				['size_id']
			)->addForeignKey(
				$setup->getFkName(UsageSetup::ENTITY_TYPE_CODE.'_option_type_value', 'size_id', $sizeTableName, 'entity_id'),
				'size_id',
				$setup->getTable($sizeTableName),
				'entity_id'
			)
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addIndex(
                $setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option_type_value', ['option_id']),
                ['option_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_type_value',
                    'option_id',
                    UsageSetup::ENTITY_TYPE_CODE.'_option',
                    'option_id'
                ),
                'option_id',
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Usage Option Type Value Table'
            );
        $setup->getConnection()
            ->createTable($table);

        /**
         * Create table 'devstone_usage_option_type_price'
         */
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_type_price')
            )
            ->addColumn(
                'option_type_price_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Type Price ID'
            )
            ->addColumn(
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option Type ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Price'
            )
            ->addColumn(
                'price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                7,
                ['nullable' => false, 'default' => 'fixed'],
                'Price Type'
            )
            ->addIndex(
                $setup->getIdxName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_type_price',
                    ['option_type_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_type_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option_type_price', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_type_price',
                    'option_type_id',
                    UsageSetup::ENTITY_TYPE_CODE.'_option_type_value',
                    'option_type_id'
                ),
                'option_type_id',
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_type_value'),
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(UsageSetup::ENTITY_TYPE_CODE.'_option_type_price', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Usage Option Type Price Table'
            );
        $setup->getConnection()
            ->createTable($table);

        /**
         * Create table 'devstone_usage_option_type_title'
         */
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_type_title')
            )
            ->addColumn(
                'option_type_title_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Type Title ID'
            )
            ->addColumn(
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option Type ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Title'
            )
            ->addIndex(
                $setup->getIdxName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_type_title',
                    ['option_type_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_type_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(UsageSetup::ENTITY_TYPE_CODE.'_option_type_title', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    UsageSetup::ENTITY_TYPE_CODE.'_option_type_title',
                    'option_type_id',
                    UsageSetup::ENTITY_TYPE_CODE.'_option_type_value',
                    'option_type_id'
                ),
                'option_type_id',
                $setup->getTable(UsageSetup::ENTITY_TYPE_CODE.'_option_type_value'),
                'option_type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(UsageSetup::ENTITY_TYPE_CODE.'_option_type_title', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment(
                'Usage Option Type Title Table'
            );
        $setup->getConnection()
            ->createTable($table);
        
        $setup->endSetup();
    }
}
