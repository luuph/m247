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

use Magento\Customer\Controller\RegistryConstants;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magento\Customer\Model\Customer;
use Bss\RewardPoint\Model\NotificationFactory;

/**
 * Class notification
 *
 * Bss\RewardPoint\Block\Adminhtml\Customer\Edit\Tabs
 */
class Notification extends \Magento\Backend\Block\Widget\Form
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
     * @var NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Notification constructor.
     *
     * @param Context $context
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param Store $systemStore
     * @param Customer $customerRepository
     * @param NotificationFactory $notificationFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        Store $systemStore,
        Customer $customerRepository,
        NotificationFactory $notificationFactory,
        array $data = []
    ) {
        $this->context = $context;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->systemStore = $systemStore;
        $this->customerRepository = $customerRepository;
        $this->notificationFactory = $notificationFactory;
        $this->authorization = $context->getAuthorization();
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
        return __('Notification Reward Points');
    }

    /**
     * Get tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Notification Reward Points');
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
        $form->setHtmlIdPrefix('_notification');
        $customer = $this->getCustomer();
        if (!$this->isAllowed() || !$customer) {
            return '';
        }

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Notification')]);

        $notify = $this->notificationFactory->create()->load($customer->getId());

        if ($notify->getId()) {
            $fieldset->addField(
                'notification_id',
                'hidden',
                [
                    'name' => 'rwp_notify[notification_id]',
                    'value' => $notify->getId(),
                    'data-form-part' => 'customer_form',
                ]
            );
        }

        $fieldset->addField(
            'rwp_notify_balance',
            'select',
            [
                'label' => __('Notify via email on balance update'),
                'title' => __('Notify via email on balance update'),
                'name' => 'rwp_notify[notify_balance]',
                'required' => true,
                'options' => [1 => __('Yes'), 0 => __('No')],
                'value' => (int)$notify->getNotifyBalance(),
                'data-form-part' => 'customer_form',
            ]
        );

        $fieldset->addField(
            'rwp_notify_expiration',
            'select',
            [
                'label' => __('Notify via email on upcoming expiry points'),
                'title' => __('Notify via email on upcoming expiry points'),
                'name' => 'rwp_notify[notify_expiration]',
                'required' => true,
                'options' => [1 => __('Yes'), 0 => __('No')],
                'value' => (int)$notify->getNotifyExpiration(),
                'data-form-part' => 'customer_form',
            ]
        );

        return $form->toHtml();
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
     * Get customer
     *
     * @return \Magento\Customer\Model\Customer|bool
     */
    protected function getCustomer()
    {
        if ($customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            $customerData = $this->customerRepository->load($customerId);

            return $customerData;
        }

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
