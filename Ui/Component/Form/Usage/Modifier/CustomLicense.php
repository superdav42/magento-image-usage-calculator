<?php

namespace DevStone\UsageCalculator\Ui\Component\Form\Usage\Modifier;

use DevStone\UsageCalculator\Api\Data\UsageCustomerInterface;
use DevStone\UsageCalculator\Api\UsageRepositoryInterface;
use DevStone\UsageCalculator\Model\ResourceModel\UsageCustomer\CollectionFactory;
use DevStone\UsageCalculator\Model\Usage\CatagoriesOptionsProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Class CustomLicense
 * @package DevStone\UsageCalculator\Ui\Component\Form\Usage\Modifier
 */
class CustomLicense implements ModifierInterface
{
    const FIELD_NAME = 'max_usage';

    public function __construct(protected RequestInterface $request, protected CatagoriesOptionsProvider $categoriesOptionsProvider, protected UsageRepositoryInterface $usageRepository, protected CollectionFactory $usageCustomerCollectionFactory)
    {
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    #[\Override]
    public function modifyData(array $data)
    {
        foreach ($data as &$item) {
            $customLicense = $this->request->getParam('custom_license');
            if (isset($item['entity_id']) && isset($customLicense) && $customLicense) {
                try {
                    $usage = $this->usageRepository->getById($item['entity_id']);
                    if ($usage->getId()) {
                        $usageCustomers = $this->usageCustomerCollectionFactory->create()
                            ->addFieldToFilter('usage_id', ['eq' => $usage->getId()]);
                        if ($usageCustomers->getSize()) {
                            /** @var UsageCustomerInterface $usageCustomer */
                            foreach ($usageCustomers->getItems() as $usageCustomer) {
                                if ($usageCustomer->getPendingCustomerEmail() !== "" && !$usageCustomer->getCustomerId()) {
                                    $item['pending_customer_emails'][] = [
                                        'email' => $usageCustomer->getPendingCustomerEmail()
                                    ];
                                }
                            }
                        }
                    }
                } catch (LocalizedException) {

                }
            }
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    #[\Override]
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
                                            'options' => $this->categoriesOptionsProvider->customLicenseOption()
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
                                            'options' => $this->categoriesOptionsProvider->allOptionsExcludingCustomLicense()
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
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_NAME,
                        'dataType' => Number::NAME,
                        'sortOrder' => 50,
                    ],
                ],
            ],
        ];
    }
}
