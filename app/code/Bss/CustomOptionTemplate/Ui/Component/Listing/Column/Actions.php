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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Actions extends \Bss\CustomOptionTemplate\Ui\Component\Listing\Column\AbstractColumn
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Actions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $item
     * @return array|mixed
     */
    protected function _prepareItem(array & $item)
    {
        $itemsAction = $this->getData('itemsAction');
        if (isset($itemsAction['title'])) {
            $itemsAction['title'] = $item['title'];
        }
        $indexField = $this->getData('config/indexField');

        if (isset($item[$indexField])) {
            foreach ($itemsAction as $key => $itemAction) {
                $path = isset($itemAction['path']) ? $itemAction['path'] : null;
                $itemAction['href'] = $this->urlBuilder->getUrl(
                    $path,
                    [$indexField => $item[$indexField]]
                );
                $item[$this->getData('name')][$key] = $itemAction;
            }
            if (isset($item['actions']['delete'])) {
                $item['actions']['delete']['confirm']['title'] = 'Delete ' . '"' . $item['title'] . '"';
                $item['actions']['delete']['confirm']['message'] = 'Are you sure you want to delete a ' . '"' . $item['title'] . '"' . ' record?';
            }
        }

        return $item;
    }
}
