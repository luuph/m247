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
 * @package    Bss_LoginAsCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\LoginAsCustomer\Helper;

use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException;
use Magento\Store\Model\StoreSwitcherInterface;

class SwitchStore
{
    /**
     * @var StoreSwitcherInterface
     */
    protected $storeSwitcher;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * SwitcherStore constructor.
     *
     * @param StoreSwitcherInterface $storeSwitcher
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param ActionContext $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StoreSwitcherInterface $storeSwitcher,
        StoreRepositoryInterface $storeRepository,
        StoreManagerInterface $storeManager,
        ActionContext $context
    ) {
        $this->storeSwitcher = $storeSwitcher;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * After Customer Login
     *
     * @param string $url
     * @param string $storecode
     * @return void
     * @throws NoSuchEntityException
     * @throws CannotSwitchStoreException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switchStoreView($url, $storecode)
    {
        $error = null;
        $fromStoreStoreCode = $this->storeManager->getStore()->getCode();
        try {
            $fromStore = $this->storeRepository->get($fromStoreStoreCode);
            $targetStore = $this->storeRepository->getActiveStoreByCode($storecode);
            $this->storeSwitcher->switch($fromStore, $targetStore, $url);
            $this->messageManager->getMessages(true);
        } catch (NoSuchEntityException $e) {
            $error = __("The store that was requested wasn't found. Verify the store and try again.");
        } catch (StoreIsInactiveException $e) {
            $error = __('Requested store is inactive');
        }
        if ($error !== null) {
            $this->messageManager->addErrorMessage($error);
        }
    }
}
