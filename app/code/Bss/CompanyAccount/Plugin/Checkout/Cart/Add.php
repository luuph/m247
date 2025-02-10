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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Checkout\Cart;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Message\ManagerInterface;
use Bss\CompanyAccount\Helper\Data;
class Add
{
    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    protected $subQuoteRepository;
    /**
     * @param ManagerInterface $manager
     * @param RedirectInterface $redirect
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        SubUserQuoteRepositoryInterface $subQuoteRepository
    ) {
        $this->manager = $manager;
        $this->redirect = $redirect;
        $this->subQuoteRepository = $subQuoteRepository;
    }

    /**
     * @param Cart $subject
     * @param int|Product $productInfo
     * @param DataObject|int|array $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        $quote = $subject->getQuote();
        if($quote->getBssIsSubQuote()) {
            $subQuote = $this->subQuoteRepository->getByQuoteId($quote->getId());
            if ($subQuote->getQuoteStatus() == "approved") {
                $this->manager->addErrorMessage(__('Please checkout your current approved order or go "Back to your previous cart" before adding more products to cart.'));
                throw new LocalizedException(__('Cannot add this product to the cart.'));
            }
            if ($subQuote->getQuoteStatus() == Data::SUB_QUOTE_WAITING) {
                $this->manager->addErrorMessage(__('Please checkout your current waiting order or go "Back to your previous cart" before adding more products to cart.'));
                throw new LocalizedException(__('Cannot add this product to the cart.'));
            }
        }
        return [$productInfo, $requestInfo];
    }
}
