<?php

namespace Appristine\LocalShipping\Block\Adminhtml\System\Config;


class Export extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        /**
         * @var \Magento\Backend\Block\Widget\Button $buttonBlock  
        */
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
        $params = ['website' => $buttonBlock->getRequest()->getParam('website')];
        $url = $this->_backendUrl->getUrl("localshipping/export/exportRatesCsv", $params);
        $data = [
            'label'     => __('Export CSV'),
            'onclick'    => "setLocation('".$url."' )",
            'class'        => '',
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }
}
