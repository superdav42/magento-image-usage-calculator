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
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class Delete
 * @package DevStone\UsageCalculator\Controller\Adminhtml\Usage
 */
class Delete extends Action
{
    /** @var usageFactory $objectFactory */
    protected $objectFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory
     */
    protected $usageCollectionFactory;



    /**
     * @param Context $context
     * @param UsageFactory $objectFactory
     */
    public function __construct(
        Context $context,
        UsageFactory $objectFactory,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->filter = $filter;
        $this->usageCollectionFactory = $collectionFactory;
        $this->objectFactory = $objectFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('DevStone_UsageCalculator::usage');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id', null);

        try {
            $objectInstance = $this->objectFactory->create()->load($id);
            if ($objectInstance->getId()) {
                $objectInstance->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
            } else {
                $this->messageManager->addErrorMessage(__('Record does not exist.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        $this->deleteUsageCustomer();
        return $resultRedirect->setPath('*/*');
    }

    public function deleteUsageCustomer()
    {
        $collection = $this->usageCollectionFactory->create();

        $id = $this->getRequest()->getParam('entity_id', null);
        $collection->addFieldToFilter('usage_id', ['eq' => $id]);

        foreach ($collection as $usage) {
            $usage->delete();
        }
    }
}
