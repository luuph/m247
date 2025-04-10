<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIChromaDbClient
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AIChromaDbClient\Controller\Adminhtml\System;

use Webkul\AIChromaDbClient\Adapter\Adapter;

class DeleteCollections extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var Adapter
     */
    protected $_adapter;

    /**
     * Intialize Object for Delete Collections
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Adapter $adapter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Adapter $adapter
    ) {
        $this->messageManager = $messageManager;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_adapter = $adapter;
        parent::__construct($context);
    }

    /**
     * Fetch all collections and delete them
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->jsonResultFactory->create();
        $response = [];
        try {
            $collectionsList = $this->_adapter->getListCollections();
            if (!empty($collectionsList)) {
                foreach ($collectionsList as $collection) {
                    $deleteresponse = $this->_adapter->deleteCollection($collection['name']);
                }
                $message = __('All Collections deleted successfully');
                $response['error'] = 0;
                $this->messageManager->addSuccess($message);
            } else {
                $message = __('No Collection found !!');
                $response['error'] = 0;
                $this->messageManager->addNotice($message);
            }
        } catch (\Exception $e) {
            $response['error'] = 1;
            $this->messageManager->addError($e->getMessage());
        }
        return $resultJson->setData($response);
    }
}
