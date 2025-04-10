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
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * ViewAccountDetails resolver, used for GraphQL request processing.
 */
class ViewAccountDetails implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Model\AccountDetails
     */
    private $accountDetails;

    /**
     * Constructor function
     *

     * @param \Webkul\Walletsystem\Model\AccountDetails $accountDetails
     */
    public function __construct(
        \Webkul\Walletsystem\Model\AccountDetails $accountDetails
    ) {
        $this->accountDetails = $accountDetails;
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
            $ViewAccountDetails=[];
            $trans_status = '';
            $accountDataCollection = $this->accountDetails->getCollection()
                                ->addFieldToFilter('customer_id', $args['customerId'])
                                ->setOrder('entity_id', 'DSC');
            foreach ($accountDataCollection as $record) {
                 $id = $record->getEntityId();
                 $ViewAccountDetails[$id]['Ac_Holder_Name'] = $record->getHoldername();
                 $ViewAccountDetails[$id]['Ac_Number'] = $record->getAccountno();
                 $ViewAccountDetails[$id]['Bank_Name'] = $record->getBankname();
                 $ViewAccountDetails[$id]['Bank_Code'] = $record->getBankcode();
                 $ViewAccountDetails[$id]['Additional_Information'] = $record->getAdditional();
            }
                return $ViewAccountDetails;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }
}
