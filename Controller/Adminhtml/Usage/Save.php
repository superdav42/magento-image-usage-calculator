<?php
/**
 * Save
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Controller\Adminhtml\Usage;

use DevStone\UsageCalculator\Helper\Data;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use DevStone\UsageCalculator\Model\UsageFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Save
 * @package DevStone\UsageCalculator\Controller\Adminhtml\Usage
 */
class Save extends Action
{
    protected UsageFactory $objectFactory;
    protected Initialization\Helper $helper;
    protected Data $data;

    /**
     * @param Context $context
     * @param UsageFactory $objectFactory
     */
    public function __construct(
        Context $context,
        UsageFactory $objectFactory,
        Initialization\Helper $helper,
        Data $data
    ) {
        $this->objectFactory = $objectFactory;
        $this->helper = $helper;
        $this->data = $data;
        parent::__construct($context);

    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('DevStone_UsageCalculator::usage');
    }

    public function execute(): ResultInterface
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $data = $this->getRequest()->getParams();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $params = [];
            $objectInstance = $this->objectFactory->create();

            $objectInstance->setStoreId($storeId);
            $params['store'] = $storeId;
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            } else {
                $objectInstance->load($data['entity_id']);
                $params['entity_id'] = $data['entity_id'];
            }
            $objectInstance->addData($data);
            $objectInstance = $this->helper->initialize($objectInstance);

            try {
                $usage = $objectInstance->save();
                $this->_eventManager->dispatch(
                    'devstone_usagecalculator_usage_prepare_save',
                    ['object' => $this->objectFactory, 'request' => $this->getRequest(), 'usage' => $usage]
                );

                $this->messageManager->addSuccessMessage(__('You saved this record.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $params['entity_id'] = $objectInstance->getId();
                    $params['_current'] = true;
                    $customLicenseId = $this->data->getCustomLicenseId();
                    if ($objectInstance->getCategoryId() == $customLicenseId) {
                        $params['custom_license'] = true;
                    }
                    return $resultRedirect->setPath('*/*/edit', $params);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the record.'));
            }

            $this->_getSession()->setFormData($this->getRequest()->getPostValue());
            return $resultRedirect->setPath('*/*/edit', $params);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
