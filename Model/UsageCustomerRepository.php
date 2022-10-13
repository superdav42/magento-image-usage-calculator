<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DevStone\UsageCalculator\Model;

use DevStone\UsageCalculator\Api\Data\UsageCustomerInterface;
use DevStone\UsageCalculator\Api\Data\UsageCustomerInterfaceFactory;
use DevStone\UsageCalculator\Api\Data\UsageCustomerSearchResultsInterface;
use DevStone\UsageCalculator\Api\Data\UsageCustomerSearchResultsInterfaceFactory;
use DevStone\UsageCalculator\Api\UsageCustomerRepositoryInterface;
use DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer as ResourceUsageCustomer;
use DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory as UsageCustomerCollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class UsageCustomerRepository implements UsageCustomerRepositoryInterface
{
    protected ResourceUsageCustomer $resource;
    protected UsageCustomerInterfaceFactory $usageCustomerFactory;
    protected UsageCustomerCollectionFactory $usageCustomerCollectionFactory;
    protected UsageCustomerSearchResultsInterfaceFactory $searchResultsFactory;
    protected CollectionProcessorInterface $collectionProcessor;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        ResourceUsageCustomer $resource,
        UsageCustomerInterfaceFactory $usageCustomerFactory,
        UsageCustomerCollectionFactory $usageCustomerCollectionFactory,
        UsageCustomerSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->usageCustomerFactory = $usageCustomerFactory;
        $this->usageCustomerCollectionFactory = $usageCustomerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function save(UsageCustomerInterface $usageCustomer): UsageCustomerInterface
    {
        try {
            $this->resource->save($usageCustomer);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the usageCustomer: %1',
                $exception->getMessage()
            ));
        }
        return $usageCustomer;
    }

    /**
     * @inheritDoc
     */
    public function get($usageCustomerId): UsageCustomerInterface
    {
        $usageCustomer = $this->usageCustomerFactory->create();
        $this->resource->load($usageCustomer, $usageCustomerId);
        if (!$usageCustomer->getId()) {
            throw new NoSuchEntityException(__('UsageCustomer with id "%1" does not exist.', $usageCustomerId));
        }
        return $usageCustomer;
    }

    /**
     * @inheritDoc
     */
    public function getByUsageAndCustomer($usageId, $customerId): ?UsageCustomerInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('usage_id', $usageId)
            ->addFilter('customer_id', $customerId)
            ->create();
        $items = $this->getList($searchCriteria)->getItems();

        if (sizeof($items) > 0) {
            return $items[0];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getByUsageAndEmail($usageId, $email): ?UsageCustomerInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('usage_id', $usageId)
            ->addFilter('pending_customer_email', $email)
            ->create();
        $items = $this->getList($searchCriteria)->getItems();

        if (sizeof($items) > 0) {
            return $items[0];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->usageCustomerCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(UsageCustomerInterface $usageCustomer): bool
    {
        try {
            $usageCustomerModel = $this->usageCustomerFactory->create();
            $this->resource->load($usageCustomerModel, $usageCustomer->getId());
            $this->resource->delete($usageCustomerModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the UsageCustomer: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }



    /**
     * @inheritDoc
     */
    public function deleteList(SearchCriteriaInterface $searchCriteria): bool
    {
        $collection = $this->usageCustomerCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->walk('delete');

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($usageCustomerId): bool
    {
        return $this->delete($this->get($usageCustomerId));
    }
}
