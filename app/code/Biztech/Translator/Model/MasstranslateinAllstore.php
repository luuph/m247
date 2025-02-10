<?php

namespace Biztech\Translator\Model;

use Magento\Framework\Exception\TranslatorException;

/**
 * Inventorysystemtab Crondata model
 */
class MasstranslateinAllstore extends \Magento\Framework\Model\AbstractModel
{
    const MASS_TRANSLATE_IN_ALLSTORE_CRON_JOB_CODE = 'masstranslateinallstorecron';
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
        $this->_init('Biztech\Translator\Model\ResourceModel\MasstranslateinAllstore');
    }
}
