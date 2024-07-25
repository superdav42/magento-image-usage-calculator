<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */
/** @noinspection PhpUndefinedMethodInspection */

/**
 * Usage
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Block\Catalog\Product;

use DevStone\UsageCalculator\Api\CategoryRepositoryInterface;
use DevStone\UsageCalculator\Api\UsageRepositoryInterface;
use DevStone\UsageCalculator\Helper\Data as DataHelper;
use DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\Value\CollectionFactory as UsageOptionCollectionFactory;
use DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\Collection;
use DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory as UsageCustomerCollectionFactory;
use DevStone\UsageCalculator\Model\Usage\Option;
use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Checkout\Model\Session;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Pricing\Price\LinkPrice;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Html\Select;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Downloadable Product Links part block
 *
 * @api
 * @since 100.0.2
 */
class Usage extends AbstractProduct
{
    protected Data $pricingHelper;
    protected SerializerInterface $serializer;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected UsageRepositoryInterface $usageRepository;
    protected CategoryRepositoryInterface $categoryRepository;
    private array $usages;
    protected UsageCustomerCollectionFactory $usageCustomerCollectionFactory;
    protected Option $optionModel;
    protected \Magento\Customer\Model\Session $session;
    protected Session $checkoutSession;
    protected UsageOptionCollectionFactory $usageOptionCollectionFactory;
    protected CollectionFactory $orderCollectionFactory;
    protected SortOrderBuilder $sortOrderBuilder;
    private DataHelper $config;

    public function __construct(
        Context                         $context,
        Data                            $pricingHelper,
        SerializerInterface             $serializer,
        SearchCriteriaBuilder           $searchCriteriaBuilder,
        UsageRepositoryInterface        $usageRepository,
        CategoryRepositoryInterface     $categoryRepository,
        Option                          $option,
        UsageCustomerCollectionFactory  $usageCustomerCollectionFactory,
        \Magento\Customer\Model\Session $session,
        Session                         $checkoutSession,
        UsageOptionCollectionFactory    $usageOptionCollection,
        CollectionFactory               $orderCollectionFactory,
        SortOrderBuilder                $sortOrderBuilder,
        DataHelper                      $config,
        array                           $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->serializer = $serializer;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->usageRepository = $usageRepository;
        $this->categoryRepository = $categoryRepository;
        $this->optionModel = $option;
        $this->usageCustomerCollectionFactory = $usageCustomerCollectionFactory;
        $this->session = $session;
        $this->checkoutSession = $checkoutSession;
        $this->usageOptionCollectionFactory = $usageOptionCollection;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        parent::__construct($context, $data);
        $this->config = $config;
    }

    public function getLinksPurchasedSeparately(): bool
    {
        return $this->getProduct()->getLinksPurchasedSeparately();
    }

    public function getLinkSelectionRequired(): bool
    {
        return $this->getProduct()->getTypeInstance()->getLinkSelectionRequired($this->getProduct());
    }

    public function hasLinks(): bool
    {
        return $this->getProduct()->getTypeInstance()->hasLinks($this->getProduct());
    }

    public function getLinks(): array
    {
        return $this->getProduct()->getTypeInstance()->getLinks($this->getProduct());
    }
    public function getAllCustomUsages()
    {
        $usageCollection = $this->usageCustomerCollectionFactory->create();

        if ($usageCollection->getSize() > 0) {
            $customerUsage = [];
            foreach ($usageCollection as $usage) {
                $customerUsage[] = $usage->getUsageId();
            }

            $searchCriteria     = $this->searchCriteriaBuilder->addFilter(
                'entity_id',
                $customerUsage,
                'in'
            )->create();
            $customerUsageItems = $this->usageRepository->getList($searchCriteria)->getItems();
            /** @var \DevStone\UsageCalculator\Model\Usage $customerUsageItem */
            foreach ($customerUsageItems as $key => $customerUsageItem) {
                if (! $customerUsageItem->getConditions()->validate($this->getProduct())) {
                    unset($customerUsageItems[$key]);
                }
            }

            return $customerUsageItems;
        }
    }

