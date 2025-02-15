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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Plugin\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package Bss\CustomerAttributes\Plugin\Model\Metadata\Form
 */
class EditPost
{
    /**
     * Form code for data extractor
     */
    public const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Mapper
     */
    protected $customerMapper;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * EditPost constructor.
     * @param Http $request
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     * @param Mapper $customerMapper
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Http                        $request,
        Session                     $customerSession,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface            $messageManager,
        Escaper                     $escaper,
        CustomerExtractor           $customerExtractor,
        Mapper                      $customerMapper,
        Validator                   $formKeyValidator
    ) {
        $this->request = $request;
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->customerMapper = $customerMapper;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @param \Magento\Customer\Controller\Account\EditPost $subject
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeExecute(\Magento\Customer\Controller\Account\EditPost $subject)
    {
        $validFormKey = $this->formKeyValidator->validate($this->request);

        if ($validFormKey && $this->request->isPost()) {
            $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->request,
                $currentCustomerDataObject
            );
            try {
                $this->customerRepository->save($customerCandidateDataObject);
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addErrorMessage($this->escaper->escapeHtml($error->getMessage()));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }
        }
        return [];
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param RequestInterface $inputData
     * @param CustomerInterface $currentCustomerData
     * @return CustomerInterface
     */
    private function populateNewCustomerDataObject(
        RequestInterface  $inputData,
        CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->customerMapper->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }
}
