<?php
namespace MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab;

use MageArray\Popup\Model\Status;

/**
 * Class Form
 * @package MageArray\Popup\Block\Adminhtml\Popup\Edit\Tab
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    /**
     * @var Status
     */
    protected $_status;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param Status $status
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \MageArray\Popup\Model\Status $status
    ) {
        $this->_localeDate = $context->getLocaleDate();
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     *
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());
    }

    /**
     * @return mixed
     */
    protected function _prepareForm()
    {
        $model = $this->getPopup();
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');
        $fieldSet = $form->addFieldset('base_fieldset', ['legend' => __('Popup Information')]);

        if ($model->getId()) {
            $fieldSet->addField('popup_id', 'hidden', ['name' => 'popup_id']);
        }

        $fieldSet->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'class' => 'required-entry',
            ]
        );

        $popupType = $fieldSet->addField(
            'popup_type',
            'select',
            [
                'name' => 'popup_type',
                'label' => __('Popup Content Type'),
                'title' => __('Popup Content Type'),
                'options' => ['0' => __('Custom Content (editor)'), '1' => __('Image')]
            ]
        );

        $wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $description = $fieldSet->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'class' => 'required-entry',
                'config' => $wysiwygConfig
            ]
        );

        $image = $fieldSet->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'required' => true,
                'class' => 'required-entry required-file',
                'note' => 'Allow image type: jpg, jpeg, gif, png',
            ]
        );

        $url = $fieldSet->addField(
            'url',
            'text',
            [
                'name' => 'url',
                'label' => __('Url of image link'),
                'title' => __('Url of image link'),
                'note' => 'Leave empty if no link',
            ]
        );

        $fieldSet->addField(
            'width',
            'text',
            [
                'name' => 'width',
                'label' => __('Popup content width'),
                'title' => __('Popup content width'),
                'note' => 'How many px or %. Use just number and select unit in the next field. 
                Border and padding size will be added to total width.',
            ]
        );

        $fieldSet->addField(
            'width_unit',
            'select',
            [
                'name' => 'width_unit',
                'label' => __('Popup width unit'),
                'title' => __('Popup width unit'),
                'required' => true,
                'class' => 'required-entry',
                'options' => ['1' => 'Px', '2' => 'Percentage (%)'],
                'note' => 'Use px if you want fixed width. Use % if you want dynamic (responsive design for mobile).',
            ]
        );

        $fieldSet->addField(
            'max_width',
            'text',
            [
                'name' => 'max_width',
                'label' => __('Popup content max width (in px)'),
                'title' => __('Popup content max width (in px)'),
                'required' => true,
                'class' => 'required-entry',
                'value' => '800',
                'note' => 'You can limit width to max width px if % width is wider than max px.',
            ]
        );
        $page = $fieldSet->addField(
            'page',
            'select',
            [
                'name' => 'page[junior]',
                'label' => __('Show at'),
                'title' => __('Show at'),
                'onchange' => 'change(this.value)',
                'values' => [
                    [
                        'value' => 0,
                        'label' => __('...')
                    ],
                    [
                        'value' => 1,
                        'label' => __('Home page')
                    ],
                    [
                        'value' => 2,
                        'label' => __('Checkout Onepage')
                    ],
                    [
                        'value' => 3,
                        'label' => __('Cart')
                    ],
                    [
                        'value' => 4,
                        'label' => __('Specified Url')
                    ]
                ],
                'note' => 'Leave unselected (empty) if you want to show popup at all pages.',
            ]
        );

        $specifiedUrl = $fieldSet->addField(
            'specified_url',
            'text',
            [
                'name' => 'specified_url',
                'label' => __('Specified Url'),
                'title' => __('Specified Url'),
                'note' => 'Write page url (e.g. www.magento.com/contacts/). 
                Use single comma (e.g. www.magento.com/contacts/,www.magento.com/about/) to separate multiple urls.',
            ]
        );

        $fieldSet->addField(
            'specified_not_url',
            'text',
            [
                'name' => 'specified_not_url',
                'label' => __('Exclude Url'),
                'title' => __('Exclude Url'),
                'note' => 'Use if you want to exclude page or url pattern. 
                Write page url (e.g. www.magento.com/aboutus/). 
                Use single comma (e.g. www.magento.com/contacts/,www.magento.com/about/) to separate multiple urls.',
            ]
        );

        $showWhen = $fieldSet->addField(
            'show_when',
            'select',
            [
                'name' => 'show_when',
                'label' => __('Show when'),
                'title' => __('Show when'),
                'required' => true,
                'class' => 'required-entry',
                'options' => [
                    '1' => 'After page is loaded',
                    '2' => 'Define seconds after page is loaded',
                    '3' => 'Define seconds user spent on entire site',
                    '4' => 'After user uses scroller',
                    '5' => 'On click',
                    '6' => 'On hover',
                    '7' => 'Exit intent (When mouse leaves browser window)'
                ]
            ]
        );

        $secondsDelay = $fieldSet->addField(
            'seconds_delay',
            'text',
            [
                'name' => 'seconds_delay',
                'value' => '2',
                'label' => __('Seconds'),
                'title' => __('Seconds'),
                'note' => 'Write how many seconds after page loads the popup should appear.',
            ]
        );

        $totalSecondsDelay = $fieldSet->addField(
            'total_seconds_delay',
            'text',
            [
                'name' => 'total_seconds_delay',
                'value' => '2',
                'label' => __('Seconds'),
                'title' => __('Seconds'),
                'note' => 'Max 7200. After 2 hours timer automatically resets to 0 again.',
            ]
        );

        $scrollPx = $fieldSet->addField(
            'scroll_px',
            'text',
            [
                'name' => 'scroll_px',
                'value' => '50',
                'label' => __('Scrolling px'),
                'title' => __('Scrolling px'),
                'note' => 'Show popup after scrolling how many px from the top of the page.',
            ]
        );

        $clickSelector = $fieldSet->addField(
            'click_selector',
            'text',
            [
                'name' => 'click_selector',
                'label' => __('Click selector'),
                'title' => __('Click selector'),
                'note' => 'Write click selector e.g. #idName, .className, div input#idName.',
            ]
        );

        $hoverSelector = $fieldSet->addField(
            'hover_selector',
            'text',
            [
                'name' => 'hover_selector',
                'label' => __('Hover selector'),
                'title' => __('Hover selector'),
                'note' => 'Write hover selector e.g. #idName, .className, div input#idName.',
            ]
        );

        $timeFormat = $dateFormat = $this->_localeDate->getTimeFormat();
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldSet->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => __('From Date'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'disabled' => false,
                'time' => true,
                'class' => 'validate-date validate-date-range date-range-custom_theme-from',
                'note' => 'Date is in local store view time.'
            ]
        );

        $fieldSet->addField(
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => __('To Date'),
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'disabled' => false,
                'time' => true,
                'class' => 'validate-date validate-date-range date-range-custom_theme-from'
            ]
        );
        $form->setValues($model->getData());
        $this->setForm($form);

        // field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::Class
            )->addFieldMap(
                $popupType->getHtmlId(),
                $popupType->getName()
            )->addFieldMap(
                $description->getHtmlId(),
                $description->getName()
            )->addFieldMap(
                $image->getHtmlId(),
                $image->getName()
            )->addFieldMap(
                $url->getHtmlId(),
                $url->getName()
            )->addFieldMap(
                $page->getHtmlId(),
                $page->getName()
            )->addFieldMap(
                $specifiedUrl->getHtmlId(),
                $specifiedUrl->getName()
            )->addFieldDependence(
                $description->getName(),
                $popupType->getName(),
                0
            )->addFieldDependence(
                $image->getName(),
                $popupType->getName(),
                1
            )->addFieldDependence(
                $url->getName(),
                $popupType->getName(),
                1
            )->addFieldDependence(
                $specifiedUrl->getName(),
                $page->getName(),
                4
            )->addFieldMap(
                $showWhen->getHtmlId(),
                $showWhen->getName()
            )->addFieldMap(
                $secondsDelay->getHtmlId(),
                $secondsDelay->getName()
            )->addFieldMap(
                $totalSecondsDelay->getHtmlId(),
                $totalSecondsDelay->getName()
            )->addFieldMap(
                $scrollPx->getHtmlId(),
                $scrollPx->getName()
            )->addFieldMap(
                $clickSelector->getHtmlId(),
                $clickSelector->getName()
            )->addFieldMap(
                $hoverSelector->getHtmlId(),
                $hoverSelector->getName()
            )->addFieldDependence(
                $secondsDelay->getName(),
                $showWhen->getName(),
                2
            )->addFieldDependence(
                $totalSecondsDelay->getName(),
                $showWhen->getName(),
                3
            )->addFieldDependence(
                $scrollPx->getName(),
                $showWhen->getName(),
                4
            )->addFieldDependence(
                $clickSelector->getName(),
                $showWhen->getName(),
                5
            )->addFieldDependence(
                $hoverSelector->getName(),
                $showWhen->getName(),
                6
            )
        );

        return parent::_prepareForm();
    }

    /**
     * @param $fieldId
     * @param $fieldName
     * @return $this
     */
    public function addFieldMap($fieldId, $fieldName)
    {
        $this->_fields[$fieldName] = $fieldId;
        return $this;
    }

    /**
     * @param $fieldName
     * @param $fieldNameFrom
     * @param $refValues
     * @return $this
     */
    public function addFieldDependence($fieldName, $fieldNameFrom, $refValues)
    {
        if (is_array($refValues)) {
            throw new \Exception(
                'Dependency from multiple values is not implemented yet. 
                Please fix to your widget.xml'
            );
        }
        $this->_depends[$fieldName][$fieldNameFrom] = $refValues;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPopup()
    {
        return $this->_coreRegistry->registry('popup');
    }

    /**
     * @return mixed
     */
    public function getPageTitle()
    {
        return $this->getPopup()->getId() ? __(
            "Edit Popup '%1'",
            $this->escapeHtml($this->getPopup()->getTitle())
        ) : __('New Popup');
    }

    /**
     * @return mixed
     */
    public function getTabLabel()
    {
        return __('Popup Information');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('Category Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
