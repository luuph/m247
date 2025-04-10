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
 * CustomerAddAccountDetail resolver, used for GraphQL request processing.
 */
class CustomerAddAccountDetail implements ResolverInterface
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
     * Construct function
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
            $responseMessage=[];
            $accountDetails  = [
                'customer_id' => $args['customerId'],
                'holdername' =>  $args['acholderName'],
                'accountno'  =>  $args['acNumber'],
                'bankname'   =>  $args['bankName'],
                'bankcode'   =>  $args['bankCode'],
                'additional' =>  $args['additionalInformation']
            ];
           
            $this->accountDetails->setData($accountDetails)
            ->save();
            $responseMessage['message'] = 'Account Information Saved Successfully';
            
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $responseMessage;
    }
}
