<?php

/**
 * UsageActions.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class UsageActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'devstone_usagecalculator/usage/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \DevStone\UsageCalculator\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        //TODO: change the url
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    if ($item['category_id'] == $this->getCustomLicenseId()) {
                        $item[$this->getData('name')]['edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_EDIT,
                                ['entity_id' => $item['entity_id'], 'custom_license' => true, 'store' => $storeId]
                            ),
                            'label' => __('Edit'),
                            'hidden' => false,
                        ];
                    } else {
                        $item[$this->getData('name')]['edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::URL_PATH_EDIT,
                                ['entity_id' => $item['entity_id'], 'store' => $storeId]
                            ),
                            'label' => __('Edit'),
                            'hidden' => false,
                        ];

                    }
                }
            }
        }

        return $dataSource;
    }

    public function getCustomLicenseId()
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('name',
            ['eq' => \DevStone\UsageCalculator\Model\Usage\CatagoriesOptionsProvider::CUSTOM_LICENSE]);

        return $collection->getFirstItem()->getId();
    }

}
