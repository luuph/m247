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

namespace Bss\QuoteExtension\Block\Adminhtml\Order\View;

use Bss\QuoteExtension\Model\Url;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\Registry;
use Bss\QuoteExtension\Model\ManageQuoteRepository;
use Magento\Sales\Helper\Admin;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @var Url
     */
    protected $quoteExtensionUrl;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param GroupRepositoryInterface $groupRepository
     * @param CustomerMetadataInterface $metadata
     * @param ElementFactory $elementFactory
     * @param Renderer $addressRenderer
     * @param Url $quoteExtensionUrl
     * @param ManageQuoteRepository $manageQuoteRepository
     * @param array $data
     */
    public function __construct(
        Context                   $context,
        Registry                  $registry,
        Admin                     $adminHelper,
        GroupRepositoryInterface  $groupRepository,
        CustomerMetadataInterface $metadata,
        ElementFactory            $elementFactory,
        Renderer                  $addressRenderer,
        Url                       $quoteExtensionUrl,
        ManageQuoteRepository     $manageQuoteRepository,
        array                     $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $adminHelper,
            $groupRepository,
            $metadata,
            $elementFactory,
            $addressRenderer,
            $data
        );
        $this->manageQuoteRepository = $manageQuoteRepository;
        $this->quoteExtensionUrl = $quoteExtensionUrl;
    }

    /**
     * Get quote by order id
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote
     * @throws LocalizedException
     */
    public function getQuoteExtension()
    {
        return $this->manageQuoteRepository->getByOrder($this->getOrder());
    }

    /**
     * Get quote by order id
     *
     * @return string
     * @throws LocalizedException
     */
    public function getQuoteUrl()
    {
        return $this->quoteExtensionUrl->getQuoteExtensionBackendViewUrl($this->getQuoteExtension()->getEntityId());
    }

    /**
     * Get quote extension increment id
     *
     * @return array|int|mixed|null
     * @throws LocalizedException
     */
    public function getQuoteExtemsionIncrementId()
    {
        return $this->getQuoteExtension()->getIncrementId();
    }
}
