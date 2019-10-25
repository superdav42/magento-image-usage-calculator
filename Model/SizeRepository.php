<?php


namespace DevStone\UsageCalculator\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use DevStone\UsageCalculator\Api\Data\SizeSearchResultsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use DevStone\UsageCalculator\Model\ResourceModel\Size as ResourceSize;
use DevStone\UsageCalculator\Model\ResourceModel\Size\CollectionFactory as SizeCollectionFactory;
use Magento\Framework\Api\SortOrder;
use DevStone\UsageCalculator\Api\SizeRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use DevStone\UsageCalculator\Api\Data\SizeInterfaceFactory;

/**
 * Class SizeRepository
 * @package DevStone\UsageCalculator\Model
 */
class SizeRepository implements SizeRepositoryInterface
{

    /**
     * @var SizeInterfaceFactory
     */
    protected $dataSizeFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var SizeFactory
     */
    protected $sizeFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var SizeCollectionFactory
     */
    protected $sizeCollectionFactory;

    /**
     * @var SizeSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceSize
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param ResourceSize $resource
     * @param SizeFactory $sizeFactory
     * @param SizeInterfaceFactory $dataSizeFactory
     * @param SizeCollectionFactory $sizeCollectionFactory
     * @param SizeSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceSize $resource,
        SizeFactory $sizeFactory,
        SizeInterfaceFactory $dataSizeFactory,
        SizeCollectionFactory $sizeCollectionFactory,
        SizeSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->sizeFactory = $sizeFactory;
        $this->sizeCollectionFactory = $sizeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSizeFactory = $dataSizeFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \DevStone\UsageCalculator\Api\Data\SizeInterface $size
    ) {
        /* if (empty($size->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $size->setStoreId($storeId);
        } */
        try {
            $size->getResource()->save($size);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the size: %1',
                $exception->getMessage()
            ));
        }
        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($sizeId)
    {
        $size = $this->sizeFactory->create();
        $size->getResource()->load($size, $sizeId);
        if (!$size->getId()) {
            throw new NoSuchEntityException(__('Size with id "%1" does not exist.', $sizeId));
        }
        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->sizeCollectionFactory->create();
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
        \DevStone\UsageCalculator\Api\Data\SizeInterface $size
    ) {
        try {
            $size->getResource()->delete($size);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Size: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($sizeId)
    {
        return $this->delete($this->getById($sizeId));
    }
}
