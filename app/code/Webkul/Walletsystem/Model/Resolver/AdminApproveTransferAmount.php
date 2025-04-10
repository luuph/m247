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

declare(strict_types=1);

namespace Webkul\Walletsystem\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * AdminApproveTransferAmount resolver, used for GraphQL request processing.
 */
class AdminApproveTransferAmount implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory
     */
    private $wallettransactionModel;

    /**
     * @var \Webkul\Walletsystem\Model\WallettransactionFactory
     */
    private $walletTransaction;

    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    private $mailHelper;

    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel
     * @param \Webkul\Walletsystem\Model\WallettransactionFactory $transactionFactory
     * @param \Webkul\Walletsystem\Helper\Mail $mailHelper
     */
    public function __construct(
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel,
        \Webkul\Walletsystem\Model\WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Helper\Mail $mailHelper
    ) {
        $this->wallettransactionModel = $wallettransactionModel;
        $this->walletTransaction = $transactionFactory;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Resolver method for GraphQL
     *
     * @param Field $field
     * @param object $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $responseMessage=[];
            $params = $args;
            $walletTransactionModel = $this->walletTransaction->create();
            $walletTransCollec = $walletTransactionModel
                                ->getCollection()
                                ->addFieldToFilter('entity_id', $params['entityId'])
                                ->addFieldToFilter('status', $walletTransactionModel::WALLET_TRANS_STATE_PENDING);
            if (is_array($params) && array_key_exists('entityId', $params) &&
            $params['entityId'] != '' && $walletTransCollec->getSize()) {
                $condition = "`entity_id`=".$params['entityId'];
                $this->walletTransaction->create()->getCollection()->setTableRecords(
                    $condition,
                    ['status' => $walletTransactionModel::WALLET_TRANS_STATE_APPROVE]
                );
                $sendMessageCollection = $this->walletTransaction->create()->getCollection()
                ->addFieldToFilter('entity_id', $params['entityId']);
                $this->mailHelper->sendCustomerBulkTransferApproveMail($sendMessageCollection);
                $message =  __('Transaction status is updated.');
            } else {
                $message =  __('Transaction status cannot be changed.');
            }
            $responseMessage['message'] = $message ;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception>getMessage()));
        }
        return $responseMessage;
    }
}
