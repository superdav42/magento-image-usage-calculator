<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DevStone\UsageCalculator\Controller\Adminhtml\Order;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Product;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

class SkusToIds extends \Magento\Backend\App\Action
{

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        private readonly JsonFactory $resultJsonFactory,
        private readonly ProductCollection $productCollection,
        private readonly \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        private readonly FilterGroupBuilder $filterGroupBuilder,
        private readonly FilterBuilder $filterBuilder,
    ) {
        parent::__construct($context);
    }

    /**
     * Ajax handler to response configuration fieldset of composite product in order
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $filterGroup = $this->filterGroupBuilder->create();
        $skus = strtolower($this->getRequest()->getParam('skus'));

        $skus_array = preg_split('/(\s+|,)/i', $skus);

        $counts = array_count_values($skus_array);
        $filter = $this->filterBuilder->create();
        $filter->setField('sku')
               ->setValue(array_keys($counts))
               ->setConditionType('in');
        $filterGroup->setFilters([$filter]);
        $searchCriteria->setFilterGroups([$filterGroup]);
        $searchCriteria->setPageSize(5000);
        $products = $this->productRepository->getList($searchCriteria)->getItems();
        $ids = [];

        foreach ($products as $product) {
            $skuKey = strtolower($product->getSku());
            for ($i = 0; $i < $counts[$skuKey]; $i++) {
                $ids[] = $product->getId();
            }
            unset($counts[$skuKey]);
        }
        $response = ['ids' => $ids];

        if (count($counts) > 0) {
            $response['warning'] = "could not find the skus ". implode(', ', array_keys($counts));
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}
