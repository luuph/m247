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
 * @category   Mageplaza
 * @package    Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Plugin\SystemConfig;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;

/**
 * Class ConditionRule
 * @package Mageplaza\PdfInvoice\Plugin\SystemConfig
 */
class ConditionRule
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Json
     */
    protected $_jsonSerializer;

    /**
     * @var array|false[]
     */
    protected $condition = [];

    /**
     * ConditionRule constructor.
     *
     * @param RequestInterface $request
     * @param Json $jsonSerializer
     */
    public function __construct(
        RequestInterface $request,
        Json $jsonSerializer
    ) {
        $this->request         = $request;
        $this->_jsonSerializer = $jsonSerializer;
        $this->condition       = [
            'pdfinvoice_order_condition'      => false,
            'pdfinvoice_invoice_condition'    => false,
            'pdfinvoice_shipment_condition'   => false,
            'pdfinvoice_creditmemo_condition' => false,
        ];
    }

    /**
     * Set prefix and condition.
     *
     * @param AbstractCondition $subject
     */
    public function beforeGetTypeElement(AbstractCondition $subject)
    {
        if ($this->request->getParam('section') === 'pdfinvoice' && $subject->getRule()) {
            $prefix = $subject->getRule()->getPrefix() ?: 'conditions';
            $subject->setData('prefix', $prefix);
            $subject->setData('js_form_object', 'mp_' . $prefix);
            if ($subject->getRule()->getData('conditions_serialized_multiple')) {
                if ($subject->getData($prefix) === null) {
                    if ($this->condition[$prefix]) {
                        $subject->setData($prefix, []);
                    } else {
                        /** @var Combine $combine */
                        $combine = clone $subject;
                        $combine = $combine->loadArray($this->getConditions($combine));
                        $subject->setData($prefix, $combine->getData($prefix) ?: []);
                        $this->condition[$prefix] = true;
                    }
                }
            } else {
                $subject->setData($prefix, []);
            }
        }
        if ($this->request->getParam('form') && str_contains($this->request->getParam('form'), 'mp_pdfinvoice')) {
            $prefix = $this->request->getParam('form_namespace') ?: 'conditions';
            $subject->setData('prefix', $prefix);
            $subject->setData($prefix, []);
        }
    }

    /**
     *
     * @param AbstractCondition $combine
     *
     * @return mixed
     */
    public function getConditions(AbstractCondition $combine): mixed
    {
        $conditionsSerialized = $combine->getRule()->getData('conditions_serialized_multiple');

        return $this->_jsonSerializer->unserialize($conditionsSerialized);
    }
}
