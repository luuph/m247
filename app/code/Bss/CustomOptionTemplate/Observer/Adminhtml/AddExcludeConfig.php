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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;

class AddExcludeConfig implements ObserverInterface
{
    const BSS_EXCLUDED_TEMPLATE = 'tenplates_excluded';

    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\Template\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * AddExcludeConfig constructor.
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\Template\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Bss\CustomOptionTemplate\Model\ResourceModel\Template\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $templateInclude = [];
        if ($product->getData('tenplates_included')) {
            $templateInclude = explode(",", $product->getData('tenplates_included'));
        }
        $templateExclude = [];
        if ($product->getData('tenplates_excluded')) {
            $templateExclude = explode(",", $product->getData('tenplates_excluded'));
        }

        $templateCollection = $this->collectionFactory->create();
        $assignTemplates = [];
        if (!empty($templateInclude)) {
            $templateCollection->addFieldToFilter('template_id', ['in' => $templateInclude]);
            if ($templateCollection->getSize()) {
                foreach ($templateCollection as $template) {
                    $assignTemplates[] = ['value' => $template->getId(), 'label' => $template->getTitle()];
                }
            }
        }

        $data = [
            static::BSS_EXCLUDED_TEMPLATE => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Excluded from Custom Option Template(s)'),
                            'formElement' => Input::NAME,
                            'componentType' => Field::NAME,
                            'component' => 'Bss_CustomOptionTemplate/js/exclude-template',
                            'elementTmpl' => 'Bss_CustomOptionTemplate/exclude_template',
                            'sortOrder' => 5,
                            'tenplates_included' => $assignTemplates,
                            'tenplates_excluded' => $templateExclude, 'value' => $templateExclude,
                            'additionalClasses' => 'exclude_config_label',
                        ],
                    ],
                ]
            ],
        ];
        $observer->getChild()->addData($data);
    }
}
