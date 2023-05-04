<?php
/**
 * AssignCustomers
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

namespace DevStone\UsageCalculator\Block\Adminhtml\Customer;

use DevStone\UsageCalculator\Api\UsageCustomerRepositoryInterface;
use DevStone\UsageCalculator\Block\Adminhtml\Customer\Tab\Customer;
use DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class AssignCustomers
 * @package DevStone\UsageCalculator\Block\Adminhtml\Customer
 */
class AssignCustomers extends Template
{
    /**
     * @var string
     */
    protected $_template = 'DevStone_UsageCalculator::usage/edit/assign_customer.phtml';

    /**
     * @var
     */
    protected $blockGrid;

    protected Registry $registry;
    protected SerializerInterface $serializer;
    protected RequestInterface $request;
    protected CollectionFactory $collectionFactory;
    protected UsageCustomerRepositoryInterface $usageCustomerRepository;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        Registry $registry,
        SerializerInterface $serializer,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        UsageCustomerRepositoryInterface $usageCustomerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
        $this->usageCustomerRepository = $usageCustomerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @throws LocalizedException
     */
    public function getBlockGrid(): BlockInterface
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                Customer::class,
                'usage.customer.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @throws LocalizedException
     */
    public function getGridHtml(): string
    {
        return $this->getBlockGrid()->toHtml();
    }

    public function getProductsJson(): string
    {
        $customer = $this->getUsageCustomer();

        if (!empty($customer)) {
            return $this->serializer->serialize($customer);
        }
        return '{}';
    }

    public function getUsageCustomer(): array
    {
        $id = $this->getRequest()->getParam('entity_id');
        $customerArray = [];

        if (isset($id)) {
            $search = $this->searchCriteriaBuilder->addFilter('usage_id', $id)->create();
            $usages = $this->usageCustomerRepository->getList($search)->getItems();
            foreach ($usages as $item) {
                $customerArray[$item->getCustomerId()] = $item->getCustomerId();
            }
        }
        return $customerArray;
    }

    public function showGrid(): bool
    {
        $customLicense = $this->request->getParam('custom_license');
        if (isset($customLicense)) {
            return true;
        }
        return false;
    }
}
