<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Block\Account;

use Amasty\SocialLogin\Model\SocialData;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;

class SocialAccounts extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_SocialLogin::account/social.phtml';

    /**
     * @var array|null
     */
    private $linkedAccounts = null;

    /**
     * @var \Amasty\SocialLogin\Model\ResourceModel\Social\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SocialData
     */
    private $socialData;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \Amasty\SocialLogin\Model\ConfigData
     */
    private $configData;

    /**
     * @var FormKey
     */
    private $formKey;

    public function __construct(
        Template\Context $context,
        \Amasty\SocialLogin\Model\ResourceModel\Social\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        SocialData $socialData,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        array $data = [],
        FormKey $formKey = null // TODO move to not optional
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->socialData = $socialData;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->configData = $configData;
        // OM for backward compatibility
        $this->formKey = $formKey ?? ObjectManager::getInstance()->get(FormKey::class);
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->customerSessionFactory->create();
    }

    /**
     * @return array
     */
    private function getLinkedAccounts()
    {
        if ($this->linkedAccounts === null) {
            $this->linkedAccounts = [];
            $customerId = $this->getCustomerSession()->getCustomer()->getId();

            if ($customerId) {
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId);

                foreach ($collection as $item) {
                    $this->linkedAccounts[$item->getType()] = $item->getName();
                }
            }
        }

        return $this->linkedAccounts;
    }

    /**
     * @return array
     */
    public function getLinkedAccountsData()
    {
        $linked = $this->getLinkedAccounts();
        $enabled = $this->socialData->getEnabledSocials();
        foreach ($enabled as $key => &$item) {
            $name = $item['type'];
            if (array_key_exists($name, $linked)) {
                $item['url'] = $this->getUnlinkUrl($name);
                $item['name'] = $linked[$name];
            } else {
                unset($enabled[$key]);
            }
        }

        return $enabled;
    }

    /**
     * @return array
     */
    public function getUnLinkedAccountsData()
    {
        $linked = $this->getLinkedAccounts();
        $enabled = $this->socialData->getEnabledSocials();
        foreach ($enabled as $key => $item) {
            $name = $item['type'];
            if (array_key_exists($name, $linked)) {
                unset($enabled[$key]);
            }
        }

        return $enabled;
    }

    /**
     * @param $type
     *
     * @return string
     */
    public function getUnlinkUrl($type)
    {
        return $this->_urlBuilder->getUrl('amsociallogin/social/unlink', ['type' => $type]);
    }

    /**
     * @return string
     */
    public function getButtonShapeClass()
    {
        return $this->configData->getButtonShapeClass();
    }

    /**
     * @return bool
     */
    public function getButtonLabelState()
    {
        return $this->configData->getButtonLabelState();
    }

    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }
}
