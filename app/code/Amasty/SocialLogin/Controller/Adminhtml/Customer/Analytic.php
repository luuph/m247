<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Controller\Adminhtml\Customer;

use Amasty\SocialLogin\Block\Adminhtml\Analytic as AnalyticBlock;
use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutInterface;

class Analytic extends BackendAction implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Customer::customer';

    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(LayoutInterface $layout, Context $context)
    {
        parent::__construct($context);
        $this->layout = $layout;
    }

    /**
     * @return Raw
     */
    public function execute()
    {
        $block = $this->layout->createBlock(AnalyticBlock::class, 'customer.social.analytic', ['data' => [
            'template' => 'Amasty_SocialLogin::analytic.phtml'
        ]]);

        /** @var Raw $raw */
        $raw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $raw->setContents($block->toHtml());

        return $raw;
    }
}
