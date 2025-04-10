<?php
namespace Unveels\CancelMyFatoorahOrder\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;

class CancelMyFatoorahOrder implements ResolverInterface
{
    protected $orderFactory;
    protected $orderRepository;
    protected $checkoutSession;

    public function __construct(
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $incrementId = $args['order_increment_id'] ?? null;

        if (!$incrementId) {
            return ["success" => false, "message" => "Missing order_increment_id."];
        }

        try {
            $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

            if (!$order->getId()) {
                return ["success" => false, "message" => "Order not found."];
            }

            if ($order->getState() !== \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                return ["success" => false, "message" => "Order is not in pending_payment state."];
            }

            $order->cancel();
            $this->orderRepository->save($order);

            $this->checkoutSession->restoreQuote();

            return ["success" => true, "message" => "Order cancelled and cart restored."];
        } catch (\Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}
