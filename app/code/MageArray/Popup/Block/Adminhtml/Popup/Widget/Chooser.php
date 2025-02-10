<?php
namespace MageArray\Popup\Block\Adminhtml\Popup\Widget;

/**
 * Class Chooser
 * @package MageArray\Popup\Block\Adminhtml\Popup\Widget
 */

class Chooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var \MageArray\Popup\Model\PopupFactory
     */
    protected $_popupFactory;
    /**
     * @var \MageArray\Popup\Model\Status
     */
    protected $_status;

    /**
     * Chooser constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MageArray\Popup\Model\Status $status
     * @param \MageArray\Popup\Model\PopupFactory $popupFactory
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MageArray\Popup\Model\Status $status,
        \MageArray\Popup\Model\PopupFactory $popupFactory,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_status = $status;
        $this->_popupFactory = $popupFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('block_identifier');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_is_active' => '1']);
    }
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('cms/block_widget/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            //'MageArray\Popup\Block\Adminhtml\Widget\Chooser'
            \MageArray\Popup\Block\Adminhtml\Popup\Widget\Chooser::Class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $block = $this->_popupFactory->create()->load($element->getValue());
            if ($block->getPopupId()) {
                $chooser->setLabel($this->escapeHtml($block->getTitle()));
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var blockId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                var blockTitle = trElement.down("td").next().innerHTML;
                ' .
            $chooserJsObject .
            '.setElementValue(blockId);
                ' .
            $chooserJsObject .
            '.setElementLabel(blockTitle);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = $this->_popupFactory->create()->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'popup_id',
            ['header' => __('ID'), 'align' => 'right', 'index' => 'popup_id', 'width' => 50]
        );

        $this->addColumn('title', ['header' => __('Title'), 'align' => 'left', 'index' => 'title']);

        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => [0 => __('Disabled'), 1 => __('Enabled')]
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return mixed
     */
    public function getGridUrl()
    {
        return $this->getUrl('cms/block_widget/chooser', ['_current' => true]);
    }
}
