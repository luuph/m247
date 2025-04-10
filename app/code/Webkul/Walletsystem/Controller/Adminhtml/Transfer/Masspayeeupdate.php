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

namespace Webkul\Walletsystem\Controller\Adminhtml\Transfer;

use Webkul\Walletsystem\Controller\Adminhtml\Transfer as TransferController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Webkul Walletsystem Class
 */
class Masspayeeupdate extends TransferController
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Walletsystem\Model\ResourceModel\WalletPayee\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action $context
     * @param Filter $filter
     * @param Walletsystem\Model\ResourceModel\WalletPayee\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        Walletsystem\Model\ResourceModel\WalletPayee\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Update action
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
            return $resultRedirect->setPath('*/*/payeelist');
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
        return $resultRedirect->setPath('*/*/payeelist');
    }
}