    public function getUsages($category = null): array
    {
        if (empty($this->usages)) {
            $customLicenseId = $this->config->getCustomLicenseId();

            $sortOrder = $this->sortOrderBuilder
                ->setField('name')
                ->setDirection(SortOrder::SORT_ASC)
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('category_id', $customLicenseId, 'neq')
                ->addSortOrder($sortOrder)
                ->create();
            $items = $this->usageRepository->getList($searchCriteria)->getItems();

            if ($this->isCustomerLoggedIn()) {
                $customerId = $this->getCustomerId();
                $usageCollection = $this->getUsageListAccordingToCustomer($customerId);

                if ($usageCollection->getSize() > 0) {
                    $customerUsage = [];
                    foreach ($usageCollection as $usage) {
                        $customerUsage[] = $usage->getUsageId();
                    }

                    $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                        'entity_id',
                        $customerUsage,
                        'in'
                    )->create();
                    $customerUsageItems = $this->usageRepository->getList($searchCriteria)->getItems();
                    /** @var \DevStone\UsageCalculator\Model\Usage $customerUsageItem */
                    foreach ($customerUsageItems as $key => $customerUsageItem) {
                        if (!$customerUsageItem->getConditions()->validate($this->getProduct())) {
                            unset($customerUsageItems[$key]);
                        }
                    }
                    $items = array_merge_recursive($items, $customerUsageItems);
                }
            }

            $this->usages = [];
            foreach ($items as $item) {
                $item->afterLoad();
                $this->usages[$item->getCategoryId()][] = $item;
            }
        }
        if ($category) {
            if (isset($this->usages[$category->getId()])) {
                return $this->usages[$category->getId()];
            } else {
                return [];
            }
        }
        if ($this->usages) {
            return call_user_func_array('array_merge', $this->usages);
        } else {
            return $this->usages;
        }
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->session->isLoggedIn();
    }

    public function getCustomerId(): ?int
    {
        return $this->session->getCustomerId();
    }

    public function getUsageListAccordingToCustomer($customerId): Collection
    {
        $usageCustomerCollection = $this->usageCustomerCollectionFactory->create();
        $usageCustomerCollection->addFieldToFilter('customer_id', $customerId);
        return $usageCustomerCollection;
    }

    public function getAllCategories()
    {

        if ($this->isCustomerLoggedIn()) {
            $customerId = $this->getCustomerId();
            $usageCollection = $this->getUsageListAccordingToCustomer($customerId);
            if ($usageCollection->getSize() > 0) {
                $searchCriteria = $this->searchCriteriaBuilder->create();
                return $this->categoryRepository->getList($searchCriteria)->getItems();
            }
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $this->config->getCustomLicenseId(), 'neq')
            ->create();
        return $this->categoryRepository->getList($searchCriteria)->getItems();
    }
    public function getCustomLicenseCategory()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $this->config->getCustomLicenseId(), 'eq')
            ->create();
        $items = $this->categoryRepository->getList($searchCriteria)->getItems();
        return array_pop($items);
    }
    /**
     * @throws LocalizedException
     */
    public function getCategories(): array
    {
        if ($this->hasData('customer_specific') && $this->getdata('customer_specific')) {
            if ($this->isCustomerLoggedIn()) {
                $customerId = $this->getCustomerId();
                $usageCollection = $this->getUsageListAccordingToCustomer($customerId);
                if ($usageCollection->getSize() > 0) {
                    $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('entity_id', $this->config->getCustomLicenseId(), 'eq')
                        ->create();
                    return $this->categoryRepository->getList($searchCriteria)->getItems();
                }
            }
            return [];
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $this->config->getCustomLicenseId(), 'neq')
            ->create();
        return $this->categoryRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @throws LocalizedException
     */
    public function getUsagesSelectHtml(
        $usages,
        $category
    ): string {
        $store = $this->getProduct()->getStore();

        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => 'usage_' . $category->getId() . '_usages',
                'class' => 'required product-custom-option admin__control-select usage-select-box',
            ]
        );

        $select->setName('usage_id[' . $category->getId() . ']')->addOption('', __('-- Please Select --'));

        foreach ($usages as $usage) {
            $select->addOption(
                $usage->getId(),
                $usage->getName(),
                [
                    'price' => $this->pricingHelper->currencyByStore($usage->getPrice(), $store, false),
                    'data-terms' => $usage->getTerms(),
                    'data-is-free' => $usage->getIsFree(),
                ]
            );
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        return $select->getHtml();
    }

    /**
     * @throws LocalizedException
     */
    public function getCategoriesSelectHtml(): string
    {
        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => 'usage_category',
                'class' => 'required product-custom-option admin__control-select',
            ]
        );

        $select->setName('usage_category')->addOption('', __('-- Please Select --'));

        foreach ($this->getAllCategories() as $category) {
            $select->addOption(
                $category->getId(),
                $category->getName()
            );
        }
        if (count($this->getPreviousCategories())) {
            $select->addOption(
                'previous',
                'Previous Usages'
            );
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        return $select->getHtml();
    }

    /**
     * Returns price converted to current currency rate
     */
    public function getCurrencyPrice(
        float $price
    ): float {
        $store = $this->getProduct()->getStore();
        return $this->pricingHelper->currencyByStore($price, $store, false);
    }

    public function getJsonConfig(): string
    {
        $finalPrice = $this->getProduct()->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE);

        $linksConfig = [];
        foreach ($this->getUsages() as $usage) {
            $amount = $finalPrice->getCustomAmount($usage->getPrice());
            $linksConfig[$usage->getId()] = [
                'finalPrice' => $amount->getValue(),
                'basePrice' => $amount->getBaseAmount(),
            ];
        }

        return $this->serializer->serialize(['links' => $linksConfig]);
    }

    /**
     * @throws NoSuchEntityException
     */
    protected function getLinkSampleUrl(
        Link $link
    ): string {
        $store = $this->getProduct()->getStore();
        return $store->getUrl('downloadable/download/linkSample', ['link_id' => $link->getId()]);
    }

    /**
     * Return title of links section
     */
    public function getLinksTitle(): string
    {
        if ($this->getProduct()->getLinksTitle()) {
            return $this->getProduct()->getLinksTitle();
        }
        return $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return true if target of link new window
     */
    public function getIsOpenInNewWindow(): bool
    {
        return $this->_scopeConfig->isSetFlag(
            Link::XML_PATH_TARGET_NEW_WINDOW,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns whether link checked by default or not
     */
    public function getIsLinkChecked(Link $link): bool
    {
        $configValue = $this->getProduct()->getPreconfiguredValues()->getLinks();
        if (!$configValue || !is_array($configValue)) {
            return false;
        }

        return in_array($link->getId(), $configValue);
    }

    /**
     * Returns value for link's input checkbox - either 'checked' or ''
     */
    public function getLinkCheckedValue(Link $link): string
    {
        return $this->getIsLinkChecked($link) ? 'checked' : '';
    }

    protected function getLinkAmount(Link $link): AmountInterface
    {
        return $this->getPriceType()->getLinkAmount($link);
    }

    /**
     * @throws LocalizedException
     */
    public function getLinkPrice(Link $link): string
    {
        return $this->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $this->getLinkAmount($link),
            $this->getPriceType(),
            $this->getProduct()
        );
    }

    /**
     * Get LinkPrice Type
     */
    protected function getPriceType(): PriceInterface
    {
        return $this->getProduct()->getPriceInfo()->getPrice(LinkPrice::PRICE_CODE);
    }

    /**
     * Get option html block
     */
    public function getOptionHtml(Option $option): string
    {
        $type = $this->getGroupOfOption($option->getType());
        $renderer = $this->getChildBlock($type);

        $renderer->setProduct($this->getProduct())->setOption($option);

        return $this->getChildHtml($type, false);
    }

    public function getGroupOfOption(string $type): string
    {
        $group = $this->optionModel->getGroupByType($type);

        return $group == '' ? 'default' : $group;
    }

    /**
     * @throws LocalizedException
     */
    public function getPreviousSelectHtml(): string
    {
        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => 'usage_previous_usages',
                'class' => 'required product-custom-option admin__control-select usage-select-box',
            ]
        );

        $select->setName('previous_category')->addOption('', __('-- Please Select --'));

        foreach ($this->getPreviousCategories() as $category) {
            $select->addOption(
                $category['id'],
                $category['name']
            );
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        return $select->getHtml();
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPreviousCategories(): array
    {
        $previousCategories = [];
        try {
            $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($items as $item) {
                $getBuyRequest = $item->getBuyRequest();
                $previousCategoriesByItems = $this->getPreviousCategoriesByItemsFromBuyRequest($getBuyRequest);
                if (count($previousCategoriesByItems) && !in_array($previousCategoriesByItems, $previousCategories)) {
                    $previousCategories[] = $previousCategoriesByItems;
                }
                if (count($previousCategories) >= 10) {
                    break;
                }
            }
            if ($this->isCustomerLoggedIn() && count($previousCategories) <= 10) {
                $ordersCollection = $this->orderCollectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('customer_id', $this->getCustomerId())
                    ->setPageSize(20 - count($previousCategories))
                    ->setOrder('created_at', 'desc');
                /**
                 * @var Order $order
                 * @var OrderItemInterface $item
                 */
                foreach ($ordersCollection as $order) {
                    $items = $order->getAllVisibleItems();
                    foreach ($items as $item) {
                        try {
                            /**
                             * @var Item $item
                             */
                            $getBuyRequest = $item->getProductOptions()['info_buyRequest'];
                            $previousCategoriesByItems = $this->getPreviousCategoriesByItemsFromBuyRequest($getBuyRequest);
                            if (count($previousCategoriesByItems) && !in_array(
                                $previousCategoriesByItems,
                                $previousCategories
                            )) {
                                $previousCategories[] = $previousCategoriesByItems;
                            }
                        } catch (Exception $e) {
                            // Probably usage was deleted, skip.
                            continue;
                        }

                        if (count($previousCategories) >= 10) {
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->_logger->error(__('Unable to fetch previous categories.'));
            $this->_logger->error($e->getMessage());
        }
        return $previousCategories;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getPreviousCategoriesByItemsFromBuyRequest($buyRequest): array
    {
        $id = '';
        $value = '';
        $previousCategoriesByItems = [];
        if (isset($buyRequest['usage_category'])) {
            $id .= $buyRequest['usage_category'] . ' - ';
            $value .= $this->getCategoryName($buyRequest['usage_category']) . ' - ';
            $id .= $buyRequest['usage_id'][$buyRequest['usage_category']] . ' - ';
            $value .= $this->getUsageName($buyRequest['usage_id'][$buyRequest['usage_category']]) . ' - ';
            if (isset($buyRequest['options'])) {
                foreach ($buyRequest['options'] as $key => $option) {
                    $id .= $key . ':' . $option . ' - ';
                    $value .= $this->getOptionName($option) . ' - ';
                }
            }
            $previousCategoriesByItems['id'] = trim($id, ' - ');
            $previousCategoriesByItems['name'] = trim($value, ' - ');
        }
        return $previousCategoriesByItems;
    }

    /**
     * @throws LocalizedException
     */
    public function getCategoryName($categoryId): ?string
    {
        $category = $this->categoryRepository->getById($categoryId);
        return $category->getName();
    }

    /**
     * @throws LocalizedException
     */
    public function getUsageName($usageId): string
    {
        $usage = $this->usageRepository->getById($usageId);
        return $usage->getName();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getOptionName($optionId): string
    {
        $usageOptionCollection = $this->usageOptionCollectionFactory->create();
        $usageOptionCollection->addTitlesToResult($this->_storeManager->getStore()->getId());
        $usageOptionCollection->addFieldToFilter('main_table.option_type_id', $optionId);
        return $usageOptionCollection->count() ? $usageOptionCollection->getFirstItem()->getTitle() : "";
    }

    /** Used for plugins to hide button */
    public function showButton(): bool
    {
        return true;
    }
}
