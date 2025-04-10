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
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * AdminCustomerWallentDetail resolver, used for GraphQL request processing.
 */
class AdminCustomerWallentDetail implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory
     */
    private $wallettransactionModel;
    
    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel,
    ) {
        $this->walletHelper = $walletHelper;
        $this->wallettransactionModel = $wallettransactionModel;
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
            $collectionData = $this->wallettransactionModel->create();
            $collectionData->addFieldToFilter('customer_id', $args['customerId'])
                ->setOrder('transaction_at', 'DESC');
           
            foreach ($collectionData as $record) {
                $id = $record->getId();
                $prefix = $this->walletHelper->getTransactionPrefix($record->getSenderType(), $record->getAction());
                if ($record->getStatus() == 0) {
                    $status = 'Pending';
                } elseif ($record->getStatus() == 1) {
                    $status = 'Approve';
                } else {
                    $status = 'Cancel';
                }
                $responseMessage[$id]['Reference'] =$prefix;
                $responseMessage[$id]['Amount'] = $record->getAmount();
                $responseMessage[$id]['Action'] = $record->getAction();
                $responseMessage[$id]['TransactionAt'] = $record->getTransactionAt();
                $responseMessage[$id]['Note'] = $record->getTransactionNote();
                $responseMessage[$id]['Status'] = $status;
            }
            return $responseMessage;
            
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $responseMessage;
    }
}
