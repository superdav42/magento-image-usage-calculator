<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DevStone\UsageCalculator\Model\Usage;

class CatagoriesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \DevStone\UsageCalculator\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    private $objectConverter;

    /**
     * @param \DevStone\UsageCalculator\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     */
    public function __construct(
        \DevStone\UsageCalculator\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $catagories = $this->categoryRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
        
        return $this->objectConverter->toOptionArray($catagories, 'entity_id', 'name');
    }
}
