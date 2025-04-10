<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block\Adminhtml\Wallet\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Webkul Walletsystem Class
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var template
     */
    protected $_template = 'Webkul_Walletsystem::tab/walletamount.phtml';
    /**
     * @var BlockGrid
     */
    protected $blockGrid;

    /**
     * @var \Webkul\Walletsystem\Helper\DataData
     */
    protected $helper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Walletsystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_formFactory = $formFactory;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Wallet Amount');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Wallet Amount');
    }

    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }

    /**
     * Can show tab in tabs.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Create form of edit tab
     *
     * @return $this
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_wallet');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Adjust amount to Wallet')]
        );
        $fieldset->addField(
            'walletamount',
            'text',
            [
                'label' => __('Enter amount'),
                'name' => 'walletamount',
            ]
        );
        $fieldset->addField(
            'walletactiontype',
            'select',
            [
                'label' => __('Action want to perform on amount'),
                'title' => __('Action want to perform on amount'),
                'name' => 'walletactiontype',
                'options' => ['credit' => __('Credit Amount'), 'debit' => __('Debit Amount')]
            ]
        );
        $fieldset->addField(
            'walletnote',
            'text',
            [
                'label' => __('Note for the transaction'),
                'name' => 'walletnote',
            ]
        );
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        return $this;
    }

    /**
     * To Html function
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    /**
     * Get Block Grid function
     *
     * @return object
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Webkul\Walletsystem\Block\Adminhtml\Wallet\Edit\Tab\Grid::class,
                'walletcustomergrid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block.
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * Get Json Helper
     *
     * @param array $formData
     * @return object
     */
    public function getJsonHelper($formData)
    {
        return $this->helper->getJsonHelper()->jsonEncode($formData);
    }
}
