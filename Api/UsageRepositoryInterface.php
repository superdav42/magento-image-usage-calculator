<?php


namespace DevStone\UsageCalculator\Api;


use DevStone\UsageCalculator\Api\Data\UsageInterface;
use DevStone\UsageCalculator\Api\Data\UsageSearchResultsInterface;
use DevStone\UsageCalculator\Model\Usage;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface UsageRepositoryInterface
{


    /**
     * Save Usage
     * @param UsageInterface $usage
     * @return UsageInterface
     * @throws LocalizedException
     */
    public function save(
        UsageInterface $usage
    );

    /**
     * Retrieve Usage
     * @param int $usageId
     * @return UsageInterface
     * @throws LocalizedException
     */
    public function getById(int $usageId): UsageInterface;

    /**
     * Retrieve Usage matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return UsageSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Usage
     * @param UsageInterface $usage
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        UsageInterface $usage
    );

    /**
     * Delete Usage by ID
     * @param string $usageId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($usageId);
}
