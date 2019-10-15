<?php

namespace DevStone\UsageCalculator\Model\Usage\Option;

use DevStone\UsageCalculator\Api\UsageCustomOptionRepositoryInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var UsageCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @param UsageCustomOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        UsageCustomOptionRepositoryInterface $optionRepository
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
        $options = [];
        /** @var $entity \DevStone\UsageCalculator\Api\Data\UsageInterface */
        foreach ($this->optionRepository->getUsageOptions($entity) as $option) {
            $option->setUsage($entity);
            $options[] = $option;
        }
        $entity->setOptions($options);
        return $entity;
    }
}
