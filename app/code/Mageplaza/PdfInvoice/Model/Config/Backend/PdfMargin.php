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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\PdfInvoice\Helper\Data as HelperData;

/**
 * Class PdfMargin
 * @package Mageplaza\PdfInvoice\Model\Config\Backend
 */
class PdfMargin extends Value
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * PaperMargin constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param HelperData $helperData
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        HelperData $helperData,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helperData = $helperData;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        $savedValue = $this->getValue();
        if (substr_count($savedValue, ';') + 1 !== 4) {
            throw new ValidatorException(__('Incorrect Pdf Margin Format'));
        }

        $paperMargin = $this->_helperData->getPdfMargin($savedValue, $this->getScopeId());
        foreach ($paperMargin as $key => $input) {
            if (!is_numeric($input) || (float) $input < 0) {
                throw new ValidatorException(__('Pdf Margin %1 Must Be A Positive Float Number', ucfirst($key)));
            }
        }

        return parent::beforeSave();
    }
}
