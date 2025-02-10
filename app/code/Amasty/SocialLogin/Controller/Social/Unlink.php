<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Controller\Social;

use Amasty\SocialLogin\Model\Unlink as UnlinkModel;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Unlink extends Action implements HttpPostActionInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var UnlinkModel
     */
    private $unlink;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    public function __construct(
        Context $context,
        Session $customerSession,
        UnlinkModel $unlink,
        FormKeyValidator $formKeyValidator = null
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->unlink = $unlink;
        // OM for backward compatibility
        $this->formKeyValidator = $formKeyValidator ?? ObjectManager::getInstance()->get(FormKeyValidator::class);
    }

    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page.'));
        } else {
            $customerId = (int) $this->customerSession->getCustomerId();
            $type = $this->getRequest()->getParam('type');
            $result = $this->unlink->execute($type, $customerId);

            if ($result['isSuccess']) {
                $this->messageManager->addSuccessMessage($result['message']);
            } else {
                $this->messageManager->addErrorMessage($result['message']);
            }
        }

        return $this->_redirect('amsociallogin/social/accounts');
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
