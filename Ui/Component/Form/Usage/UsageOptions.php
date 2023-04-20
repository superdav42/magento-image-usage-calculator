<?php

namespace DevStone\UsageCalculator\Ui\Component\Form\Usage;

use DevStone\UsageCalculator\Api\UsageCustomOptionRepositoryInterface;
use DevStone\UsageCalculator\Model\Usage\SizesOptionsProvider;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Currency;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\HtmlContent;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Data provider for "Usage Options" panel
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UsageOptions extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    const DATA_SOURCE_DEFAULT = 'usage';

    /**#@+
     * Group values
     */
    const GROUP_CUSTOM_OPTIONS_NAME = 'custom_options';
    const GROUP_CUSTOM_OPTIONS_SCOPE = 'usage';
    const GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME = 'search-engine-optimization';
    const GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER = 31;
    /**#@-*/

    /**#@+
     * Button values
     */
    const BUTTON_ADD = 'button_add';
    const BUTTON_IMPORT = 'button_import';
    /**#@-*/

    /**#@+
     * Container values
     */
    const CONTAINER_HEADER_NAME = 'container_header';
    const CONTAINER_OPTION = 'container_option';
    const CONTAINER_COMMON_NAME = 'container_common';
    const CONTAINER_TYPE_STATIC_NAME = 'container_type_static';
    /**#@-*/

    /**#@+
     * Grid values
     */
    const GRID_OPTIONS_NAME = 'options';
    const GRID_TYPE_SELECT_NAME = 'values';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_ENABLE = 'affect_usage_custom_options';
    const FIELD_OPTION_ID = 'option_id';
    const FIELD_TITLE_NAME = 'title';
    const FIELD_HELP_NAME = 'help';
    const FIELD_STORE_TITLE_NAME = 'store_title';
    const FIELD_TYPE_NAME = 'type';
    const FIELD_IS_REQUIRE_NAME = 'is_require';
    const FIELD_SORT_ORDER_NAME = 'sort_order';
    const FIELD_PRICE_NAME = 'price';
    const FIELD_PRICE_TYPE_NAME = 'price_type';
    const FIELD_IMAGE_SIZE = 'size_id';
    const FIELD_IS_DELETE = 'is_delete';
    const FIELD_IS_USE_DEFAULT = 'is_use_default';
    /**#@-*/

    /**#@+
     * Import options values
     */
    const IMPORT_OPTIONS_MODAL = 'import_options_modal';
    const CUSTOM_OPTIONS_LISTING = 'product_custom_options_listing';
    /**#@-*/

    protected LocatorInterface $locator;
    protected StoreManagerInterface $storeManager;
    protected ProductOptionsPrice $productOptionsPrice;
    protected UrlInterface $urlBuilder;
    protected ArrayManager $arrayManager;
    protected array $meta = [];
    protected CurrencyInterface $localeCurrency;
    protected UsageCustomOptionRepositoryInterface $optionRepository;
    protected SizesOptionsProvider $sizesOptionsProvider;

    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ProductOptionsPrice $productOptionsPrice,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        UsageCustomOptionRepositoryInterface $optionRepo,
        SizesOptionsProvider $sizesOptionsProvider,
        CurrencyInterface $localeCurrency
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->productOptionsPrice = $productOptionsPrice;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->optionRepository = $optionRepo;
        $this->sizesOptionsProvider = $sizesOptionsProvider;
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        foreach ($data as $id => &$usage) {
            $options = [];
            $productOptions = $this->optionRepository->getList($id);
            /** @var \DevStone\UsageCalculator\Model\Usage\Option $option */
            foreach ($productOptions as $index => $option) {
                $optionData = $option->getData();
                $optionData[static::FIELD_IS_USE_DEFAULT] = !$option->getData(static::FIELD_STORE_TITLE_NAME);
                $options[$index] = $this->formatPriceByPath(static::FIELD_PRICE_NAME, $optionData);
                $values = $option->getValues() ?: [];

                foreach ($values as $value) {
                    $value->setData(static::FIELD_IS_USE_DEFAULT, !$value->getData(static::FIELD_STORE_TITLE_NAME));
                }
                /** @var Option $value */
                foreach ($values as $value) {
                    $options[$index][static::GRID_TYPE_SELECT_NAME][] = $this->formatPriceByPath(
                        static::FIELD_PRICE_NAME,
                        $value->getData()
                    );
                }
            }

            $usage[static::DATA_SOURCE_DEFAULT] = [
                static::FIELD_ENABLE => 1,
                static::GRID_OPTIONS_NAME => $options,
            ];
        }

        return $data;
    }

    /**
     * Format float number to have two digits after delimiter
     *
     * @param string $path
     * @param array $data
     * @return array
     */
    protected function formatPriceByPath($path, array $data)
    {
        $value = $this->arrayManager->get($path, $data);

        if (is_numeric($value)) {
            $data = $this->arrayManager->replace($path, $data, $this->formatPrice($value));
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->createCustomOptionsPanel();

        return $this->meta;
    }

    /**
     * Create "Customizable Options" panel
     *
     * @return $this
     * @since 101.0.0
     */
    protected function createCustomOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::GROUP_CUSTOM_OPTIONS_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Usage Options'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible' => false,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                            ],
                        ],
                    ],
                    'children' => [
                        'preview' => $this->getPreviewPane(5),
                        static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),

                        static::FIELD_ENABLE => $this->getEnableFieldConfig(20),
                        static::GRID_OPTIONS_NAME => $this->getOptionsGridConfig(30),

                    ]
                ]
            ]
        );

        return $this;
    }

    protected function getPreviewPane($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => 'leel',
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,

                        'component' => 'DevStone_UsageCalculator/js/components/preview',
                        'template' => 'DevStone_UsageCalculator/form/components/preview',
                        'sortOrder' => $sortOrder,
                        'content' => __('Preview.'),
                    ],
                ],
            ],
