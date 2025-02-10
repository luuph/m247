<?php

namespace Biztech\Translator\Block\Adminhtml\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * This block is frontend model for calculateing charactors
 */
class Calculatechar extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Biztech_Translator::system/config/calculatechar.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for Calculate Charater Count button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('translator/cron/calculatechar');
    }

    /**
     * Generate Calculate Charater Count button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'calculate_char',
                'label' => __('Calculate Character Count'),
            ]
        );

        return $button->toHtml();
    }
}
