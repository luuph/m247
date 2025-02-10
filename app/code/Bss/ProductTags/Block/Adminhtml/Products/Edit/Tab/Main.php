<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Block\Adminhtml\Products\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Webspeaks\ProductsGrid\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Bss\ProductTags\Model\System\Config\Status
     */
    protected $status;

    /**
     * @var \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element
     */
    protected $renderer;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element $renderer
     * @param \Magento\Config\Model\Config\Source\Enabledisable $status
     * @param \Bss\ProductTags\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element $renderer,
        \Magento\Config\Model\Config\Source\Enabledisable $status,
        \Bss\ProductTags\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->systemStore = $systemStore;
        $this->renderer = $renderer;
        $this->status = $status;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('product_tag');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('protag_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);

        if ($model->getId()) {
            $fieldset->addField('protags_id', 'hidden', ['name' => 'protags_id']);
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->status->toOptionArray()
            ]
        );

        $fieldset->addField(
            'name_tag',
            'text',
            [
                'name' => 'name_tag',
                'label' => __('Tag'),
                'title' => __('Tag'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'tag_key',
            'text',
            [
                'name' => 'tag_key',
                'label' => __('Tag Key'),
                'title' => __('Tag Key'),
                'note' => __('Tag Key needs to be shorter than 80 characters'),
                'required' => true,
            ]
        );
        // Router Tag
        $fieldset->addField(
            'router_tag',
            'text',
            [
                'name' => 'router_tag',
                'label' => __('Customize Router'),
                'title' => __('Customize Router'),
                'note' => __('Enter the router of the product tag page that you want to customize.<br>
                              Ex: The default URL show &lt;domain&gt;/catalogtags/&lt;tag-key&gt;, so the router is "catalogtags"'),
                'required' => false
            ]
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderers = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderers);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Tag Info');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Tag Infomation');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
