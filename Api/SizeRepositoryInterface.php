<?php


namespace DevStone\UsageCalculator\Api;


interface SizeRepositoryInterface
{


    /**
     * Save Size
     * @param \DevStone\SizeCalculator\Api\Data\SizeInterface $size
     * @return \DevStone\SizeCalculator\Api\Data\SizeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \DevStone\UsageCalculator\Api\Data\SizeInterface $size
    );

    /**
     * Retrieve Size
     * @param string $sizeId
     * @return \DevStone\SizeCalculator\Api\Data\SizeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($sizeId);

    /**
     * Retrieve Size matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \DevStone\SizeCalculator\Api\Data\SizeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Size
     * @param \DevStone\SizeCalculator\Api\Data\SizeInterface $size
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \DevStone\UsageCalculator\Api\Data\SizeInterface $size
    );

    /**
     * Delete Size by ID
     * @param string $sizeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sizeId);
}
