<?php

namespace Acx\Backend\Model\System\Config\Backend;

use Magento\Framework\App\Config\Storage\WriterInterface;

class SaveConfig extends \Magento\Framework\App\Config\Value
{

    private $writerInterface;
    
    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param WriterInterface $writerInterface
     * @param array $data
     */
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        WriterInterface $writerInterface,
        array $data = []
            
    ) {
        $this->writerInterface = $writerInterface;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $path = $this->getPath();
        $result = [];
        
        if($path == 'csp/mode/storefront/report_uri'){
            $this->writerInterface->save('csp/mode/storefront/report_uri', $value);
            $this->writerInterface->save('csp/mode/admin/report_uri', $value);
        }
        return $this;
    }
}