<?php
namespace Lof\CustomerAvatar\Controller\Avatar;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\ResourceConnection;

class Delete extends Action
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var File
     */
    private $fileIo;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param File $fileIo
     * @param JsonFactory $jsonFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        File $fileIo,
        JsonFactory $jsonFactory,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->fileIo = $fileIo;
        $this->jsonFactory = $jsonFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Execute function
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('You must be logged in to delete your avatar.')
            ]);
        }

        try {
            $customer = $this->customerSession->getCustomer();
            $avatarAttribute = $customer->getProfilePicture();

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/DeleteAvatar2.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('text message');
            $logger->info(print_r($customer->debug(),1));
            $logger->info($avatarAttribute);

            if ($avatarAttribute) {
                $customer->setProfilePicture("");
                $customer->save();

                $logger->info('Avatar deleted successfully using direct DB update.');

                return $result->setData([
                    'success' => true,
                    'message' => __('Avatar deleted successfully.')
                ]);
            } else {
                return $result->setData([
                    'success' => false,
                    'message' => __('No avatar found to delete.')
                ]);
            }
        } catch (LocalizedException $e) {
            $logger->info('Err 1');
            $logger->info($e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $logger->info('Err 2');
            $logger->info($e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('Something went wrong while deleting the avatar.')
            ]);
        }
    }
}
