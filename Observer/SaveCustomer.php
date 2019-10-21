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
     * SaveCustomer constructor.
     * @param \DevStone\UsageCalculator\Model\UsageCustomerFactory $usageCustomerFactory
     */
    public function __construct(
        \DevStone\UsageCalculator\Model\UsageCustomerFactory $usageCustomerFactory
    )
    {
        $this->usageCustomerFactory = $usageCustomerFactory;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $request  = $observer->getData('request');
        $customers = $request->getParam('usage_customers');
        $usageId= $observer->getData('usage')->getId();
        if(isset($customers)){
            $customersArray = json_decode($customers);
            foreach ($customersArray as $customerId){
                $usageCustomer = $this->usageCustomerFactory->create();
                $usageCustomers = $this->usageCustomerFactory->create()->getCollection()
                    ->addFieldToFilter('usage_id',['eq'=> $usageId])
                    ->addFieldToFilter('customer_id',['eq' => $customerId]);

                if(count($usageCustomers)){
                    $usageCustomer->setEntityId($usageCustomers->getFirstItem()->getEntityId());
                }
                $usageCustomer->setUsageId($observer->getData('usage')->getId());
                $usageCustomer->setCustomerId($customerId);
                $usageCustomer->save();
            }
        }
    }
}