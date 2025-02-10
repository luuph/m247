<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/
namespace Biztech\Translator\Model\Config\Source;

use Magento\Framework\Setup\Lists;

class Fromlanguage implements \Magento\Framework\Option\ArrayInterface
{

    protected $_lists;
    protected $helperLanguage;

    /**
     * Fromlanguage constructor.
     * @param Lists                               $lists
     * @param \Biztech\Translator\Helper\Language $helperLanguage
     */
    public function __construct(
        Lists $lists,
        \Biztech\Translator\Helper\Language $helperLanguage
    ) {
        $this->_lists = $lists;
        $this->helperLanguage = $helperLanguage;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $languages = $this->helperLanguage->getLanguages();
        $options[] = ['label' => 'Auto detect', 'value' => 'auto'];
        foreach ($languages as $key => $language) {
            $options[] = ['label' => strtoupper($key) . ': ' . $language, 'value' => $key];
        }
        return ($options);
    }
}
