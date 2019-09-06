<?php

namespace DevStone\UsageCalculator\Model\Usage\Option;

use DevStone\UsageCalculator\Api\UsageCustomOptionRepositoryInterface as OptionRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $options = $entity->getOptions();

        $optionIds = [];

        if ($options) {
            $optionIds = array_map(function ($option) {
                /** @var \DevStone\UsageCalculatorModel\Usage\Option $option */
                return $option->getOptionId();
            }, $options);
        }

        /** @var \DevStone\UsageCalculator\Api\Data\UsageInterface $entity */
        foreach ($this->optionRepository->getUsageOptions($entity) as $option) {
            if (!in_array($option->getOptionId(), $optionIds)) {
                $this->optionRepository->delete($option);
            }
        }
        if ($options) {
            foreach ($options as $option) {
                $option->setUsageId($entity->getId());
                $this->optionRepository->save($option);
            }
        }

        return $entity;
    }
}
