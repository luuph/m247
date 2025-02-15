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
 * @package    Bss_AdminActionLog
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminActionLog\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class PageVisit extends \Bss\AdminActionLog\Ui\Component\Listing\Column\AbstractColumn
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $helper;

    /**
     * PageVisit constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\Helper\Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->helper = $helper;
    }

    /**
     * Prepare Item
     *
     * @param array $item
     * @return array
     */
    protected function _prepareItem(array & $item)
    {
        if (isset($item['id'])) {
            $item[$this->getData('name')] = '<a href="' . $this->helper->getUrl('bssadmin/visit/detail', ['id'=>$item['id']]) . '" class="view_detail" target="_blank">' . __('View') . '</a>';
        }

        return $item;
    }
}
