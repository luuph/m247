<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Plugin\Customer\Model;

use Magento\Customer\Model\Visitor;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;

class SetVisitorData extends AbstractModel
{
    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        SessionManagerInterface $session,
        RequestInterface $request,
        ModelContext $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->session = $session;
        $this->request = $request;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param Visitor $subject
     * @param Visitor $result
     */
    public function afterInitByRequest(Visitor $subject, $result)
    {
        if (!$subject->getId() && str_contains($this->request->getRequestUri(), 'amsociallogin')) {
            $subject->setSessionId($this->session->getSessionId());
            $subject->save();
            $this->_eventManager->dispatch('visitor_init', ['visitor' => $subject]);
            $this->session->setVisitorData($subject->getData());
        }

        return $result;
    }
}
