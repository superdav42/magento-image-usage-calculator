<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DevStone\UsageCalculator\Controller\Adminhtml\Order;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Product;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

class ConfigureProductToAdd extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param Product $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Context $context,
        Product $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        StoreManagerInterface $storeManager = null
    ) {
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * Ajax handler to response configuration fieldset of composite product in order
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        // Prepare data
        $productId = 89487; // TODO: fetch first from db

        $configureResult = new \Magento\Framework\DataObject();
        $configureResult->setOk(true);
        $configureResult->setProductId($productId);
        $sessionQuote = $this->_objectManager->get(\Magento\Backend\Model\Session\Quote::class);
        $configureResult->setCurrentStoreId($sessionQuote->getStore()->getId());
        $this->storeManager->setCurrentStore($sessionQuote->getStore()->getCode());
        $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());

        // Render page
        /** @var \Magento\Catalog\Helper\Product\Composite $helper */
        $helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class);
        $resultLayout = $helper->renderConfigureResult($configureResult);
        $resultLayout->addHandle('devstone_product_bulk_add');
        return $resultLayout;
    }
}
