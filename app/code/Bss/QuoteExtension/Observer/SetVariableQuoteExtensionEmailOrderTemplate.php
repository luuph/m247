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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Observer;

use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SetVariableQuoteExtensionEmailOrderTemplate implements ObserverInterface
{

    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @param ManageQuoteRepository $manageQuoteRepository
     */
    public function __construct(ManageQuoteRepository $manageQuoteRepository)
    {
        $this->manageQuoteRepository = $manageQuoteRepository;
    }

    /**
     * Observer for email_order_set_template_vars_before
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transportObject');
        $quoteExtensionIncrementId = $this->manageQuoteRepository->getByOrder($transport->getOrder())->getIncrementId();
        $transport->setData('bss_quote_extension_increment_id', $quoteExtensionIncrementId);
    }
}
