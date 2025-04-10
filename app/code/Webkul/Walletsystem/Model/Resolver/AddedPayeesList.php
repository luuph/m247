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
use Magento\Customer\Model\CustomerFactory;

/**
 * AddedPayeesList resolver, used for GraphQL request processing.
 */
class AddedPayeesList implements ResolverInterface
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Model\WalletPayeeFactory
     */
    private $walletPayee;

    /**
     * Construct function
     *
     * @param CustomerFactory $customerFactory
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     */
    public function __construct(
        CustomerFactory $customerFactory,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
    ) {
        $this->customerFactory = $customerFactory;
        $this->walletPayee = $walletPayee;
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
            $addedPayeesListdetails=[];
            $status = '';
            
            $walletPayeeCollection = $this->walletPayee->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $args['customerId']);
            foreach ($walletPayeeCollection as $record) {
                $email = $this->customerFactory->create()->load($record->getPayeeCustomerId())->getEmail();
                $id  = $record->getEntityId();
                if ($record->getStatus() == 0) {

                    $status = 'Pending';

                } elseif ($record->getStatus() == 1) {

                    $status = 'Approve';
                } else {

                    $status = 'Cancel';
                }
                $addedPayeesListdetails[$id]['PayeeId'] = $id;
                $addedPayeesListdetails[$id]['Nick_name'] = $record->getNickName();
                $addedPayeesListdetails[$id]['Email_address'] =$email;
                $addedPayeesListdetails[$id]['Status'] = $status;
            }
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $addedPayeesListdetails;
    }
}
