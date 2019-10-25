<?php

namespace DevStone\UsageCalculator\Observer;

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
     * SaveCustomer constructor.
     * @param \DevStone\UsageCalculator\Model\UsageCustomerFactory $usageCustomerFactory
     * @param \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
     */
    public function __construct(
        \DevStone\UsageCalculator\Model\UsageCustomerFactory $usageCustomerFactory,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
    ) {
        $this->usageCustomerFactory = $usageCustomerFactory;
        $this->usageCustomerCollectionFactory = $collectionFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getData('request');
        $customers = $request->getParam('usage_customers');
        $usageId = $observer->getData('usage')->getId();
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
}
