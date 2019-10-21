<?php

namespace DevStone\UsageCalculator\Block\Adminhtml\Customer;

/**
 * Class AssignCustomers
 * @package DevStone\UsageCalculator\Block\Adminhtml\Customer
 */
class AssignCustomers extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'DevStone_UsageCalculator::usage/edit/assign_customer.phtml';

    /**
     * @var
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * AssignCustomers constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\RequestInterface $request,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \DevStone\UsageCalculator\Block\Adminhtml\Customer\Tab\Customer::class,
                'usage.customer.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $customer = $this->getUsageCustomer();

        if (!empty($customer)) {
            return $this->jsonEncoder->encode($customer);
        }
        return '{}';
    }


    /**
     * @return array
     */
    public function getUsageCustomer()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $customerArray = [];

        if(isset($id)){
            $collection = $this->collectionFactory->create();
            foreach ($collection as $item) {
                $customerArray[$item->getCustomerId()] = $item->getCustomerId();
            }
        }
        return $customerArray;
    }

    public function showGrid(){
        $customLicense = $this->request->getParam('custom_license');
        if(isset($customLicense))
            return $customLicense;
        return false;
    }
}