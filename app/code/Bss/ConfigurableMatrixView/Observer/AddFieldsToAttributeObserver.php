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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableMatrixView\Observer;

use Magento\Config\Model\Config\Source;
use Magento\Framework\Module\Manager;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFieldsToAttributeObserver implements ObserverInterface
{
    /**
     * @var Source\Yesno
     */
    protected $yesNo;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * AddFieldsToAttributeObserver constructor.
     * @param Manager $moduleManager
     * @param Source\Yesno $yesNo
     */
    public function __construct(Manager $moduleManager, Source\Yesno $yesNo)
    {
        $this->moduleManager = $moduleManager;
        $this->yesNo = $yesNo;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleManager->isEnabled('Bss_ConfigurableMatrixView')) {
            return;
        }

        $form = $observer->getForm();
        $fieldset = $form->getElement('base_fieldset');
        $yesnoSource = $this->yesNo->toOptionArray();
        $fieldset->addField(
            'is_matrix_view',
            'select',
            [
                'name' => 'is_matrix_view',
                'label' => __('Use in MatrixView'),
                'title' => __('Use in MatrixView'),
                'note' => __('Use in BSS MatrixView'),
                'values' => $yesnoSource,
            ],
            'is_filterable'
        );
    }
}
