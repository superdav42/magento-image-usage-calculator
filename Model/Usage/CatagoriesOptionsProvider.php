<?php

namespace DevStone\UsageCalculator\Model\Usage;

/**
 * Class CatagoriesOptionsProvider
 * @package DevStone\UsageCalculator\Model\Usage
 */
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CatagoriesOptionsProvider constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \DevStone\UsageCalculator\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \DevStone\UsageCalculator\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function allOptionsExcludingCustomLicense()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $this->getCustomLicenseId(), 'neq')
            ->create();
        $catagories = $this->categoryRepository->getList($searchCriteria)->getItems();
        return $this->objectConverter->toOptionArray($catagories, 'entity_id', 'name');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function customLicenseOption()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $this->getCustomLicenseId(), 'eq')
            ->create();
        $catagories = $this->categoryRepository->getList($searchCriteria)->getItems();
        return $this->objectConverter->toOptionArray($catagories, 'entity_id', 'name');
    }

    /**
     * Return array of options as value-label pair
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $catagories = $this->categoryRepository->getList($searchCriteria)->getItems();
        return $this->objectConverter->toOptionArray($catagories, 'entity_id', 'name');
    }

    /**
     * @return mixed
     */
    public function getCustomLicenseId()
    {
        return $this->scopeConfig->getValue(
            'usage_cal/general/category_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
