<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DevStone\UsageCalculator\Api;

use DevStone\UsageCalculator\Api\Data\UsageCustomerInterface;
use DevStone\UsageCalculator\Api\Data\UsageCustomerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface UsageCustomerRepositoryInterface
{

    /**
     * Save UsageCustomer
     * @param UsageCustomerInterface $usageCustomer
     * @return UsageCustomerInterface
     * @throws LocalizedException
     */
    public function save(
        UsageCustomerInterface $usageCustomer
    ): UsageCustomerInterface;

    /**
     * Retrieve UsageCustomer
     * @param int $usageCustomerId
     * @return UsageCustomerInterface
     * @throws LocalizedException
     */
    public function get(int $usageCustomerId): UsageCustomerInterface;

    /**
     * Retrieve UsageCustomer
     * @param int $usageId
     * @param int $customerId
     * @return UsageCustomerInterface
     * @throws LocalizedException
     */
    public function getByUsageAndCustomer(int $usageId, int $customerId): ?UsageCustomerInterface;

    /**
     * Retrieve UsageCustomer
     * @param int $usageId
     * @param string $email
     * @return UsageCustomerInterface
     * @throws LocalizedException
     */
    public function getByUsageAndEmail(int $usageId, string $email): ?UsageCustomerInterface;

    /**
     * Retrieve UsageCustomer matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): SearchResultsInterface;

    /**
     * Delete UsageCustomer
     * @param UsageCustomerInterface $usageCustomer
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        UsageCustomerInterface $usageCustomer
    ): bool;

    /**
     * Delete UsageCustomers
     * @param SearchCriteriaInterface $searchCriteria
     * @return bool true on success
     * @throws LocalizedException
     */
    public function deleteList(
        SearchCriteriaInterface $searchCriteria
    ): bool;

    /**
     * Delete UsageCustomer by ID
     * @param int $usageCustomerId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $usageCustomerId): bool;
}

