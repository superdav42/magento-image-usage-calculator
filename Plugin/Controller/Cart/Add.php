<?php
/**
 * Add
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Plugin\Controller\Cart;

use DevStone\UsageCalculator\Api\UsageRepositoryInterface;
use DevStone\UsageCalculator\Helper\Data;
use Magento\Checkout\Controller\Cart\Add as AddSubject;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class Add
 * @package DevStone\UsageCalculator\Plugin\Controller\Cart
 */
class Add
{
    protected CustomerSession $customerSession;
    protected RequestInterface $request;
    protected UsageRepositoryInterface $usageRepository;
    protected CollectionFactory $orderCollectionFactory;
    protected Session $checkoutSession;
    protected ScopeConfigInterface $scopeConfig;
    protected ManagerInterface $messageInterface;
    protected RedirectFactory $resultRedirectFactory;
    private Data $data;

    public function __construct(
        CustomerSession          $session,
        RequestInterface         $request,
        CollectionFactory        $orderCollectionFactory,
        Session                  $checkoutSession,
        ScopeConfigInterface     $config,
        ManagerInterface         $messageInterface,
        RedirectFactory          $redirectFactory,
        UsageRepositoryInterface $usageRepository,
        Data                     $data
    ) {
        $this->request = $request;
        $this->customerSession = $session;
        $this->usageRepository = $usageRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $config;
        $this->messageInterface = $messageInterface;
        $this->resultRedirectFactory = $redirectFactory;
        $this->data = $data;
    }

    public function aroundExecute(AddSubject $subject, callable $proceed): mixed
    {
        $usageId = $this->request->getParam('usage_id');
        if ($this->request->getParam('usage_category') != $this->data->getCustomLicenseId()) {
            return $proceed();
        } elseif (array_key_exists($this->data->getCustomLicenseId(), $usageId)) {
            if ($this->customerSession->isLoggedIn()) {
                try {
                    $customerLicensedUsage = $this->usageRepository->getById($usageId[$this->data->getCustomLicenseId()]);
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
                                custom license can only be used %1 time(s)', $maxUsage)
                            );
                            return $this->resultRedirectFactory->create()->setPath('*/*/');
                        }
                    }
                } catch (LocalizedException $e) {

                }
            }
        }
        return $proceed();
    }

    public function getUsageCountByOrders($usageId): int
    {
        $totalUsageCount = 0;
        $ordersCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
            ->setPageSize(20)
            ->setOrder('created_at', 'desc');
        /**
         * @var Order $order
         * @var OrderItemInterface $item
         */
        foreach ($ordersCollection as $order) {
            $items = $order->getAllVisibleItems();
            foreach ($items as $item) {
                $productOptions = $item->getProductOptions();
                if (isset($productOptions['usage_id']) && $usageId[$this->data->getCustomLicenseId()] === $productOptions['usage_id']) {
                    $totalUsageCount++;
                }
            }
        }

        return $totalUsageCount;
    }

    public function getUsageCountByQuote($usageId): int
    {
        $totalUsageCount = 0;
        try {
            $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($items as $item) {
                $buyRequest = $item->getBuyRequest();
                $categoryId = $buyRequest->getUsageCategory();
                if ($categoryId == $this->data->getCustomLicenseId()) {
                    $usageIds = $buyRequest->getUsageId();
                    if (array_key_exists($this->data->getCustomLicenseId(), $usageIds)) {
                        if ($usageIds[$this->data->getCustomLicenseId()] == $usageId[$this->data->getCustomLicenseId()]) {
                            $totalUsageCount++;
                        }
                    }
                }
            }

            return $totalUsageCount;
        } catch (LocalizedException $e) {

        }
        return $totalUsageCount;
    }
}
