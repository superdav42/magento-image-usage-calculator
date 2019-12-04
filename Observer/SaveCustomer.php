<?php

namespace DevStone\UsageCalculator\Observer;

use Magento\Framework\App\Config;
use Magento\Framework\Event\Observer;

/**
 * Class SaveCustomer
 * @package DevStone\UsageCalculator\Observer
 */
class SaveCustomer implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \DevStone\UsageCalculator\Model\UsageCustomerFactory
     */
    protected $usageCustomerFactory;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory
     */
    protected $usageCustomerCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * SaveCustomer constructor.
     * @param Config\ScopeConfigInterface $config
     * @param \DevStone\UsageCalculator\Model\UsageCustomerFactory $usageCustomerFactory
     * @param \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \DevStone\UsageCalculator\Model\UsageCustomerFactory $usageCustomerFactory,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
    ) {
        $this->usageCustomerFactory = $usageCustomerFactory;
        $this->usageCustomerCollectionFactory = $collectionFactory;
        $this->scopeConfig = $config;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Framework\App\RequestInterface $request
         */
        $request = $observer->getData('request');
        $customers = $request->getParam('usage_customers');
        /**
         * @var \DevStone\UsageCalculator\Model\Usage $usage
         */
        $usage = $observer->getData('usage');
        $usageId = $usage->getEntityId();

        if (isset($customers)) {
            $customersArray = json_decode($customers);
            foreach ($customersArray as $customerId) {
                $usageCustomer = $this->usageCustomerFactory->create();

                $usageCustomers = $this->usageCustomerCollectionFactory->create()
                    ->addFieldToFilter('usage_id', ['eq' => $usageId])
                    ->addFieldToFilter('customer_id', ['eq' => $customerId]);

                //If usage already exsist then override its value
                if ($usageCustomers->getSize()) {
                    $usageCustomer->setEntityId($usageCustomers->getFirstItem()->getEntityId());
                }

                $usageCustomer->setUsageId($observer->getData('usage')->getId());
                $usageCustomer->setCustomerId($customerId);
                $usageCustomer->save();
            }
        }
    }

    /**
     * @return mixed
     */
    public function getCustomLicenseId()
    {
        return $this->scopeConfig->getValue(
            'usage_cal/general/category_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
