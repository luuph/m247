<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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

namespace Mageplaza\AffiliateUltimate\Controller\Account;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Affiliate\Controller\Account;
use Mageplaza\Affiliate\Helper\Data as DataHelper;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\CampaignFactory;
use Mageplaza\Affiliate\Model\TransactionFactory;
use Mageplaza\Affiliate\Model\WithdrawFactory;
use Mageplaza\AffiliateUltimate\Block\Account\Home\CreditChart;

/**
 * Class Credit
 * @package Mageplaza\AffiliateUltimate\Controller\Account
 */
class Credit extends Account
{
    /**
     * @var CreditChart
     */
    private $creditChartBlock;

    /**
     * @var EmailAddress
     */
    protected $emailValidator;

    /**
     * Credit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TransactionFactory $transactionFactory
     * @param AccountFactory $accountFactory
     * @param WithdrawFactory $withdrawFactory
     * @param DataHelper $dataHelper
     * @param CustomerSession $customerSession
     * @param Registry $registry
     * @param EmailAddress $emailValidator
     * @param CampaignFactory $campaignFactory
     * @param CreditChart $creditChartBlock
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TransactionFactory $transactionFactory,
        AccountFactory $accountFactory,
        WithdrawFactory $withdrawFactory,
        DataHelper $dataHelper,
        CustomerSession $customerSession,
        Registry $registry,
        EmailAddress $emailValidator,
        CampaignFactory $campaignFactory,
        CreditChart $creditChartBlock
    ) {
        $this->creditChartBlock = $creditChartBlock;

        parent::__construct(
            $context,
            $resultPageFactory,
            $transactionFactory,
            $accountFactory,
            $withdrawFactory,
            $dataHelper,
            $customerSession,
            $registry,
            $emailValidator,
            $campaignFactory
        );
    }

    /**
     * @return Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        return $this->getResponse()->representJson($this->creditChartBlock->creditChartData());
    }
}
