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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Model\Customer;

use Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper;
use Bss\CustomerAttributes\Helper\CustomerAddress;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Bss\CustomerAttributes\Model\Config\Source\DisplayBackendCustomerDetail;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Multiline;

class DataProviderWithDefaultAddresses extends \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses
{
    public const CUSTOMER_ADDRESS = 'customer_address';

    /**
     * Customer fields that must be removed
     *
     * @var array
     */
    private static $forbiddenCustomerFields = [
        'password_hash',
        'rp_token',
    ];

    /**
     * @var array
     */
    private $loadedData = [];

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver
     */
    private $attributeMetadataResolver;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var B2BRegistrationIntegrationHelper
     */
    protected $b2BRegistrationIntegration;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var CustomerAddress
     */
    protected $customerAddress;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var Customerattribute
     */
    protected $helper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Construct
     *
     * @param CustomerAddress $customerAddress
     * @param AttributeRepositoryInterface $attributeRepository
     * @param Customerattribute $helper
     * @param B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     * @param Manager $moduleManager
     * @param Http $request
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param CountryFactory $countryFactory
     * @param SessionManagerInterface $session
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param AttributeMetadataResolver $attributeMetadataResolver
     * @param bool $allowToShowHiddenAttributes
     * @param array $meta
     * @param array $data
     * @param CustomerFactory|null $customerFactory
     * @param ContextInterface|null $context
     * @param CustomerRepositoryInterface|null $customerRepository
     * @throws LocalizedException
     */
    public function __construct(
        CustomerAddress $customerAddress,
        AttributeRepositoryInterface $attributeRepository,
        Customerattribute $helper,
        B2BRegistrationIntegrationHelper $b2BRegistrationIntegration,
        Manager $moduleManager,
        Http $request,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CustomerCollectionFactory $customerCollectionFactory,
        Config $eavConfig,
        CountryFactory $countryFactory,
        SessionManagerInterface $session,
        FileUploaderDataResolver $fileUploaderDataResolver,
        AttributeMetadataResolver $attributeMetadataResolver,
        $allowToShowHiddenAttributes = true,
        array $meta = [],
        array $data = [],
        CustomerFactory $customerFactory = null,
        ?ContextInterface $context = null,
        CustomerRepositoryInterface $customerRepository = null
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $customerCollectionFactory,
            $eavConfig,
            $countryFactory,
            $session,
            $fileUploaderDataResolver,
            $attributeMetadataResolver,
            $allowToShowHiddenAttributes,
            $meta,
            $data
        );
        $this->customerAddress = $customerAddress;
        $this->attributeRepository = $attributeRepository;
        $this->helper = $helper;
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->session = $session;
        $this->countryFactory = $countryFactory;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->customerRepository = $customerRepository ?: ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $this->attributeMetadataResolver = $attributeMetadataResolver;
        $this->meta['customer']['children'] = $this->getAttributesMeta(
            $eavConfig->getEntityType('customer')
        );
        $this->customerFactory = $customerFactory ?: ObjectManager::getInstance()->get(CustomerFactory::class);
    }

    /**
     * Convert data custom attribute address
     *
     * @return array
     */
    public function getData(): array
    {
        $result = parent::getData();
        if ($this->helper->isEnable()) {
            $attributeAddress = $this->helper->converAddressCollectioin();
            foreach ($result as $key => $customer) {
                if (isset($customer["default_billing_address"])) {
                    $result[$key]["default_billing_address"]["custom_attributes_address"] =
                        $this->customerAddress->getDataCustomAddress($customer["default_billing_address"], $attributeAddress);
                }
                if (isset($customer["default_shipping_address"])) {
                    $result[$key]["default_shipping_address"]["custom_attributes_address"] =
                        $this->customerAddress->getDataCustomAddress($customer["default_shipping_address"], $attributeAddress);
                }
            }
        }
        return $result;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws LocalizedException
     */
    private function getAttributesMeta(Type $entityType): array
    {
        $meta = [];
        $customerB2b = [Customerattribute::B2B_PENDING, Customerattribute::B2B_APPROVAL, Customerattribute::B2B_REJECT];
        $attributes = $entityType->getAttributeCollection();
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $meta[$attribute->getAttributeCode()] = $this->attributeMetadataResolver->getAttributesMeta(
                $attribute,
                $entityType,
                $this->allowToShowHiddenAttributes
            );
        }
        $this->attributeMetadataResolver->processWebsiteMeta($meta);
        if (!$this->moduleManager->isEnabled('Bss_B2bRegistration')) {
            return $meta;
        }
        $params = $this->request->getParams();
        $attributes = $entityType->getAttributeCollection();
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == "b2b_activasion_status") {
                continue;
            }
            $usedInForms = $attribute->getUsedInForms();
            if (in_array('is_customer_attribute', $usedInForms) && isset($params['id'])) {
                $customerId = $params['id'];
                if ($this->checkB2bActiveStatus($customerId, $customerB2b)) {
                    /* B2b Customer */
                    if ($this->b2BRegistrationIntegration->getAttributeDisplay($attributeCode) ==
                        DisplayBackendCustomerDetail::NORMAL_ACCOUNTS
                    ) {
                        unset($meta[$attributeCode]);
                    }
                } else {
                    /* Normal Account */
                    if ($this->b2BRegistrationIntegration->getAttributeDisplay($attributeCode) ==
                        DisplayBackendCustomerDetail::B2B_ACCOUNTS) {
                        unset($meta[$attributeCode]);
                    }
                }
            }
        }
        return $meta;
    }

    /**
     * Check b2b with one customer not full
     *
     * @param int|string $customerId
     * @param array $customerB2b
     * return bool
     */
    public function checkB2bActiveStatus($customerId, $customerB2b)
    {
        if ($this->loadedData) {
            if (isset($this->loadedData[$customerId]['customer']['b2b_activasion_status'])
                && in_array($this->loadedData[$customerId]['customer']['b2b_activasion_status'], $customerB2b)
            ) {
                return true;
            }
            return false;
        } else {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $b2bAttribute = $customer->getCustomAttribute('b2b_activasion_status');
                if ($b2bAttribute && $b2bAttribute->getValue() && in_array($b2bAttribute->getValue(), $customerB2b)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /***
     * Prepare values for Custom Attributes.
     *
     * @param array $data
     * @return void
     */
    private function prepareCustomAttributeValue(array &$data): void
    {
        foreach ($this->meta['customer']['children'] as $attributeName => $attributeMeta) {
            if ($attributeMeta['arguments']['data']['config']['dataType'] === Multiline::NAME
                && isset($data[$attributeName])
                && !is_array($data[$attributeName])
            ) {
                $data[$attributeName] = explode("\n", $data[$attributeName]);
            }
        }
    }
}
