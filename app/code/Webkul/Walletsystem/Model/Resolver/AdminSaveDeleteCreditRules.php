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
 * AdminSaveDeleteCreditRules resolver, used for GraphQL request processing.
 */
class AdminSaveDeleteCreditRules implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Webkul\Walletsystem\Model\WalletcreditrulesFactory
     */
    private $walletcreditrulesFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Webkul\Walletsystem\Api\WalletCreditRepositoryInterface
     */
    private $creditRuleRepository;

    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Model\WalletcreditrulesFactory $walletcreditrulesFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Webkul\Walletsystem\Model\WalletcreditrulesFactory $walletcreditrulesFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->walletcreditrulesFactory = $walletcreditrulesFactory;
        $this->date = $date;
        $this->creditRuleRepository = $creditRuleRepository;
        $this->logger = $logger;
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
            $message = '';
            $data = $args;
            if ($data && $data['deleteCreditRule']!== 1) {
                $error = $this->validateData($data);
                if (!empty($error)) {
                    $message = $error;
                }
                $model = $this->walletcreditrulesFactory->create();
                $id = $data['entityId'];
                if ($id) {
                    $this->logger->debug("Enter Here");
                    $model->load($id);
                } else {
                    $duplicate = $this->checkForAlreadyExists($data);
                    if ($duplicate) {
                        $message = "A rule with same details already exists.";
                    }
                }
                if ($id) {
                    $creditRules = [
                        'entity_id' => $data['entityId'],
                        'based_on' => $data['basedOn'],
                        'amount' => $data['amount'],
                        'minimum_amount' => $data['minimumAmount'],
                        'start_date' => $data['startDate'],
                        'end_date' => $data['endDate'],
                        'status' => $data['status'],
                        'created_at' => $this->date->gmtDate()
                    ];
                    $model->setData($creditRules);
                    $model->save();
                } else {
                    $creditRules = [
                        'based_on' => $data['basedOn'],
                        'amount' => $data['amount'],
                        'minimum_amount' => $data['minimumAmount'],
                        'start_date' => $data['startDate'],
                        'end_date' => $data['endDate'],
                        'status' => $data['status'],
                        'created_at' => $this->date->gmtDate()
                    ];
                    $model->setData($creditRules);
                    $model->save();
                }
                
                $message = "Credit Rule successfully saved.";
            } else {
                if ($data && array_key_exists('entityId', $data)) {
                    $entityId = $data['entityId'];
                    if ($entityId) {
                            $this->creditRuleRepository->deleteById($entityId);
                            $message = "Credit Rule successfully deleted.";
                    }
                }
            }
            $responseMessage['message'] = $message ;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception>getMessage()));
        }
        return $responseMessage;
    }

     /**
      * Validate data
      *
      * @param array $data
      * @return array
      */
    protected function validateData($data)
    {
        $errorMessage ='';
        if ($data['startDate'] > $data['endDate']) {
            $errorMessage = "End date can not be lesser then start From date.";
        }
        return $errorMessage;
    }

       /**
        * Check for already exists
        *
        * @param array $data
        * @return bool
        */
    protected function checkForAlreadyExists($data)
    {
        $creditModel = $this->walletcreditrulesFactory->create()
            ->getCollection()
            ->addFieldToFilter('amount', $data['amount'])
            ->addFieldToFilter('based_on', $data['basedOn'])
            ->addFieldToFilter('minimum_amount', $data['minimumAmount'])
            ->addFieldToFilter('start_date', $data['startDate'])
            ->addFieldToFilter('end_date', $data['endDate']);

        if ($creditModel->getSize()) {
            return true;
        }
        return false;
    }
}
