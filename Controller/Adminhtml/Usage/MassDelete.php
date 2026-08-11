<?php
/**
 * MassDelete.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Controller\Adminhtml\Usage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use DevStone\UsageCalculator\Model\ResourceModel\Usage\Collection;

/**
 * Class MassDelete
 */
class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /** @var Collection $objectCollection */
    protected $objectCollection;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory
     */
    protected $usageCollectionFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param Collection $objectCollection
     * @param \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Collection $objectCollection,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->objectCollection = $objectCollection;
        $this->usageCollectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    #[\Override]
    public function execute()
    {
        $collection = $this->filter->getCollection($this->objectCollection);
        $collectionSize = $collection->getSize();
        $usageIds = $collection->getAllIds();
        $this->deleteUsageCustomer($usageIds);
        $collection->walk('delete');
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Delete all the Custom License Usage
     *
     * @param int[] $usageIds Usage entity IDs selected for deletion.
     */
    public function deleteUsageCustomer(array $usageIds)
    {
        if ($usageIds === []) {
            return;
        }

        $collection = $this->usageCollectionFactory->create();
        $collection->addFieldToFilter('usage_id', ['in' => $usageIds]);
        foreach ($collection as $usage) {
            $usage->delete();
        }
    }
}
