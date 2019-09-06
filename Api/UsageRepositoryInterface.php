<?php


namespace DevStone\UsageCalculator\Api;


interface UsageRepositoryInterface
{


    /**
     * Save Usage
     * @param \DevStone\UsageCalculator\Api\Data\UsageInterface $usage
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \DevStone\UsageCalculator\Api\Data\UsageInterface $usage
    );

    /**
     * Retrieve Usage
     * @param string $usageId
     * @return \DevStone\UsageCalculator\Api\Data\UsageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($usageId);

    /**
     * Retrieve Usage matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \DevStone\UsageCalculator\Api\Data\UsageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Usage
     * @param \DevStone\UsageCalculator\Api\Data\UsageInterface $usage
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \DevStone\UsageCalculator\Api\Data\UsageInterface $usage
    );

    /**
     * Delete Usage by ID
     * @param string $usageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($usageId);
}
