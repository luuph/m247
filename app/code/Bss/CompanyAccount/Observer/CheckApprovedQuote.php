<?php
namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;

class CheckApprovedQuote implements ObserverInterface
{
    /**
     * @var Cart
     */
    protected $cart;

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
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @param Cart $cart
     * @param ManagerInterface $manager
     * @param RedirectInterface $redirect
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        SubUserQuoteRepositoryInterface $subQuoteRepository,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->cart = $cart;
        $this->manager = $manager;
        $this->redirect = $redirect;
        $this->subQuoteRepository = $subQuoteRepository;
        $this->redirectFactory = $redirectFactory;
    }
    public function execute(Observer $observer) {
        $quote = $this->cart->getQuote();
        if($quote->getBssIsSubQuote()) {
            $subQuote = $this->subQuoteRepository->getByQuoteId($quote->getId());
            if ($subQuote->getQuoteStatus() == "approved") {
                $resultRedirect = $this->redirectFactory->create();
                $this->manager->addErrorMessage(
                    __('Please checkout your current approved order or go "Back to your previous cart" before adding more products to cart')
                );
                $resultRedirect->setPath('/');
                return $resultRedirect;
            }
        }
    }
}
