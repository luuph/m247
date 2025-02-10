<?php
namespace Biztech\Translator\Module\I18n\Dictionary;

class Generator extends \Magento\Setup\Module\I18n\Dictionary\Generator
{
    /**
     * Get Directory parser
     *
     * @param bool $withContext
     * @return \Magento\Setup\Module\I18n\ParserInterface
     */
    public function getDirectoryParser($withContext)
    {
        return $withContext ? $this->contextualParser : $this->parser;
    }
}
