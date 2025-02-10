<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mageplaza\PdfInvoice\Helper\Data;

/**
 * Class Preview
 * @package Mageplaza\PdfInvoice\Block\Adminhtml\System\Config
 */
class Preview extends Field
{
    /**
     * @var string
     */
    protected $_buttonLabel = '';

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Load constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }


    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Mageplaza_PdfInvoice::config/preview.phtml');
    }

    /**
     * Get the button html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel  = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_buttonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id'      => $element->getHtmlId()
            ]
        );

        return $element->getElementHtml() . $this->_toHtml();
    }

    /**
     * Get Pdf Templates Config
     *
     * @param $type
     *
     * @return mixed
     */
    public function getPdfTemplate($type)
    {
        return $this->helperData->getPdfTemplate($type);
    }

    /**
     * Get Templates
     *
     * @param $type
     *
     * @return array
     */
    public function getTemplateList($type)
    {
        return $this->helperData->getTemplates($type);
    }

    /**
     * Get preview template  url
     * @return string
     */
    public function getPreviewTemplateUrl()
    {
        return $this->getUrl('pdfinvoice/template/preview');
    }
}
