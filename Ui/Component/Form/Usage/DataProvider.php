<?php
/**
 * DataProvider
 *
 * @copyright Copyright © 2018 DevStone. All rights reserved.
 * @author    david@nnucomputerwhiz.com
 */
namespace DevStone\UsageCalculator\Ui\Component\Form\Usage;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\DataProvider\AbstractDataProvider;
use DevStone\UsageCalculator\Model\ResourceModel\Usage\Collection;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;
    
    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UsageOptions
     */
    protected $usageOptions;
    
    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param FilterPool $filterPool
     * @param RequestInterface $request
     * @param UsageOptions $usageOptions
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        FilterPool $filterPool,
        RequestInterface $request,
        UsageOptions $usageOptions,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->filterPool = $filterPool;
        
        $this->request = $request;
        $this->usageOptions = $usageOptions;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->loadedData) {
            $this->loadedData = [];
            $storeId = (int)$this->request->getParam('store');
            $this->collection
                ->setStoreId($storeId)
                ->addAttributeToSelect('*');
            $items = $this->collection->getItems();
            foreach ($items as $item) {
                $item->setStoreId($storeId);
                $this->loadedData[$item->getId()] = $item->getData();
                break;
            }
            
            $this->loadedData = $this->usageOptions->modifydata($this->loadedData);
        }
        return $this->loadedData;
    }

    public function getMeta() 
    {
        $meta = parent::getMeta();
        
        return $this->usageOptions->modifyMeta($meta);
    }

}
