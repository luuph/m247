<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model\System\Config;

use Magento\Framework\Module\ModuleListInterface;

class Magemodules
{
    protected $moduleList;

    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $modules[] = ['label' => __('All'), 'value' => 'all'];
        $moduleKeys = array_keys((array)$this->moduleList->getAll());
        foreach ($moduleKeys as $key => $className) {
            $modules[] = ['label' => $className, 'value' => $className];
        }
        return $modules;
    }

    /**
     * @return array
     */
    public function getInterfaceArray()
    {
        $options = [];
        $options[] = ['label' => __('Frontend'), 'value' => 'frontend'];
        $options[] = ['label' => __('Admin HTML'), 'value' => 'adminhtml'];
        return $options;
    }
}
