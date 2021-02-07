<?php
/**
 * Add
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Plugin\Controller\Cart;

/**
 * Class Add
 * @package DevStone\UsageCalculator\Plugin\Controller\Cart
 */
class Add
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \DevStone\UsageCalculator\Api\UsageRepositoryInterface
     */
    private $usageRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageInterface;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * Add constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Message\ManagerInterface $messageInterface
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \DevStone\UsageCalculator\Api\UsageRepositoryInterface $usageRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Message\ManagerInterface $messageInterface,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \DevStone\UsageCalculator\Api\UsageRepositoryInterface $usageRepository
    ) {
        $this->request = $request;
        $this->customerSession = $session;
        $this->usageRepository = $usageRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $config;
        $this->messageInterface = $messageInterface;
        $this->resultRedirectFactory = $redirectFactory;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundExecute(\Magento\Checkout\Controller\Cart\Add $subject, callable $proceed)
    {
        $usageId = $this->request->getParam('usage_id');
        $usageCategory = $this->request->getParam('usage_category');
        if ($usageCategory != $this->getCustomLicenseId() || !isset($usageCategory)) {
            return $proceed();
        } elseif (array_key_exists($this->getCustomLicenseId(), $usageId)) {
            if ($this->customerSession->isLoggedIn()) {
                $customerLicensedUsage = $this->usageRepository->getById($usageId[$this->getCustomLicenseId()]);
                if ($customerLicensedUsage) {
                    $maxUsage = $customerLicensedUsage->getMaxUsage();
                    if (!isset($maxUsage) || !($maxUsage > 0)) {
                        return $proceed();
                    }
                    $totalUsageCountByOrder = $this->getUsageCountByOrders($usageId);
                    $totalUsageCountByQuote = $this->getUsageCountByQuote($usageId);
                    if (($totalUsageCountByOrder + $totalUsageCountByQuote) < $maxUsage) {
                        return $proceed();
                    } else {
                        $this->messageInterface->addErrorMessage(
                            __('You cannot add this item to your cart because this 
                                custom license can only be used %1 times', $maxUsage)
                        );
                        return $this->resultRedirectFactory->create()->setPath('*/*/');
                    }
                }
            }
        }
        return $proceed();
    }

    /**
     * @param $usageId
     * @return int
     */
    public function getUsageCountByOrders($usageId)
    {
        $totalUsageCount = 0;
        $ordersCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
            ->setPageSize(20)
            ->setOrder('created_at', 'desc');
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Api\Data\OrderItemInterface $item
         */
        foreach ($ordersCollection as $order) {
            $items = $order->getAllVisibleItems();
            foreach ($items as $item) {
                $productOptions = $item->getProductOptions();
                if (isset($productOptions['usage_id']) && $usageId === $productOptions['usage_id']) {
                    $totalUsageCount++;
                }
            }
        }

        return $totalUsageCount;
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

    /**
     * @param $usageId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUsageCountByQuote($usageId)
    {
        $totalUsageCount = 0;
        $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            $buyRequest = $item->getBuyRequest();
            $categoryId = $buyRequest->getUsageCategory();
            if ($categoryId == $this->getCustomLicenseId()) {
                $usageIds = $buyRequest->getUsageId();
                if (array_key_exists($this->getCustomLicenseId(), $usageIds)) {
                    if ($usageIds[$this->getCustomLicenseId()] == $usageId[$this->getCustomLicenseId()]) {
                        $totalUsageCount++;
                    }
                }
            }
        }

        return $totalUsageCount;
    }
}
