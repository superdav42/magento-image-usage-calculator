<?php


namespace DevStone\UsageCalculator\Api;


interface CategoryRepositoryInterface
{


    /**
     * Save Category
     * @param \DevStone\UsageCalculator\Api\Data\CategoryInterface $usage
     * @return \DevStone\UsageCalculator\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \DevStone\UsageCalculator\Api\Data\CategoryInterface $usage
    );

    /**
     * Retrieve Category
     * @param string $usageId
     * @return \DevStone\UsageCalculator\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($usageId);

    /**
     * Retrieve Category matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \DevStone\UsageCalculator\Api\Data\CategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Category
     * @param \DevStone\UsageCalculator\Api\Data\CategoryInterface $usage
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \DevStone\UsageCalculator\Api\Data\CategoryInterface $usage
    );

    /**
     * Delete Category by ID
     * @param string $usageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($usageId);
}
