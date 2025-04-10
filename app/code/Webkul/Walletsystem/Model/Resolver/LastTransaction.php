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
 * LastTransaction resolver, used for GraphQL request processing.
 */
class LastTransaction implements ResolverInterface
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
            $lastTransactionDetails=[];
            $status = '';
            $amt =0;
            $debitAmount = 0;
            $creditAmount = 0;
            $transactionDetails = $this->getwalletTransactionCollection($args['customerId']);
            foreach ($transactionDetails as $record) {
                $id = $record->getId();
                if ($record->getStatus() == 1) {
                    $status = 'Approve';
                } elseif ($record->getStatus() == 2) {
                    $status = 'Cancel';
                } else {
                    $status = 'Pending';
                }

                if ($record->getAction() == 'debit') {

                    $amt =  $this->walletHelper->getwkconvertCurrency(
                        $record->getBaseCurrencyCode(),
                        $helper->getCurrentCurrencyCode(),
                        $record->getAmount()
                    );

                    $debitAmount = $this->walletHelper->getFormattedPriceAccToCurrency(
                        $amt,
                        2,
                        $this->walletHelper->getCurrentCurrencyCode()
                    );
                    
                } else {

                    $amt =  $this->walletHelper->getwkconvertCurrency(
                        $record->getBaseCurrencyCode(),
                        $this->walletHelper->getCurrentCurrencyCode(),
                        $record->getCurrAmount()
                    );

                    $creditAmount = $this->walletHelper->getFormattedPriceAccToCurrency(
                        $amt,
                        2,
                        $this->walletHelper->getCurrentCurrencyCode()
                    );

                }
                $lastTransactionDetails[$id]['reference'] = $record->getEntityId();
                $lastTransactionDetails[$id]['debit'] = $debitAmount;
                $lastTransactionDetails[$id]['credit'] = $creditAmount;
                $lastTransactionDetails[$id]['status'] = $status;
            }
            
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $lastTransactionDetails;
    }

    /**
     * GetwalletTransactionCollection function
     *
     * @param int $customerId
     */
    public function getwalletTransactionCollection($customerId)
    {
        if (!$this->transactioncollection) {
            $walletRecordCollection = $this->wallettransactionModel->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('transaction_at', 'DESC');
            $this->transactioncollection = $walletRecordCollection;
        }

        return $this->transactioncollection;
    }
}
