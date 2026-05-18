<?php

namespace DevStone\UsageCalculator\Model\Usage;

class SizesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @param \DevStone\UsageCalculator\Api\SizeRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     */
    public function __construct(private readonly \DevStone\UsageCalculator\Api\SizeRepositoryInterface $sizeRepository, private readonly \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, private readonly \Magento\Framework\Convert\DataObject $objectConverter)
    {
    }

    /**
     * @return array
     */
    #[\Override]
    public function toOptionArray($placeholder = false)
    {
        $sizes = $this->sizeRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();

        $optionArray = $this->objectConverter->toOptionArray($sizes, 'entity_id', 'code');

        if ($placeholder) {
            array_unshift($optionArray, ['value' => '', 'label' => $placeholder]);
        }

        return $optionArray;
    }
}