//            'children' => [
//                'body' => [
//                    'arguments' => [
//                        'data' => [
//                            'config' => [
//
//                            ]
//                        ]
//                    ]
//                ]
//            ]
        ];
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Usage options provide more details about how an image will be used.'),
                    ],
                ],
            ],
            'children' => [
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Option'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => '${ $.ns }.${ $.ns }.' . static::GROUP_CUSTOM_OPTIONS_NAME
                                            . '.' . static::GRID_OPTIONS_NAME,
                                        '__disableTmpl' => ['targetName' => false],
                                        'actionName' => 'processingAddChild',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
//                'preview' => [
//                    'arguments' => [
//                        'data' => [
//                            'config' => [
//                                'label' => 'leel',
//                                'formElement' => Container::NAME,
//                                'componentType' => Container::NAME,
//                                'template' => 'ui/form/components/complex',
//                                'sortOrder' => $sortOrder,
//                                'content' => __('Preview.'),
//                            ],
//                        ],
//                    ],
//                ],
            ],
        ];
    }

    /**
     * Get config for the whole grid
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getOptionsGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Option'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'dataProvider' => static::CUSTOM_OPTIONS_LISTING,
                        'imports' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Option'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'collapsible' => true,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(40),
                                static::CONTAINER_COMMON_NAME => $this->getCommonContainerConfig(10),
                                static::CONTAINER_TYPE_STATIC_NAME => $this->getStaticTypeContainerConfig(20),
                                static::GRID_TYPE_SELECT_NAME => $this->getSelectTypeGridConfig(30)
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for hidden field responsible for enabling custom options processing
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getEnableFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_ENABLE,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for container with common fields for any type
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getCommonContainerConfig($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_OPTION_ID => $this->getOptionIdFieldConfig(10),
                static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                    20,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Option Title'),
                                    'component' => 'Magento_Catalog/component/static-type-input',
                                    'valueUpdate' => 'input',
                                    'imports' => [
                                        'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                                        'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default'
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
                static::FIELD_HELP_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Help Text'),
                                'component' => 'Magento_Catalog/component/static-type-input',
                                'componentType' => Field::NAME,
                                'formElement' => Input::NAME,
                                'dataScope' => static::FIELD_HELP_NAME,
                                'dataType' => Text::NAME,
                                'sortOrder' => 21,
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'imports' => [
                                    'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                                    'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default'
                                ]
                            ],
                        ],
                    ],
                ],
                static::FIELD_TYPE_NAME => $this->getTypeFieldConfig(30),
                static::FIELD_IS_REQUIRE_NAME => $this->getIsRequireFieldConfig(40)
            ]
        ];

        return $commonContainer;
    }

    /**
     * Get config for container with fields for all types except "select"
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getStaticTypeContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                        'fieldTemplate' => 'Magento_Catalog/form/field',
                        'visible' => false,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_PRICE_NAME => $this->getPriceFieldConfig(10),
                static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(20),
            ]
        ];
    }

    /**
     * Get config for grid for "select" types
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getSelectTypeGridConfig($sortOrder)
    {
        $options = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'imports' => [
                            'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                            'optionTypeId' => '${ $.provider }:${ $.parentScope }.option_type_id',
                            'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default'
                        ],
                        'service' => [
                            'template' => 'Magento_Catalog/form/element/helper/custom-option-type-service',
                        ],
                    ],
                ],
            ],
        ];

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Value'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                            10,
                            []
                        ),
                        static::FIELD_PRICE_NAME => $this->getPriceFieldConfigForSelectType(20),
                        static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                        static::FIELD_IMAGE_SIZE => $this->getImageSizeFieldConfig(40),
                        static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(50),
                        static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(60)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for hidden id field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getOptionIdFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_OPTION_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Title" fields
     *
     * @param int $sortOrder
     * @param array $options
     * @return array
     * @since 101.0.0
     */
    protected function getTitleFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_TITLE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * Get config for "Option Type" field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getTypeFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Option Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'component' => 'Magento_Catalog/js/custom-options-type',
                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                        'selectType' => 'optgroup',
                        'dataScope' => static::FIELD_TYPE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->getProductOptionTypes(),
                        'disableLabel' => true,
                        'multiple' => false,
                        'selectedPlaceholders' => [
                            'defaultPlaceholder' => __('-- Please select --'),
                        ],
                        'validation' => [
                            'required-entry' => true
                        ],
                        'groupsConfig' => [
                            'text' => [
                                'values' => ['field', 'area'],
                                'indexes' => [
                                    static::CONTAINER_TYPE_STATIC_NAME,
                                    static::FIELD_PRICE_NAME,
                                    static::FIELD_PRICE_TYPE_NAME
                                ]
                            ],
                            'select' => [
                                'values' => ['drop_down', 'radio', 'checkbox', 'multiple'],
                                'indexes' => [
                                    static::GRID_TYPE_SELECT_NAME
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Required" field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getIsRequireFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Required'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_IS_REQUIRE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '1',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for sorting
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for removing rows
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getIsDeleteFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price" field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getPriceFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price'),
                        'componentType' => Field::NAME,
                        'component' => 'Magento_Catalog/js/components/custom-options-component',
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_NAME,
                        'dataType' => Number::NAME,
                        'addbefore' => $this->getCurrencySymbol(),
                        'addbeforePool' => $this->productOptionsPrice->prefixesToOptionArray(),
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-zero-or-greater' => true
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price" field for select type.
     *
     * @param int $sortOrder
     * @return array
     */
    private function getPriceFieldConfigForSelectType($sortOrder)
    {
        $priceFieldConfig = $this->getPriceFieldConfig($sortOrder);
        $priceFieldConfig['arguments']['data']['config']['template'] = 'Magento_Catalog/form/field';

        return $priceFieldConfig;
    }

    /**
     * Get config for "Price Type" field
     *
     * @param int $sortOrder
     * @param array $config
     * @return array
     * @since 101.0.0
     */
    protected function getPriceTypeFieldConfig($sortOrder, array $config = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Price Type'),
                            'component' => 'Magento_Catalog/js/components/custom-options-price-type',
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => static::FIELD_PRICE_TYPE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => $this->productOptionsPrice->toOptionArray(),
                            'imports' => [
                                'priceIndex' => self::FIELD_PRICE_NAME,
                            ],
                        ],
                    ],
                ],
            ],
            $config
        );
    }

    protected function getImageSizeFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Image Size'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_IMAGE_SIZE,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->sizesOptionsProvider->toOptionArray('---'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get options for drop-down control with product option types
     *
     * @return array
     * @since 101.0.0
     */
    protected function getProductOptionTypes()
    {
        return  [
            [
                'value' => 0,
                'label' => 'Text',
                'optgroup' => [
                    [
                        'label' => 'Field',
                        'value' => 'field',
                    ],
                    [
                        'label' => 'Area',
                        'value' => 'area',
                    ],
                ],
            ],
            [
                'value' => 2,
                'label' => 'Select',
                'optgroup' => [
                    [
                        'label' => 'Drop-down',
                        'value' => 'drop_down',
                    ],
                    [
                        'label' => 'Radio Buttons',
                        'value' => 'radio',
                    ],
                    [
                        'label' => 'Checkbox',
                        'value' => 'checkbox',
                    ],
                    [
                        'label' => 'Multiple Select',
                        'value' => 'multiple',
                    ],
                ],
            ],

        ];
    }

    /**
     * Get currency symbol
     *
     * @return string
     * @since 101.0.0
     */
    protected function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }


    /**
     * Format price according to the locale of the currency
     *
     * @param mixed $value
     * @return string
     * @since 101.0.0
     */
    protected function formatPrice($value)
    {
        if (!is_numeric($value)) {
            return null;
        }
        try {
            $store = $this->storeManager->getStore();
            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
            $value = $currency->toCurrency($value, ['display' => Currency::NO_SYMBOL]);
        } catch (\Exception $e) {
            return parent::formatPrice($value);
        }

        return $value;
    }
}
