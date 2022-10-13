<?php

declare(strict_types=1);

namespace DevStone\UsageCalculator\Api\Data;

interface UsageCustomerInterface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $entity_id
     * @return UsageCustomerInterface
     */
    public function setId(int $entity_id);

    /**
     * @return int
     */
    public function getUsageId(): int;

    /**
     * @param int $usageId
     * @return UsageCustomerInterface
     */
    public function setUsageId(int $usageId): UsageCustomerInterface;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return UsageCustomerInterface
     */
    public function setCustomerId(int $customerId): UsageCustomerInterface;
    /**
     * @return string
     */
    public function getPendingCustomerEmail(): string;

    /**
     * @param string $pendingCustomerEmail
     * @return UsageCustomerInterface
     */
    public function setPendingCustomerEmail(string $pendingCustomerEmail): UsageCustomerInterface;
}
