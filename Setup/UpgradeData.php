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

    protected $categoryFactory;

    /**
     * UpgradeData constructor.
     * @param UsageSetupFactory $usageSetupFactory
     * @param \DevStone\UsageCalculator\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        UsageSetupFactory $usageSetupFactory,
        \DevStone\UsageCalculator\Model\CategoryFactory $categoryFactory
    ) {
        $this->usageSetupFactory = $usageSetupFactory;
        $this->categoryFactory = $categoryFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var UsageSetup $usageSetup */
        $usageSetup = $this->usageSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $category = $this->categoryFactory->create();
            $category
                ->setName('Custom License')
                ->setTerms('Customer License Terms')
                ->save();
        }

        $usageSetup->installEntities();
        $entities = $usageSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $usageSetup->addEntityType($entityName, $entity);
        }

        $setup->endSetup();
    }
}
