<?php
namespace DevStone\UsageCalculator\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
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

    public function  upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
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
