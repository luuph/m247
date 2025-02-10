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
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magento\Customer\Model\Customer;
use Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs\Balance\Grid;

/**
 * Class reward point
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs
 */
class RewardPoint extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var Customer
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * RewardPoint constructor.
     *
     * @param Context $context
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param Store $systemStore
     * @param Customer $customerRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        Store $systemStore,
        Customer $customerRepository,
        array $data = []
    ) {
        $this->context            = $context;
        $this->formFactory        = $formFactory;
        $this->registry           = $registry;
        $this->systemStore        = $systemStore;
        $this->customerRepository = $customerRepository;
        $this->authorization      = $context->getAuthorization();

        parent::__construct($context, $data);
    }

    /**
     * Get after
     *
     * @return string
     */
    public function getAfter()
    {
        return 'wishlist';
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Reward Points');
    }

    /**
     * Get tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Reward Points');
    }

    /**
     * Can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * To html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $form = $this->formFactory->create();
        $form->setHtmlIdPrefix('_reward_point');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Reward Points Information')]
        );
        if ($this->isAllowed()) {
            $fieldset->addField(
                'rwp_website_id',
                'select',
                [
                    'label' => __('Website'),
                    'title' => __('Website'),
                    'name' => 'rwp[website_id]',
                    'required' => true,
                    'values' => $this->systemStore->getWebsiteValuesForForm(),
                    'data-form-part' => 'customer_form',
                ]
            );
            $fieldset->addField(
                'rwp_change_balance',
                'text',
                [
                    'label' => __('Update balance'),
                    'title' => __('Update balance'),
                    'name' => 'rwp[point]',
                    'note' => __('Enter positive or negative number of points. E.g. 10 or -10'),
                    'data-form-part' => 'customer_form',
                ]
            );
            $fieldset->addField(
                'rwp_note',
                'text',
                [
                    'label' => __('Note'),
                    'title' => __('Note'),
                    'name' => 'rwp[note]',
                    'note' => __(''),
                    'data-form-part' => 'customer_form',
                ]
            );
        }
        $grid = $this->getLayout()->createBlock(Grid::class, 'rewardpoint.grid');
        $notification = $this->getLayout()->createBlock(Notification::class, 'rewardpoint.notification');
        return $form->toHtml() . $grid->toHtml() . $notification->toHtml();
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Is allow
     *
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->authorization->isAllowed('Bss_RewardPoint::transaction');
    }
}
