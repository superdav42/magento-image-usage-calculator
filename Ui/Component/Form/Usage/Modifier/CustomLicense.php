<?php

namespace DevStone\UsageCalculator\Ui\Component\Form\Usage\Modifier;

/**
 * Class CustomLicense
 * @package DevStone\UsageCalculator\Ui\Component\Form\Usage\Modifier
 */
class CustomLicense implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    const FIELD_NAME = 'max_usage';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \DevStone\UsageCalculator\Model\Usage\CatagoriesOptionsProvider
     */
    protected $catagoriesOptionsProvider;

    /**
     * CustomLicense constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \DevStone\UsageCalculator\Model\Usage\CatagoriesOptionsProvider $catagoriesOptionsProvider
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \DevStone\UsageCalculator\Model\Usage\CatagoriesOptionsProvider $catagoriesOptionsProvider
    ) {
        $this->request = $request;
        $this->catagoriesOptionsProvider = $catagoriesOptionsProvider;
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        $customLicense = $this->request->getParam('custom_license');
        $maxUsages = [];
        if (isset($customLicense) && $customLicense) {
            $meta = array_replace_recursive(
                $meta,
                [
                    'main_fieldset' => [
                        'children' => [
                            'category_id' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'options' => $this->catagoriesOptionsProvider->customLicenseOption()
                                        ]
                                    ]
                                ]
                            ],
                            'max_usage' => $this->getMaxUsage()
                        ]
                    ]
                ]
            );

        } else {
            $meta = array_replace_recursive(
                $meta,
                [
                    'main_fieldset' => [
                        'children' => [
                            'category_id' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'options' => $this->catagoriesOptionsProvider->allOptionsExcludingCustomLicense()
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }
        return $meta;
    }

    /**
     * @return array
     */
    protected function getMaxUsage()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Max Usage'),
                        'formElement' => \Magento\Ui\Component\Form\Field::NAME,
                        'componentType' => \Magento\Ui\Component\Form\Element\Input::NAME,
                        'dataScope' => static::FIELD_NAME,
                        'dataType' => \Magento\Ui\Component\Form\Element\DataType\Number::NAME,
                        'sortOrder' => 50,
                    ],
                ],
            ],
        ];
    }
}
