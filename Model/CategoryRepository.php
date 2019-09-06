<?php


namespace DevStone\UsageCalculator\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use DevStone\UsageCalculator\Api\Data\CategorySearchResultsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use DevStone\UsageCalculator\Model\ResourceModel\Category as ResourceCategory;
use DevStone\UsageCalculator\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Api\SortOrder;
use DevStone\UsageCalculator\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use DevStone\UsageCalculator\Api\Data\CategoryInterfaceFactory;
use DevStone\UsageCalculator\Api\Data\CategoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{

    protected $dataObjectProcessor;

    protected $categoryFactory;

    protected $dataObjectHelper;

    protected $categoryCollectionFactory;

    protected $searchResultsFactory;

    protected $resource;

    private $storeManager;
    /**
     * @param ResourceCategory $resource
     * @param CategoryFactory $categoryFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategorySearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceCategory $resource,
        CategoryFactory $categoryFactory,
        CategoryInterfaceFactory $dataSizeFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategorySearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        CategoryInterface $category
    ) {
        /* if (empty($category->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $category->setStoreId($storeId);
        } */
        try {
            $category->getResource()->save($category);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the category: %1',
                $exception->getMessage()
            ));
        }
        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($categoryId)
    {
        $category = $this->categoryFactory->create();
        $category->getResource()->load($category, $categoryId);
        if (!$category->getId()) {
            throw new NoSuchEntityException(__('Category with id "%1" does not exist.', $categoryId));
        }
        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->categoryCollectionFactory->create();
        
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
        
//        
//        $searchResults = $this->searchResultsFactory->create();
//        $searchResults->setSearchCriteria($searchCriteria);
//        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
//        $collection = $this->customerFactory->create()->getCollection();
//        $this->extensionAttributesJoinProcessor->process(
//            $collection,
//            \Magento\Customer\Api\Data\CustomerInterface::class
//        );
//        // This is needed to make sure all the attributes are properly loaded
//        foreach ($this->customerMetadata->getAllAttributesMetadata() as $metadata) {
//            $collection->addAttributeToSelect($metadata->getAttributeCode());
//        }
//        // Needed to enable filtering on name as a whole
//        $collection->addNameToSelect();
//        // Needed to enable filtering based on billing address attributes
//        $collection->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
//            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
//            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
//            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
//            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
//            ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left');
//
//        $this->collectionProcessor->process($searchCriteria, $collection);
//
//        $searchResults->setTotalCount($collection->getSize());
//
//        $customers = [];
//        /** @var \Magento\Customer\Model\Customer $customerModel */
//        foreach ($collection as $customerModel) {
//            $customers[] = $customerModel->getDataModel();
//        }
//        $searchResults->setItems($customers);
//        return $searchResults;
        
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        CategoryInterface $category
    ) {
        try {
            $category->getResource()->delete($category);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Category: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($categoryId)
    {
        return $this->delete($this->getById($categoryId));
    }
}
