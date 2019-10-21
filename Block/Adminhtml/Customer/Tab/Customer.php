<?php

namespace DevStone\UsageCalculator\Block\Adminhtml\Customer\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;

/**
 * Class Customer
 * @package DevStone\UsageCalculator\Block\Adminhtml\Customer\Tab
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var mixed
     */
    private $status;

    /**
     * @var mixed
     */
    private $visibility;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory
     */
    protected $collectionFactory;


    /**
     * Customer constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory
     * @param array $data
     * @param Visibility|null $visibility
     * @param Status|null $status
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\RequestInterface $request,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory,
        array $data = [],
        Visibility $visibility = null,
        Status $status = null
    ) {
        $this->customerFactory = $customerFactory;
        $this->coreRegistry = $coreRegistry;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->status = $status ?: ObjectManager::getInstance()->get(Status::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('usage_calculator_customers');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->customerFactory->create()->getCollection()->addAttributeToSelect('*');
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_usage',
            [
                'type' => 'checkbox',
                'name' => 'in_usage',
                'values' => $this->_getSelectedCustomer(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customer', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedCustomer()
    {
        $customers = $this->getRequest()->getPost('selected_customers');
        if ($customers === null) {
            return $this->getCustomersId();
        }
        return $customers;
    }

    /**
     * @return array
     */
    public function getCustomersId()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $customerArray = [];
        if (isset($id)) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('usage_id', ['eq' => $id]);
            foreach ($collection as $item) {
                $customerArray[$item->getCustomerId()] = $item->getCustomerId();
            }
        }
        return $customerArray;
    }
}