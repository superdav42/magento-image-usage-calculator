<?php
namespace DevStone\UsageCalculator\Model\Usage;

class CatagoriesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{

    const CUSTOM_LICENSE = 'Custom License';

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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $catagories = $this->categoryRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();

        // Remove the Custom License Option
        $newCategories = [];
        foreach ($catagories as $catagory) {
            if ($catagory->getName() != self::CUSTOM_LICENSE) {
                $newCategories[] = $catagory;
            }
        }
        return $this->objectConverter->toOptionArray($newCategories, 'entity_id', 'name');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function customToOptionArray()
    {

        $catagories = $this->categoryRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();

        // Get the Custom License Option
        $newCategories = [];
        foreach ($catagories as $catagory) {
            if ($catagory->getName() == self::CUSTOM_LICENSE) {
                $newCategories[] = $catagory;
            }
        }
        return $this->objectConverter->toOptionArray($newCategories, 'entity_id', 'name');
    }
}
