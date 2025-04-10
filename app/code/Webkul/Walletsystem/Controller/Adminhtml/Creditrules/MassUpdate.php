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

namespace Webkul\Walletsystem\Controller\Adminhtml\Creditrules;

use Webkul\Walletsystem\Controller\Adminhtml\Creditrules as CreditrulesController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Webkul Walletsystem Class
 */
class MassUpdate extends CreditrulesController
{
    /**
     * @var Webkul\Walletsystem\Api\WalletCreditRepositoryInterface
     */
    protected $creditRuleRepository;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    protected $collectionFactory;

   /**
    * Constructor
    *
    * @param Action\Context $context
    * @param Filter $filter
    * @param Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository
    * @param Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
    */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository,
        Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->creditRuleRepository = $creditRuleRepository;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Mass Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $status = $data['entity_id'];
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $entityIds = $collection->getAllIds();
            if (!empty($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $creditRuleCollection = $this->collectionFactory->create();
                $creditRuleCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );

                $this->messageManager->addSuccess(
                    __(
                        'A Total of %1 record(s) successfully updated.',
                        count($entityIds)
                    )
                );
            }
            return $resultRedirect->setPath('*/*/creditrules');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Updating the data.')
            );
        }
        return $resultRedirect->setPath('*/*/creditrules');
    }
}
