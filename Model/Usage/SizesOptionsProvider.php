<?php
namespace DevStone\UsageCalculator\Model\Usage;

class SizesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \DevStone\UsageCalculator\Api\SizeRepositoryInterface
     */
    private $sizeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    private $objectConverter;

    /**
     * @param \DevStone\UsageCalculator\Api\SizeRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     */
    public function __construct(
        \DevStone\UsageCalculator\Api\SizeRepositoryInterface $sizeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter
    ) {
        $this->sizeRepository = $sizeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
    }

    /**
     * @return array
     */
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
