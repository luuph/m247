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
class Massdelete extends CreditrulesController
{
    /**
     * @var Webkul\Walletsystem\Api\WalletCreditRepositoryInterface
     */
    private $creditRuleRepository;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Initialize dependencies
     *
     * @param Action\Context                                                        $context
     * @param Filter                                                                $filter
     * @param Walletsystem\Api\WalletCreditRepositoryInterface                      $creditRuleRepository
     * @param Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory  $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        Walletsystem\Api\WalletCreditRepositoryInterface $creditRuleRepository,
        Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory $collectionFactory
    ) {
        $this->creditRuleRepository = $creditRuleRepository;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $creditRuleDeleted = 0;
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            foreach ($collection as $item) {
                $this->creditRuleRepository->deleteById($item->getEntityId());
                $creditRuleDeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $creditRuleDeleted)
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Deleting the data.')
            );
        }
        return $resultRedirect->setPath('*/*/creditrules');
    }
}
