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
 * AdminAllCustomerWallentDetail resolver, used for GraphQL request processing.
 */
class AdminAllCustomerWallentDetail implements ResolverInterface
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory
     */
    private $walletrecordModel;

    /**
     * Construct function
     *
     * @param CustomerFactory $customerFactory
     * @param \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory $walletrecordModel
     */
    public function __construct(
        CustomerFactory $customerFactory,
        \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory $walletrecordModel,
    ) {
        $this->customerFactory = $customerFactory;

        $this->walletrecordModel = $walletrecordModel;
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
            $collectionData = $this->walletrecordModel->create();
            foreach ($collectionData as $record) {
                $customer = $this->customerFactory->create();
                $customer->load($record->getCustomerId());
                $id = $record->getId();
                $responseMessage[$id]['CustomerName'] =$customer->getName();
                $responseMessage[$id]['TotalAmount'] = $record->getTotalAmount();
                $responseMessage[$id]['RemainingAmount'] = $record->getRemainingAmount();
                $responseMessage[$id]['UsedAmount'] = $record->getUsedAmount();
                $responseMessage[$id]['ModifyAt'] = $record->getUpdatedAt();
            }
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception>getMessage()));
        }
        return $responseMessage;
    }
}
