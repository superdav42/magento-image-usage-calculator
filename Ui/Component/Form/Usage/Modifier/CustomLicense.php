<?php

namespace DevStone\UsageCalculator\Ui\Component\Form\Usage\Modifier;

/**
 * Class CustomLicense
 * @package DevStone\UsageCalculator\Ui\Component\Form\Usage\Modifier
 */
class CustomLicense implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
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
        if (isset($customLicense) && $customLicense) {
            $meta['main_fieldset']['children']['category_id']['arguments']['data']['config']['options'] =
                $this->catagoriesOptionsProvider->customLicenseOption();
        } else {
            $meta['main_fieldset']['children']['category_id']['arguments']['data']['config']['options'] =
                $this->catagoriesOptionsProvider->allOptionsExcludingCustomLicense();
        }
        return $meta;
    }
}
