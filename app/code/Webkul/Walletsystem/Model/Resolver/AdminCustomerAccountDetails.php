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
use Magento\Customer\Model\CustomerFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

/**
 * AdminCustomerAccountDetails resolver, used for GraphQL request processing.
 */
class AdminCustomerAccountDetails implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\AccountDetails\CollectionFactory
     */
    private $accountResourceModel;

    /**
     * Construct function
     *
     * @param CustomerFactory $customerFactory
     * @param \Webkul\Walletsystem\Model\ResourceModel\AccountDetails\CollectionFactory $accountResourceModel
     */
    public function __construct(
        CustomerFactory $customerFactory,
        \Webkul\Walletsystem\Model\ResourceModel\AccountDetails\CollectionFactory $accountResourceModel
    ) {
        $this->customerFactory = $customerFactory;
        $this->accountResourceModel = $accountResourceModel;
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
            $response=[];
            $collectionData = $this->accountResourceModel->create();
            foreach ($collectionData as $record) {
                $id = $record->getId();
                $email = $this->customerFactory->create()->load($record->getCustomerId())->getEmail();
                $customerName = $this->customerFactory->create()->load($record->getCustomerId())->getName();
                $response[$id]['CustomerName'] = $customerName;
                $response[$id]['CustomerEmail'] =$email;
                $response[$id]['HolderName'] = $record->getHoldername();
                $response[$id]['BankName'] =$record->getBankname();
                $response[$id]['AdditionalInformation'] =$record->getAdditional();
                $response[$id]['BankCode'] =$record->getBankcode();
                $response[$id]['RequestForDelete'] =$record->getRequestToDelete() == 0 ? 'No': 'Yes' ;
            }
            
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception>getMessage()));
        }
        return $response;
    }
}
