<?php

namespace DevStone\UsageCalculator\Observer;

use DevStone\UsageCalculator\Api\UsageCustomerRepositoryInterface;
use DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory;
use DevStone\UsageCalculator\Model\Usage;
use DevStone\UsageCalculator\Model\UsageCustomerFactory;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class SaveCustomer
 * @package DevStone\UsageCalculator\Observer
 */
class SaveCustomer implements ObserverInterface
{
    public function __construct(protected ScopeConfigInterface $scopeConfig, protected UsageCustomerFactory $usageCustomerFactory, protected CollectionFactory $usageCustomerCollectionFactory, protected UsageCustomerRepositoryInterface $usageCustomerRepository, protected SearchCriteriaBuilder $searchCriteriaBuilder)
    {
    }

    /**
     * @param Observer $observer
     * @throws Exception
     */
    #[\Override]
    public function execute(Observer $observer)
    {
        /**
         * @var RequestInterface $request
         */
        $request = $observer->getData('request');
        $customers = $request->getParam('usage_customers');
        $pendingCustomers = $request->getParam('pending_customer_emails');
        /**
         * @var Usage $usage
         */
        $usage = $observer->getData('usage');
        $usageId = $usage->getEntityId();
        $savedIds = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('usage_id', $usage->getId())
            ->addFilter('customer_id', 0, 'neq');
        if (isset($customers)) {

            $customersArray = json_decode((string) $customers);
            foreach ($customersArray as $customerId) {
                $usageCustomer = $this->usageCustomerFactory->create();

                $usageCustomer = $this->usageCustomerRepository->getByUsageAndCustomer($usageId, $customerId) ?? $usageCustomer;

                $usageCustomer->setUsageId($usage->getId());
                $usageCustomer->setCustomerId((int)$customerId);
                $usageCustomer = $this->usageCustomerRepository->save($usageCustomer);
                $savedIds[] = $usageCustomer->getId();
            }
            $searchCriteria->addFilter('entity_id', $savedIds, 'nin');
        }
        $this->usageCustomerRepository->deleteList($searchCriteria->create());

        $savedIds = [];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('usage_id', $usage->getId())
            ->addFilter('customer_id', 0);
        if (isset($pendingCustomers)) {
            foreach ($pendingCustomers as $pendingCustomer) {
                if (!isset($pendingCustomer['email']) || $pendingCustomer['email'] === "") {
                    continue;
                }
                $usageCustomer = $this->usageCustomerFactory->create();

                $usageCustomer = $this->usageCustomerRepository->getByUsageAndEmail($usageId, $pendingCustomer['email']) ?? $usageCustomer;

                $usageCustomer->setUsageId($usage->getId());
                $usageCustomer->setPendingCustomerEmail($pendingCustomer['email']);
                $usageCustomer = $this->usageCustomerRepository->save($usageCustomer);
                $savedIds[] = $usageCustomer->getId();
            }
            $searchCriteria->addFilter('entity_id', $savedIds, 'nin');
        }
        $this->usageCustomerRepository->deleteList($searchCriteria->create());
    }

    /**
     * @return mixed
     */
    public function getCustomLicenseId()
    {
        return $this->scopeConfig->getValue(
            'usage_cal/general/category_id',
            ScopeInterface::SCOPE_STORE
        );
    }
}
