<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Controller\Adminhtml\Transaction;

/**
 * Class import
 *
 * Bss\RewardPoint\Controller\Adminhtml\Transaction
 */
class Import extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\ReviewsImport\Model\ResourceModel\Import
     */
    protected $importModel;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @var \Bss\RewardPoint\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * Import constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\RewardPoint\Model\ResourceModel\Import $importModel
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\RewardPoint\Model\ResourceModel\Transaction $transactionResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\RewardPoint\Model\ResourceModel\Import $importModel,
        \Magento\Framework\App\Request\Http $request,
        \Bss\RewardPoint\Model\ResourceModel\Transaction $transactionResource
    ) {
        parent::__construct($context);
        $this->importModel = $importModel;
        $this->request = $request;
        $this->transactionResource = $transactionResource;
    }

    /**
     * Execute
     *
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $filepath = "import/transactions/" . $this->request->getFiles('file')['name'];
        try {
            $this->importModel->setFilePath($filepath);
            $this->importModel->importFromCsvFile();
            $this->messageManager->addSuccessMessage(
                __('Inserted Row(s): ' . $this->transactionResource->getInsertedRows())
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/importexport',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }

    /**
     * Is allow
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_RewardPoint::transaction');
    }
}
