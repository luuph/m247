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
 * DeletePayee resolver, used for GraphQL request processing.
 */
class DeletePayee implements ResolverInterface
{
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
     * @param \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
     */
    public function __construct(
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
    ) {
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
            $responseMessage=[];
            $result = '';
            $params = $args ;
            $result = $this->validateParams($params);
            $responseMessage['message'] = $result;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $responseMessage;
    }

        /**
         * Validate params
         *
         * @param array $params
         */
    protected function validateParams($params)
    {
        $message = '';
        if (isset($params)
            && is_array($params)
            && array_key_exists('payeeId', $params)
            && $params['payeeId']!='') {
            $response  = $this->deletePayee($params);
            $message = $response;
        } else {
            $message = "There is some error during executing this process, please try again later.";
        }

        return $message;
    }

       /**
        * Delete payee
        *
        * @param array $params
        */
    public function deletePayee($params)
    {
        $payeeModel = $this->walletPayee->create()->load($params['payeeId']);

        if ($payeeModel->getCustomerId()) {
            $payeeModel->delete();
            return "Payee is successfully deleted";
        } else {
            return "Payee not exist";
        }
    }
}
