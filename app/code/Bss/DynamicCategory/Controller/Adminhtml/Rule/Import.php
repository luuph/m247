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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Controller\Adminhtml\Rule;

use Bss\DynamicCategory\Block\Adminhtml\Catalog\Edit\Tab\Conditions;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Layout;
use Exception;

class Import extends \Bss\DynamicCategory\Controller\Adminhtml\Rule
{
    /**
     * Import rule block
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            /** @var Layout $layout */
            $layout   = $this->_view->getLayout();
            $block    = $layout->createBlock(Conditions::class);
            $response = $block->toHtml();
        } catch (Exception $exception) {
            $response = __('An error occurred');
            $this->logger->critical($exception);
        }

        return $this->_response->setBody($response);
    }
}
