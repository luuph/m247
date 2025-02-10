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
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Rule\Block\Conditions as RuleConditions;
use Magento\SalesRule\Model\Rule;

/**
 * Class Condition
 * @package Mageplaza\PdfInvoice\Block\Adminhtml\System
 */
class Condition extends Field
{
    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;
    /**
     * @var RuleConditions
     */
    protected $_conditions;
    /**
     * @var FormFactory
     */
    protected $_formFactory;

    /**
     * @var Rule
     */
    protected $_rule;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param Fieldset $rendererFieldset
     * @param RuleConditions $conditions
     * @param FormFactory $formFactory
     * @param Rule $rule
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Fieldset $rendererFieldset,
        RuleConditions $conditions,
        FormFactory $formFactory,
        Rule $rule,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions       = $conditions;
        $this->_formFactory      = $formFactory;
        $this->_rule             = $rule;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @param AbstractElement $element
     *
     * @return mixed|string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $model = $this->_rule;
        /** @var Form $form */
        $form   = $this->_formFactory->create();
        $htmlId = $element->getHtmlId();
        $form->setHtmlIdPrefix('rule_' . $htmlId);
        $data['conditions_serialized_multiple'] = $element->getData()['value'];
        $data['prefix']                         = $htmlId;
        $formName                               = 'mp_' . $htmlId;
        $model->setData($data);
        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNameInLayout('pdf-layout-rule'
        )->setNewChildUrl(
            $this->getUrl("sales_rule/promo_quote/newConditionHtml/form/{$formName}/form_namespace/" . $htmlId)
        )->setFieldSetId($formName);


        $fieldset = $form->addFieldset($htmlId . '_fieldset', [
            'legend' => __('Apply the rule only if the following conditions are met (leave blank for all products)'),
        ])->setRenderer($renderer);

        $fieldset->addField($htmlId . '_conditions', 'text', [
            'name'  => $htmlId . '_conditions',
            'label' => __('Condition'),
            'title' => __('Condition'),
        ])
            ->setRule($model)
            ->setRenderer($this->_conditions);

        return $form->toHtml() . $this->getScriptHtml($htmlId);
    }

    /**
     * @param string $htmlId
     *
     * @return string
     */
    public function getScriptHtml(string $htmlId)
    {
        $inputName = str_replace('pdfinvoice_', '', $htmlId);
        $inputName = str_replace('_condition', '', $inputName);

        return <<<SCRIPT
            <script type="text/javascript">
                require([ 'jquery'], function ($) {
                    "use strict";
                    $(document).ready(function () {
                        var fieldName = 'pdfinvoice_' + '{$inputName}' + '_condition';
                        var inputHtml = '<input class="field-none" id="' + fieldName + '" name="groups[' + '{$inputName}' + '][fields][condition][value]" >';
                        $('#row_pdfinvoice_' + '{$inputName}' + '_condition .value').after(inputHtml);
                    });
                });
            </script>
        SCRIPT;
    }
}
