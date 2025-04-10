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

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

/**
 * AdminConfirmWalletOrder resolver, used for GraphQL request processing.
 */
class AdminConfirmWalletOrder implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;

    /**
     * Construct function
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
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
        $message = '';
        try {
            $responseMessage=[];
            $params = $args;
            $orderId =$params['orderId'];
            if ($orderId) {
             
                $order = $this->orderRepository->get($orderId);
                $entityId = $order->getEntityId();

                if ($entityId) {

                    if ($order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($order);
                        $invoice->register();
                        $result = $invoice->save();

                        if ($result) {
                            $message = 'Wallet Amount Updated';
                        }
                    } else {
                        $message = 'Already Complete';
                    }
                } else {
                    $message = 'No Data Found';
                }
              
            } else {
                $message = 'Order Id Required';
            }
            $responseMessage['message'] =$message;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception>getMessage()));
        }
        return $responseMessage;
    }
}
