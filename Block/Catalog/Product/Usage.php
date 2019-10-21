<?php

namespace DevStone\UsageCalculator\Block\Catalog\Product;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Pricing\Price\LinkPrice;
use Magento\Framework\Json\EncoderInterface;

/**
 * Downloadable Product Links part block
 *
 * @api
 * @since 100.0.2
 */
class Usage extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var UsageRepositoryInterface
     */
    protected $usageRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     *
     * @var \DevStone\UsageCalculator\Model\Category[]
     */
    private $categories;

    /**
     *
     * @var \DevStone\UsageCalculator\Model\Usage[]
     */
    private $usages;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory
     */
    protected $usageCustomerCollectionFactory;

    /**
     * @var \DevStone\UsageCalculator\Model\Usage\Option
     */
    protected $optionModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * Usage constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param EncoderInterface $encoder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \DevStone\UsageCalculator\Api\UsageRepositoryInterface $usageRepository
     * @param \DevStone\UsageCalculator\Api\CategoryRepositoryInterface $categoryRepository
     * @param \DevStone\UsageCalculator\Model\Usage\Option $option
     * @param \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $usageCustomerCollectionFactory
     * @param \Magento\Customer\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        EncoderInterface $encoder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \DevStone\UsageCalculator\Api\UsageRepositoryInterface $usageRepository,
        \DevStone\UsageCalculator\Api\CategoryRepositoryInterface $categoryRepository,
        \DevStone\UsageCalculator\Model\Usage\Option $option,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $usageCustomerCollectionFactory,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->encoder = $encoder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->usageRepository = $usageRepository;
        $this->categoryRepository = $categoryRepository;
        $this->optionModel = $option;
        $this->usageCustomerCollectionFactory = $usageCustomerCollectionFactory;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return boolean
     */
    public function getLinksPurchasedSeparately()
    {
        return $this->getProduct()->getLinksPurchasedSeparately();
    }

    /**
     * @return boolean
     */
    public function getLinkSelectionRequired()
    {
        return $this->getProduct()->getTypeInstance()->getLinkSelectionRequired($this->getProduct());
    }

    /**
     * @return boolean
     */
    public function hasLinks()
    {
        return $this->getProduct()->getTypeInstance()->hasLinks($this->getProduct());
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->getProduct()->getTypeInstance()->getLinks($this->getProduct());
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomLicenseId()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $list = $this->categoryRepository->getList($searchCriteria)->getItems();
        foreach ($list as $catagory) {
            if ($catagory->getName() == \DevStone\UsageCalculator\Model\Usage\CatagoriesOptionsProvider::CUSTOM_LICENSE) {
                return $catagory->getId();
            }
        }
        return null;
    }


    /**
     * @return array
     */
    public function getUsages($category = null)
    {
        if (empty($this->usages)) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $items = $this->usageRepository->getList($searchCriteria)->getItems();

            if ($this->isCustomerLoggedIn()) {
                $customerId = $this->getCustomerId();
                $usageCollection = $this->getUsageListAccordingToCustomer($customerId);

                $customLicenseId = $this->getCustomLicenseId();
                if (count($usageCollection)) {
                    $customerUsage = [];

                    foreach ($usageCollection as $usage) {
                        $customerUsage[$usage->getUsageId()] = $usage;
                    }
                    foreach ($items as $key => $usage) {
                        if ($usage->getCategoryId() == $customLicenseId) {
                            if (!array_key_exists($usage->getId(), $customerUsage)) {
                                unset($items[$key]);
                            }
                        }
                    }

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

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->session->getCustomerId();
    }

    /**
     * @param $customerId
     * @return \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\Collection
     */
    public function getUsageListAccordingToCustomer($customerId) {
        /**
         * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\Collection $usageCustomerCollection
         */
        $usageCustomerCollection = $this->usageCustomerCollectionFactory->create();
        $usageCustomerCollection->addFieldToFilter('customer_id', $customerId);
        return $usageCustomerCollection;
    }

    /**
     * @return array|\DevStone\UsageCalculator\Api\Data\CategoryInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $list = $this->categoryRepository->getList($searchCriteria)->getItems();

        if (!$this->isCustomerLoggedIn()) {
            $customerId = $this->getCustomerId();
            $usageCollection = $this->getUsageListAccordingToCustomer($customerId);
            if (count($usageCollection)) {
                $newCategories = [];
                foreach ($list as $catagory) {
                    if ($catagory->getName() != \DevStone\UsageCalculator\Model\Usage\CatagoriesOptionsProvider::CUSTOM_LICENSE) {
                        $newCategories[] = $catagory;
                    }
                }
                $list = $newCategories;
            }
        }

        return $list;
    }

    /**
     * @param $usages
     * @param $category
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUsagesSelectHtml($usages, $category)
    {
        $store = $this->getProduct()->getStore();

        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => 'usage_' . $category->getId() . '_usages',
                'class' => 'required product-custom-option admin__control-select usage-select-box'
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
                    'credits' => $usage->getCredits(),
                ]
            );

        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        return $select->getHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoriesSelectHtml()
    {
        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => 'usage_category',
                'class' => 'required product-custom-option admin__control-select'
            ]
        );

        $select->setName('usage_category')->addOption('', __('-- Please Select --'));

        foreach ($this->getCategories() as $category) {

            $select->addOption(
                $category->getId(),
                $category->getName()
            );
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        return $select->getHtml();
    }

    /**
     * Returns price converted to current currency rate
     *
     * @param float $price
     * @return float
     */
    public function getCurrencyPrice($price)
    {
        $store = $this->getProduct()->getStore();
        return $this->pricingHelper->currencyByStore($price, $store, false);
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        $finalPrice = $this->getProduct()->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE);

        $linksConfig = [];
        foreach ($this->getUsages() as $usage) {

            $amount = $finalPrice->getCustomAmount($usage->getPrice());
            $linksConfig[$usage->getId()] = [
                'finalPrice' => $amount->getValue(),
                'basePrice' => $amount->getBaseAmount()
            ];
        }

        return $this->encoder->encode(['links' => $linksConfig]);
    }

    /**
     * @param Link $link
     * @return string
     */
    public function getLinkSampleUrl($link)
    {
        $store = $this->getProduct()->getStore();
        return $store->getUrl('downloadable/download/linkSample', ['link_id' => $link->getId()]);
    }

    /**
     * Return title of links section
     *
     * @return string
     */
    public function getLinksTitle()
    {
        if ($this->getProduct()->getLinksTitle()) {
            return $this->getProduct()->getLinksTitle();
        }
        return $this->_scopeConfig->getValue(
            \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return true if target of link new window
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsOpenInNewWindow()
    {
        return $this->_scopeConfig->isSetFlag(
            \Magento\Downloadable\Model\Link::XML_PATH_TARGET_NEW_WINDOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns whether link checked by default or not
     *
     * @param Link $link
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsLinkChecked($link)
    {
        $configValue = $this->getProduct()->getPreconfiguredValues()->getLinks();
        if (!$configValue || !is_array($configValue)) {
            return false;
        }

        return $configValue && in_array($link->getId(), $configValue);
    }

    /**
     * Returns value for link's input checkbox - either 'checked' or ''
     *
     * @param Link $link
     * @return string
     */
    public function getLinkCheckedValue($link)
    {
        return $this->getIsLinkChecked($link) ? 'checked' : '';
    }

    /**
     * @param Link $link
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function getLinkAmount($link)
    {
        return $this->getPriceType()->getLinkAmount($link);
    }

    /**
     * @param Link $link
     * @return string
     */
    public function getLinkPrice(Link $link)
    {
        return $this->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $this->getLinkAmount($link),
            $this->getPriceType(),
            $this->getProduct()
        );
    }

    /**
     * Get LinkPrice Type
     *
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    protected function getPriceType()
    {
        return $this->getProduct()->getPriceInfo()->getPrice(LinkPrice::PRICE_CODE);
    }


    /**
     * Get option html block
     *
     * @param \DevStone\UsageCalculator\Model\Usage\Option $option
     * @return string
     */
    public function getOptionHtml(\DevStone\UsageCalculator\Model\Usage\Option $option)
    {
        $type = $this->getGroupOfOption($option->getType());
        $renderer = $this->getChildBlock($type);

        $renderer->setProduct($this->getProduct())->setOption($option);

        return $this->getChildHtml($type, false);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getGroupOfOption($type)
    {
        $group = $this->optionModel->getGroupByType($type);

        return $group == '' ? 'default' : $group;
    }
}
