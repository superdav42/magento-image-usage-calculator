<?php

namespace DevStone\UsageCalculator\Controller\Usage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Ajax extends Action implements HttpGetActionInterface
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        private readonly \Magento\Framework\Registry $coreRegistry,
        private readonly \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    ) {
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $product = $this->loadProduct($productId);

        if (! $product) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        // Prevent caching for all page cache systems (varnish, fastly, built-in)
        $result->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        
        return $result;
    }


    /**
     * Load product model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     */
    protected function loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        try {
            $product = $this->productRepository->getById($productId);

            if (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
                throw new NoSuchEntityException();
            }
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }

        $this->coreRegistry->register('current_product', $product);
        $this->coreRegistry->register('product', $product);

        return $product;
    }
}
