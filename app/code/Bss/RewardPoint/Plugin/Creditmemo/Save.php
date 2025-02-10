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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Plugin\Creditmemo;

use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save as CreditmemoSave;

class Save
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;
    /**
     * Save constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\RewardPoint\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Bss\RewardPoint\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * Plugin
     *
     * @param CreditmemoSave $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(CreditmemoSave $subject)
    {
        $data = $this->request->getPost('creditmemo');
        if ($this->helper->isAutoRefundOrderToPoints()) {
            $data['do_offline'] = 1;
        }
        $this->request->setPostValue('creditmemo', $data);
    }
}
