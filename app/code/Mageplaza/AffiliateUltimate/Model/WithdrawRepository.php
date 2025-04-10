<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\Withdraw\Method;
use Mageplaza\Affiliate\Model\Withdraw\Status;
use Mageplaza\AffiliateUltimate\Api\Data\WithdrawInterface;
use Mageplaza\AffiliateUltimate\Api\Data\WithdrawSearchResultInterface;
use Mageplaza\AffiliateUltimate\Api\Data\WithdrawSearchResultInterfaceFactory as SearchResultFactory;
use Mageplaza\AffiliateUltimate\Api\WithdrawRepositoryInterface;
use Mageplaza\AffiliateUltimate\Helper\Data;
use Mageplaza\AffiliateUltimate\Model\WithdrawFactory as WithdrawAPIFactory;

/**
 * Class WithdrawRepository
 * @package Mageplaza\AffiliateUltimate\Model
 */
class WithdrawRepository implements WithdrawRepositoryInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var WithdrawFactory
     */
    protected $withdrawAPIFactory;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var bool
     */
    protected $isStandard = false;

    /**
     * @var Method
     */
    protected $paymentMethod;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * WithdrawRepository constructor.
     *
     * @param Data $helperData
     * @param WithdrawFactory $withdrawFactory
     * @param SearchResultFactory $searchResultFactory
     * @param Method $paymentMethod
     * @param AccountFactory $accountFactory
     */
    public function __construct(
        Data $helperData,
        WithdrawAPIFactory $withdrawFactory,
        SearchResultFactory $searchResultFactory,
        Method $paymentMethod,
        AccountFactory $accountFactory
    ) {
        $this->helperData          = $helperData;
        $this->withdrawAPIFactory  = $withdrawFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->paymentMethod       = $paymentMethod;
        $this->accountFactory      = $accountFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultFactory->create();

        return $this->helperData->processGetList($searchCriteria, $searchResult);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Withdraw id required'));
        }

        if ($this->isStandard) {
            $withdraw = $this->getWithdraw()->load($id);
        } else {
            $withdraw = $this->withdrawAPIFactory->create()->load($id);
        }

        if (!$withdraw->getId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        }

        return $withdraw;
    }

    /**
     * @return mixed
     */
    public function getWithdraw()
    {
        return ObjectManager::getInstance()->create('\Mageplaza\Affiliate\Model\Withdraw');
    }

    /**
     * {@inheritDoc}
     */
    public function getByAffiliateId($affiliateId)
    {
        if (!$affiliateId) {
            throw new InputException(__('Affiliate id required'));
        }

        return $this->searchResultFactory->create()->addFieldToFilter('account_id', $affiliateId);
    }

    /**
     * {@inheritDoc}
     */
    public function approve($id)
    {
        try {
            $withdraw = $this->get($id);
            if ($withdraw->getStatus() == Status::CANCEL) {
                throw  new  Exception(__('The withdraw had canceled.'));
            }

            $withdraw->setStatus(Status::COMPLETE)->save();
        } catch (Exception $e) {
            throw new CouldNotSaveException((__('Could not approve the withdraw: %1', $e->getMessage())));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function cancel($id)
    {
        try {
            $this->isStandard = true;
            /** @var \Mageplaza\Affiliate\Model\Withdraw $withdraw */
            $withdraw = $this->get($id);
            $withdraw->cancel();
        } catch (Exception $e) {
            throw new CouldNotSaveException((__('Could not cancel the withdraw: %1', $e->getMessage())));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function save(WithdrawInterface $data)
    {
        if (empty($data->getAccountId())) {
            throw new InputException(__('Affiliate id required'));
        }

        if (empty($data->getAmount())) {
            throw new InputException(__('Amount required'));
        }
        if (empty($data->getPaymentMethod())) {
            throw new InputException(__('Payment method required'));
        }

        if ($data->getAmount() <= 0.001) {
            throw new InputException(__('Amount must great than zero'));
        }

        if ($data->getPaymentMethod()) {
            $paymentMethods = $this->paymentMethod->getOptionHash();
            if (!isset($paymentMethods[$data->getPaymentMethod()])) {
                throw new NoSuchEntityException(__('Payment method doesn\'t exist'));
            }

            if ($data->getPaymentMethod() == 'paypal') {
                if (empty($data->getPaypalEmail())) {
                    throw new InputException(__('Paypal email required'));
                }

                if (!filter_var($data->getPaypalEmail(), FILTER_VALIDATE_EMAIL)) {
                    throw new InputException(__('Invalid paypal email address.'));
                }
            }
        }
        /** @var \Mageplaza\Affiliate\Model\Account $account */
        $account = $this->accountFactory->create()->load($data->getAccountId());
        if (!$account->getId()) {
            throw new NoSuchEntityException(__('Affiliate account doesn\'t exist'));
        }

        $data = [
            'customer_id'           => $account->getCustomerId(),
            'amount'                => $data->getAmount(),
            'fee'                   => $data->getFee(),
            'payment_method'        => $data->getPaymentMethod(),
            'withdraw_description'  => $data->getDescription(),
            'offline_address'       => $data->getOfflineAddress(),
            'banktranfer'           => $data->getBanktranfer(),
            'paypal_email'          => $data->getPaypalEmail(),
            'paypal_transaction_id' => $data->getPaypalTransactionId()
        ];
        try {
            $withdraw = $this->getWithdraw()->setData($data)->save();
        } catch (Exception $e) {
            throw new CouldNotSaveException((__('Could not save the withdraw: %1', $e->getMessage())));
        }

        return $withdraw->getId();
    }
}
