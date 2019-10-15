<?php

namespace DevStone\UsageCalculator\Model\Usage\Option;

use DevStone\UsageCalculator\Api\Data\UsageInterface;
use DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterface;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Repository implements \DevStone\UsageCalculator\Api\UsageCustomOptionRepositoryInterface
{
    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\Product\Usage\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \DevStone\UsageCalculator\Model\Usage\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var \DevStone\UsageCalculator\Api\UsageRepositoryInterface
     */
    protected $usageRepository;

    /**
     * @var \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option
     */
    protected $optionResource;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var HydratorPool
     */
    protected $hydratorPool;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * Constructor
     *
     * @param \DevStone\UsageCalculator\Api\UsageRepositoryInterface $usageRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option $optionResource
     * @param Converter $converter
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory|null $collectionFactory
     * @param \Magento\Catalog\Model\Product\OptionFactory|null $optionFactory
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     */
    public function __construct(
        \DevStone\UsageCalculator\Api\UsageRepositoryInterface $usageRepository,
        \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option $optionResource,
        \DevStone\UsageCalculator\Model\Usage\Option\Converter $converter,
        \DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\CollectionFactory $collectionFactory = null,
        \DevStone\UsageCalculator\Model\Usage\OptionFactory $optionFactory = null,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null
    ) {
        $this->usageRepository = $usageRepository;
        $this->optionResource = $optionResource;
        $this->converter = $converter;
        $this->collectionFactory = $collectionFactory ?: ObjectManager::getInstance()
            ->get(\DevStone\UsageCalculator\Model\ResourceModel\Usage\Option\CollectionFactory::class);
        $this->optionFactory = $optionFactory ?: ObjectManager::getInstance()
            ->get(\DevStone\UsageCalculator\Model\Usage\OptionFactory::class);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\EntityManager\MetadataPool::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($id)
    {
        $usage = $this->usageRepository->getById($id);
        return $usage->getOptions() ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function getUsageOptions(UsageInterface $usage, $requiredOnly = false)
    {
        return $this->collectionFactory->create()->getUsageOptions(
            $usage->getEntityId(),
            $usage->getStoreId(),
            $requiredOnly
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $optionId)
    {
        $product = $this->usageRepository->getById($sku);
        $option = $product->getOptionById($optionId);
        if ($option === null) {
            throw NoSuchEntityException::singleField('optionId', $optionId);
        }
        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(UsageCustomOptionInterface $entity)
    {
        $this->optionResource->delete($entity);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(
        UsageInterface $product,
        UsageInterface $duplicate
    ) {
        $hydrator = $this->getHydratorPool()->getHydrator(UsageInterface::class);
        $metadata = $this->metadataPool->getMetadata(UsageInterface::class);
        return $this->optionResource->duplicate(
            $this->optionFactory->create([]),
            $hydrator->extract($product)[$metadata->getLinkField()],
            $hydrator->extract($duplicate)[$metadata->getLinkField()]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(UsageCustomOptionInterface $option)
    {
        $usageId = $option->getUsageId();
        if (!$usageId) {
            throw new CouldNotSaveException(__('usage_id should be specified'));
        }
        /** @var \DevStone\UsageCalculator\Model\Usage $usage */
        $usage = $this->usageRepository->getById($usageId);
        $option->setData('usage_id', $usageId);
        $option->setData('store_id', $usage->getStoreId());

        if ($option->getOptionId()) {
            $options = $usage->getOptions();
            if (!$options) {
                $options = $this->getUsageOptions($usage);
            }

            $persistedOption = array_filter($options, function ($iOption) use ($option) {
                return $option->getOptionId() == $iOption->getOptionId();
            });
            $persistedOption = reset($persistedOption);

            if (!$persistedOption) {
                throw new NoSuchEntityException();
            }
            $originalValues = $persistedOption->getValues();
            $newValues = $option->getData('values');
            if ($newValues) {
                if (isset($originalValues)) {
                    $newValues = $this->markRemovedValues($newValues, $originalValues);
                }
                $option->setData('values', $newValues);
            }
        }
        $option->save();
        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdentifier($sku, $optionId)
    {
        $product = $this->usageRepository->getById($sku);
        $options = $product->getOptions();
        $option = $product->getOptionById($optionId);
        if ($option === null) {
            throw NoSuchEntityException::singleField('optionId', $optionId);
        }
        unset($options[$optionId]);
        try {
            $this->delete($option);
            if (empty($options)) {
                $this->usageRepository->save($product);
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not remove custom option'));
        }
        return true;
    }

    /**
     * Mark original values for removal if they are absent among new values
     *
     * @param $newValues array
     * @param $originalValues \Magento\Catalog\Model\Product\Option\Value[]
     * @return array
     */
    protected function markRemovedValues($newValues, $originalValues)
    {
        $existingValuesIds = [];

        foreach ($newValues as $newValue) {
            if (array_key_exists('option_type_id', $newValue)) {
                $existingValuesIds[] = $newValue['option_type_id'];
            }
        }
        /** @var $originalValue \Magento\Catalog\Model\Product\Option\Value */
        foreach ($originalValues as $originalValue) {
            if (!in_array($originalValue->getData('option_type_id'), $existingValuesIds)) {
                $originalValue->setData('is_delete', 1);
                $newValues[] = $originalValue->getData();
            }
        }

        return $newValues;
    }

    /**
     * @return \Magento\Framework\EntityManager\HydratorPool
     * @deprecated 101.0.0
     */
    private function getHydratorPool()
    {
        if (null === $this->hydratorPool) {
            $this->hydratorPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\HydratorPool::class);
        }
        return $this->hydratorPool;
    }
}
