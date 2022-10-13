<?php

namespace DevStone\UsageCalculator\Model;

use DevStone\UsageCalculator\Api\Data\UsageCustomerInterface;
use DevStone\UsageCalculator\Setup\UsageSetup;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class UsageCustomer
 * @package DevStone\UsageCalculator\Model
 */
class UsageCustomer extends AbstractModel implements IdentityInterface, UsageCustomerInterface
{
    const CACHE_TAG = UsageSetup::ENTITY_TYPE_CODE . '_customer';
    protected $_cacheTag = UsageSetup::ENTITY_TYPE_CODE . '_customer';
    protected $_eventPrefix = UsageSetup::ENTITY_TYPE_CODE . '_customer';
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(ResourceModel\UsageCustomer::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getUsageId(): int
    {
        return (int) $this->getData('usage_id');
    }

    public function setUsageId(int $usageId): UsageCustomerInterface
    {
        return $this->setData('usage_id', $usageId);
    }

    public function getCustomerId(): int
    {
        return (int) $this->getData('customer_id');
    }

    public function setCustomerId(int $customerId): UsageCustomerInterface
    {
        return $this->setData('customer_id', $customerId);
    }

    public function getPendingCustomerEmail(): string
    {
        return (string) $this->getData('pending_customer_email');
    }

    public function setPendingCustomerEmail(string $pendingCustomerEmail): UsageCustomerInterface
    {
        return $this->setData('pending_customer_email', $pendingCustomerEmail);
    }
}
