<?php

namespace DevStone\UsageCalculator\Controller\Adminhtml\Usage\Initialization;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;

class Helper
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;




    /**
     * @var CustomOptionFactory
     */
    protected $customOptionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterfaceFactory $customOptionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \DevStone\UsageCalculator\Api\Data\UsageCustomOptionInterfaceFactory $customOptionFactory
    ) {
        $this->request = $request;
        $this->customOptionFactory = $customOptionFactory;        
    }

    /**
     * Initialize usage from data
     *
     * @param \DevStone\UsageCalculator\Model\Usage $usage
     * @param array $usageData
     * @return \DevStone\UsageCalculator\Model\Usage
     */
    public function initializeFromData(
            \DevStone\UsageCalculator\Model\Usage $usage, 
            array $usageData
    ) {
        

//        $usageData = $this->normalize($usageData);

        if (isset($usageData['options'])) {
            $usageOptions = $usageData['options'];
            unset($usageData['options']);
        } else {
            $usageOptions = [];
        }
        

        $usage = $this->fillProductOptions($usage, $usageOptions);

        $usage->setCanSaveCustomOptions(
            !empty($usageData['affect_usage_custom_options']) && !$usage->getOptionsReadonly()
        );

        return $usage;
    }

    /**
     * Initialize usage before saving
     *
     * @param \DevStone\UsageCalculator\Model\Usage $usage
     * @return \DevStone\UsageCalculator\Model\Usage
     */
    public function initialize( \DevStone\UsageCalculator\Model\Usage $usage)
    {
        $usageData = $this->request->getPost('usage', []);
        return $this->initializeFromData($usage, $usageData);
    }

    /**
     * Internal normalization
     *
     * @param array $productData
     * @return array
     * @todo Remove this method
     * @since 101.0.0
     */
    protected function normalize(array $productData)
    {
        foreach ($productData as $key => $value) {
            if (is_scalar($value)) {
                if ($value === 'true') {
                    $productData[$key] = '1';
                } elseif ($value === 'false') {
                    $productData[$key] = '0';
                }
            } elseif (is_array($value)) {
                $productData[$key] = $this->normalize($value);
            }
        }

        return $productData;
    }

    /**
     * Merge product and default options for product
     *
     * @param array $productOptions product options
     * @param array $overwriteOptions default value options
     * @return array
     */
    public function mergeProductOptions($productOptions, $overwriteOptions)
    {
        if (!is_array($productOptions)) {
            return [];
        }

        if (!is_array($overwriteOptions)) {
            return $productOptions;
        }

        foreach ($productOptions as $optionIndex => $option) {
            $optionId = $option['option_id'];
            $option = $this->overwriteValue($optionId, $option, $overwriteOptions);

            if (isset($option['values']) && isset($overwriteOptions[$optionId]['values'])) {
                foreach ($option['values'] as $valueIndex => $value) {
                    if (isset($value['option_type_id'])) {
                        $valueId = $value['option_type_id'];
                        $value = $this->overwriteValue($valueId, $value, $overwriteOptions[$optionId]['values']);
                        $option['values'][$valueIndex] = $value;
                    }
                }
            }

            $productOptions[$optionIndex] = $option;
        }

        return $productOptions;
    }

    /**
     * Overwrite values of fields to default, if there are option id and field name in array overwriteOptions
     *
     * @param int $optionId
     * @param array $option
     * @param array $overwriteOptions
     * @return array
     */
    private function overwriteValue($optionId, $option, $overwriteOptions)
    {
        if (isset($overwriteOptions[$optionId])) {
            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_' . $fieldName])) {
                    $option[$fieldName] = $option['default_' . $fieldName];
                    if ('title' == $fieldName) {
                        $option['is_delete_store_title'] = 1;
                    }
					if ('help' == $fieldName) {
                        $option['is_delete_store_help'] = 1;
                    }
                }
            }
        }

        return $option;
    }


    /**
     * Fills $product with options from $productOptions array
     *
     * @param \DevStone\UsageCalculator\Model\Usage $usage
     * @param array $usageOptions
     * @return \DevStone\UsageCalculator\Model\Usage
     */
    private function fillProductOptions(
        \DevStone\UsageCalculator\Model\Usage $usage, 
        array $usageOptions
    ) {
        if ($usage->getOptionsReadonly()) {
            return $product;
        }

        if (empty($usageOptions)) {
            return $usage->setOptions([]);
        }

        // mark custom options that should to fall back to default value
        $options = $this->mergeProductOptions(
            $usageOptions,
            $this->request->getPost('options_use_default')
        );
        $customOptions = [];
        foreach ($options as $customOptionData) {
            if (!empty($customOptionData['is_delete'])) {
                continue;
            }

            if (empty($customOptionData['option_id'])) {
                $customOptionData['option_id'] = null;
            }

            if (isset($customOptionData['values'])) {
                $customOptionData['values'] = array_filter($customOptionData['values'], function ($valueData) {
                    return empty($valueData['is_delete']);
                });
            }

            $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
            $customOption->setUsageId($usage->getId());
            $customOptions[] = $customOption;
        }

        return $usage->setOptions($customOptions);
    }
}
