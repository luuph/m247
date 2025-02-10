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

namespace Bss\RewardPoint\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class maximum earn review
 *
 * Bss\RewardPoint\Block\Adminhtml\System\Config\Form
 */
class MaximumEarnReview extends AbstractFieldArray
{

    /**
     * @var array
     */
    protected $_columns = [];

    /**
     * @var boolean
     */
    protected $_addAfter = false;

    /**
     * @var string
     */
    protected $_addButtonLabel = false;

    /**
     * @var string
     */
    protected $_template = 'Bss_RewardPoint::system/config/form/field/array.phtml';

    /**
     * Get type date render
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getTypeDateRenderer()
    {
        $typeDateRenderer = $this->getLayout()->createBlock(
            \Bss\RewardPoint\Block\Adminhtml\System\Config\Form\TypeDate::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
        return $typeDateRenderer;
    }

    /**
     * Prepare to render
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'type_date',
            [
                'label'    => __(''),
                'renderer' => $this->_getTypeDateRenderer(),
            ]
        );

        $this->addColumn('period_time', ['label' => __('Period time')]);
        $this->addColumn('max_point', ['label' => __('Max Point')]);
    }

    /**
     * Prepare array row
     *
     * @param  DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_'.$this->_getTypeDateRenderer()->calcOptionHash($row->getData('type_date'))]
            = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param  string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == 'period_time' || $columnName == 'max_point') {
            $this->_columns[$columnName]['class'] = 'input-text validate-number validate-greater-than-zero';
        }

        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }

        $column    = $this->_columns[$columnName];
        $inputName = $this->_getCellInputElementName($columnName);

        if ($column['renderer']) {
            return $column['renderer']->setInputName(
                $inputName
            )->setInputId(
                $this->_getCellInputElementId('', $columnName)
            )->setColumnName(
                $columnName
            )->setColumn(
                $column
            )->toHtml();
        }

        return '<input type="text" id="'.$this->_getCellInputElementId(
            '',
            $columnName
        ).'"'.' name="'.$inputName.'" value="'.$columnName.'" '.($column['size']
                ? 'size="'.$column['size'].'"' : '').' class="'.(isset($column['class'])
                ? $column['class'] : 'input-text').'"'.(isset($column['style'])
                ? ' style="'.$column['style'].'"' : '').'/>';
    }
}
