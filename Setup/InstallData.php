<?php
/**
 * InstallData
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Usage setup factory
     *
     * @var UsageSetupFactory
     */
    protected $usageSetupFactory;
    

    /**
     * Init
     *
     * @param UsageSetupFactory $usageSetupFactory
     */
    public function __construct(
        UsageSetupFactory $usageSetupFactory
    ) {
        $this->usageSetupFactory = $usageSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) //@codingStandardsIgnoreLine
    {
        /** @var UsageSetup $usageSetup */
        $usageSetup = $this->usageSetupFactory->create(['setup' => $setup]);
        
        $setup->startSetup();

        $usageSetup->installEntities();
        $entities = $usageSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $usageSetup->addEntityType($entityName, $entity);
        }
       
        $setup->endSetup();
    }
}
