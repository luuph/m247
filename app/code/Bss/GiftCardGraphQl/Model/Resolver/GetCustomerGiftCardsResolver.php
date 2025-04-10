<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_GiftCardGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GiftCardGraphQl\Model\Resolver;

use Bss\GiftCard\Model\Pattern\Code;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCardGraphQl\Exception\GraphQlUnhandledException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Bss\GiftCard\Helper\Data as GiftCardData;

/**
 * Class GetCustomerGiftCardsResolver
 */
class GetCustomerGiftCardsResolver implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var CodeFactory
     */
    protected $codeFactory;

    /**
     * @var GiftCardData
     */
    protected $giftCardData;

    /**
     * GetCustomerGiftCardsResolver constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param CodeFactory $codeFactory
     * @param GiftCardData $giftCardData
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger,
        CodeFactory $codeFactory,
        GiftCardData $giftCardData
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->codeFactory = $codeFactory;
        $this->giftCardData = $giftCardData;
    }

    /**
	 * @inheritDoc
	 */
	public function resolve(
	    Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $currentUserId = $context->getUserId();

        /** @var \Magento\GraphQl\Model\Query\ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }
        try {
            $customer = $this->customerRepository->getById($currentUserId);
            $codeCollection = $this->codeFactory
                ->create()
                ->loadByEmail($customer->getEmail());
            $resultData = [];
            /** @var Code $code */
            foreach ($codeCollection as $code) {
                $itemData["code"] = $code->getCode();
                $itemData["value"] = $code->getValue();
                $itemData["expire_date"] = $this->giftCardData->formatDateTime($code->getExpiryDay());
                $itemData["status"] = [
                    "value" => $code->getStatus(),
                    "label" => $code->getStatusLabel()
                ];
                $itemData["order_id"] = $code->getOrderId();
                $resultData[] = $itemData;
            }
            return $resultData;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__("The customer with ID=%1 does not exist.", $currentUserId));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new GraphQlUnhandledException(__("Something went wrong. Please review the log."));
        }
	}
}
