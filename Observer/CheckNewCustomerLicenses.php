<?php

declare(strict_types=1);

namespace DevStone\UsageCalculator\Observer;

use DevStone\UsageCalculator\Api\Data\UsageCustomerInterface;
use DevStone\UsageCalculator\Api\UsageCustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CheckNewCustomerLicenses implements ObserverInterface
{
    public function __construct(protected SearchCriteriaBuilder $searchCriteriaBuilder, private readonly UsageCustomerRepositoryInterface $usageCustomerRepository, private readonly LoggerInterface $logger)
    {
    }

    #[\Override]
    public function execute(Observer $observer)
    {
        try {
            /** @var CustomerInterface $customer */
            $customer = $observer->getCustomer();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('pending_customer_email', $customer->getEmail())
                ->addFilter('customer_id', 0)
                ->create();
            $usageCustomers = $this->usageCustomerRepository->getList($searchCriteria);
            if ($usageCustomers->getTotalCount() > 0) {
                /** @var UsageCustomerInterface $usageCustomer */
                foreach ($usageCustomers->getItems() as $usageCustomer) {
                    $usageCustomer->setCustomerId((int)$customer->getId());
                    $usageCustomer->setPendingCustomerEmail("");
                    $this->usageCustomerRepository->save($usageCustomer);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(__('Unable to attach licenses to customer %s', $customer->getId()));
            $this->logger->error($e->getMessage());
        }
    }
}
