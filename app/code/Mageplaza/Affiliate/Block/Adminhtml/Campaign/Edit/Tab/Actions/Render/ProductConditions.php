<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_Affiliate
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab\Actions\Render;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Rule\Block\Actions;
use Mageplaza\Affiliate\Model\Campaign;

/**
 * Class ProductConditions
 *
 * @package Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab\Actions\Render
 */
class ProductConditions extends AbstractElement
{
    /**
     * @var Actions
     */
    protected $actions;

    /**
     * @var Campaign
     */
    protected $rule;

    /**
     * @var Text
     */
    protected $input;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * ProductConditions constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Actions $actions
     * @param Campaign $rule
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Actions $actions,
        Campaign $rule,
        Registry $registry,
        array $data = []
    ) {
        $this->actions  = $actions;
        $this->rule     = $rule;
        $this->registry = $registry;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $model = $this->registry->registry('current_campaign');
        if ($model) {
            $this->rule->loadPost($model->getData());
        }
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $htmlId      = $this->getHtmlId();
        $newChildUrl = $this->_escaper->escapeUrl($this->getNewChildUrl());

        $html = '
        <div class="rule-tree" id="' . $htmlId . '" style="width: 140%;margin-left: -30%">
            <div class="rule-tree-wrapper">
                    ' . $this->getInputHtml() . '
            </div>
        </div>';

        return $html . '<script>
    require([
        "Magento_Rule/rules",
        "prototype"
    ], function(VarienRulesForm){
        window.' . $htmlId . ' = new VarienRulesForm("' . $htmlId . '", "' . $newChildUrl . '");
    });
</script>';
    }

    /**
     * @return string
     */
    public function getInputHtml()
    {
        $this->rule->getActions()->setJsFormObject($this->getHtmlId());
        $this->input = $this->_factoryElement->create('text');
        $this->input->setRule($this->rule)->setRenderer($this->actions);

        return $this->input->toHtml();
    }
}
