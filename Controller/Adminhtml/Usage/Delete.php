<?php
/**
 * Delete
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Controller\Adminhtml\Usage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use DevStone\UsageCalculator\Model\UsageFactory;

/**
 * Class Delete
 * @package DevStone\UsageCalculator\Controller\Adminhtml\Usage
 */
class Delete extends Action
{
    /** @var usageFactory $objectFactory */
    protected $objectFactory;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory
     */
    protected $usageCollectionFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param UsageFactory $objectFactory
     * @param \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        UsageFactory $objectFactory,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
    ) {
        $this->usageCollectionFactory = $collectionFactory;
        $this->objectFactory = $objectFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('DevStone_UsageCalculator::usage');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    #[\Override]
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id', null);

        try {
            $objectInstance = $this->objectFactory->create()->load($id);
            if ($objectInstance->getId()) {
                $this->deleteUsageCustomer((int)$id);
                $objectInstance->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
            } else {
                $this->messageManager->addErrorMessage(__('Record does not exist.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $resultRedirect->setPath('*/*');
    }

    /**
     * Delete custom-license customer assignments for a usage.
     *
     * @param int|null $usageId Usage entity ID; defaults to the request value.
     */
    public function deleteUsageCustomer(?int $usageId = null)
    {
        $collection = $this->usageCollectionFactory->create();

        $usageId ??= (int)$this->getRequest()->getParam('entity_id');
        $collection->addFieldToFilter('usage_id', ['eq' => $usageId]);

        foreach ($collection as $usage) {
            $usage->delete();
        }
    }
}
