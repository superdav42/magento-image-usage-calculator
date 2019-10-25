<?php


namespace DevStone\UsageCalculator\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use DevStone\UsageCalculator\Api\Data\UsageSearchResultsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use DevStone\UsageCalculator\Model\ResourceModel\Usage as ResourceUsage;
use DevStone\UsageCalculator\Model\ResourceModel\Usage\CollectionFactory as UsageCollectionFactory;
use Magento\Framework\Api\SortOrder;
use DevStone\UsageCalculator\Api\UsageRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class UsageRepository
 * @package DevStone\UsageCalculator\Model
 */
class UsageRepository implements UsageRepositoryInterface
{

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var UsageFactory
     */
    protected $usageFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var UsageCollectionFactory
     */
    protected $usageCollectionFactory;

    /**
     * @var UsageSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceUsage
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;


    /**
     * @param ResourceUsage $resource
     * @param UsageFactory $usageFactory
     * @param UsageCollectionFactory $usageCollectionFactory
     * @param UsageSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceUsage $resource,
        UsageFactory $usageFactory,
        UsageCollectionFactory $usageCollectionFactory,
        UsageSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->usageFactory = $usageFactory;
        $this->usageCollectionFactory = $usageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \DevStone\UsageCalculator\Api\Data\UsageInterface $usage
    ) {
        /* if (empty($usage->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $usage->setStoreId($storeId);
        } */
        try {
            $usage->getResource()->save($usage);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the usage: %1',
                $exception->getMessage()
            ));
        }
        return $usage;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($usageId)
    {
//        $usage = $this->usageFactory->create();
//        $usage->setData('store_id', 5);
//
//        $usage->getResource()->load($usage, $usageId);

        $collection = $this->usageCollectionFactory->create();

        $collection->addAttributeToSelect('*');

//        $collection->addStoreFilter($this->storeManager->getStore()->getId(), false);

        $collection->addFieldToFilter('entity_id', $usageId);

        $items = $collection->getItems();
        $usage = current($items);
        $usage->afterLoad();
        if (!$usage->getId()) {
            throw new NoSuchEntityException(__('Usage with id "%1" does not exist.', $usageId));
        }
        return $usage;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->usageCollectionFactory->create();

        $collection->addAttributeToSelect('*');

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
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \DevStone\UsageCalculator\Api\Data\UsageInterface $usage
    ) {
        try {
            $usage->getResource()->delete($usage);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Usage: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($usageId)
    {
        return $this->delete($this->getById($usageId));
    }
}
