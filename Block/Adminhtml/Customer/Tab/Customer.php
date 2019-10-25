<?php
/**
 * Customer
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */

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
     * @var \DevStone\UsageCalculator\Model\ResourceModel\Customer\CollectionFactory|\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

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

    protected $resource;

    /**
     * Customer constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
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
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\RequestInterface $request,
        \DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory $collectionFactory,
        \DevStone\UsageCalculator\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        array $data = [],
        Visibility $visibility = null,
        Status $status = null
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
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
        $usageId = $this->getRequest()->getParam('entity_id');
        isset($usageId) ? $this->setDefaultFilter(['in_usage' => 1]) : $this->setDefaultFilter(['in_usage' => '']);
        $this->setUseAjax(true);
    }

    /**
     * @param Grid\Column $column
     * @return $this|Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_usage') {
            $customerIds = $this->_getSelectedCustomer();
            if (empty($customerIds)) {
                $customerIds = 0;
            }
            $linkField = 'entity_id';
            $filter = $column->getFilter();
            if ($filter !== false && $column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter($linkField, ['in' => $customerIds]);
            } elseif (!empty($customerIds)) {
                $this->getCollection()->addFieldToFilter($linkField, ['nin' => $customerIds]);
            }
        } elseif ($column->getId() == 'name') {
            $this->getCollection()
                ->getSelect()
                ->where("CONCAT(e.firstname, ' ', e.lastname) like '%" . $column->getFilter()->getValue() . "%'");
        } elseif ($column->getId() == 'company') {
            $this->getCollection()
                ->getSelect()
                ->where('company like "%' . $column->getFilter()->getValue() . '%"');
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $collection = $this->customerCollectionFactory->create();
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

        // Customer Email Column
        $this->addColumn(
            'email',
            [
                'type' => 'text',
                'name' => 'email',
                'header' => __('Email'),
                'index' => 'email'
            ]
        );

        //Customer Name Column
        $this->addColumn(
            'name',
            [
                'type' => 'text',
                'name' => 'name',
                'header' => __('Name'),
                'index' => 'name'
            ]
        );

        //Customer Company Column
        $this->addColumn(
            'company',
            [
                'type' => 'text',
                'name' => 'company',
                'header' => __('Company'),
                'index' => 'company'
            ]
        );
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