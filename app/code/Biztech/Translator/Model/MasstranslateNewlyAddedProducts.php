<?php

namespace Biztech\Translator\Model;

use Magento\Framework\Exception\TranslatorException;

/**
 * Inventorysystemtab Crondata model
 */
class MasstranslateNewlyAddedProducts extends \Magento\Framework\Model\AbstractModel
{
    const NEWLY_ADDED_PRODUCT_TRANSLATE_CRON_JOB_CODE = 'masstranslatenewlyaddedproductcron';
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Biztech\Translator\Model\ResourceModel\MasstranslateNewlyAddedProducts');
    }
}
