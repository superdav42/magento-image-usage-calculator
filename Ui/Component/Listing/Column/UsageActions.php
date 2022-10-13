<?php

/**
 * UsageActions.php
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Ui\Component\Listing\Column;

use DevStone\UsageCalculator\Helper\Data;
use DevStone\UsageCalculator\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class UsageActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'devstone_usagecalculator/usage/edit';
    protected UrlInterface $urlBuilder;
    protected CollectionFactory $collectionFactory;
    protected ScopeConfigInterface $scopeConfig;
    private Data $config;

    /**
     * UsageActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        Data $config,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->config = $config;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    if ($item['category_id'] == $this->config->getCustomLicenseId()) {
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
}
