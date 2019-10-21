<?php

namespace DevStone\UsageCalculator\Block\Adminhtml\Usage\Edit;

/**
 * Class CustomLicense
 * @package DevStone\UsageCalculator\Block\Adminhtml\Usage\Edit
 */
class CustomLicense implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * CustomLicense constructor.
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Add Custom License'),
            'on_click' => sprintf("location.href = '%s';", $this->getCustomLicense(['custom_license' => true])),
            'class' => 'save primary',
            'sort_order' => 10
        ];
    }

    /**
     * @param $parms
     * @return mixed
     */
    public function getCustomLicense($parms)
    {
        return $this->urlBuilder->getUrl('*/*/edit', $parms);
    }
}
