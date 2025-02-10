<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model;

use Magento\Framework\App\Language\Dictionary;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\State;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Translate\ResourceInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\FileSystem;
use Biztech\Translator\Model\System\Config\Locales;

class Search extends \Magento\Framework\Translate
{
    protected $_translateLocale;
    protected $_storeId;
    protected $_interface;
    protected $_modules;
    protected $_viewFileSystem;
    protected $packDictionary;
    
    protected $locales;

    public function __construct(
        DesignInterface $viewDesign,
        FrontendInterface $cache,
        FileSystem $viewFileSystem,
        ModuleList $moduleList,
        Reader $modulesReader,
        ScopeResolverInterface $scopeResolver,
        ResourceInterface $translate,
        ResolverInterface $locale,
        State $appState,
        \Magento\Framework\Filesystem $filesystem,
        RequestInterface $request,
        Csv $csvParser,
        Locales $locales,
        Dictionary $packDictionary
    ) {
        $this->locales = $locales;
        parent::__construct($viewDesign, $cache, $viewFileSystem, $moduleList, $modulesReader, $scopeResolver, $translate, $locale, $appState, $filesystem, $request, $csvParser, $packDictionary);
    }

    /**
     * @param $string
     * @param $locale
     * @param $modules
     * @param $interface
     * @return array
     */
    public function searchString($string, $locale, $modules, $interface)
    {
        $results = [];
        $stringPattern = '^(?i)' . $string . '^';

        if (!empty($string)) {
            $this->_modules = $modules;
            $this->_interface = $interface;
            $this->_data = [];
            $resultArray = [];
            $temp = [];
            if ($locale == 'all') {
                $this->setLocale('en_US');
            } else {
                $this->setLocale($locale);
            }
            $this->_loadTranslations($locale);
            $results = $this->_matchTranslationInArray($stringPattern);
            if (isset($results['msg'])) {
                $result['msg'] = 'true';
                return $result;
            }
            if (count($results) >= 1000) {
                $result['warning'] = 'true';
                return $result;
            } else {
                foreach ($results as $key => $_result) {
                    foreach ($_result as $locale => $translation) {
                        $translation['key'] = $key;
                        $temp['locale'] = $locale;
                        $temp['translation'] = $translation;
                        $resultArray[] = $temp;
                    }
                }
                $results = $resultArray;
            }
            return $results;
        } else {
            return $results;
        }
    }

    /**
     * @param $locale
     * @param null $area
     * @param bool $forceReload
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _loadData($locale, $area = null, $forceReload = false)
    {
        $this->_cleanCache();

        $this->setConfig(
            ['area' => isset($area) ? $area : $this->_appState->getAreaCode()]
        );

        if (!$forceReload) {
            $this->_data = $this->_loadCache();
            if ($this->_data !== false) {
                return $this;
            }
        }
        $this->_data = [];

        $this->_loadTranslations($locale);
        $this->_loadThemeTranslation();
        $this->_loadPackTranslation();
        $this->_loadDbTranslation();

        $this->_saveCache();

        return $this;
    }

    /**
     * @param $locale
     * @return $this
     */
    private function _loadTranslations($locale)
    {
        $configModules = $this->_moduleList->getAll();

        if ($locale == 'all') {
            foreach ($this->locales->toOptionArray() as $key => $value) {
                if ($this->_modules == 'all') {
                    $this->loadModuleTranslationByModulesList($this->_moduleList->getNames());
                } else {
                    $d = $this->_loadModuleTranlsation($this->_modules, $key, $forceReload = true);
                }
            }
        } else {
            if ($this->_modules == 'all') {
                $this->loadModuleTranslationByModulesList($this->_moduleList->getNames());
            } else {
                $this->_loadModuleTranlsation($this->_modules, $locale, $forceReload = true);
            }
        }

        return $this;
    }

    /**
     * @param $module
     * @param $locale
     * @param $forceReload
     * @return $this
     */
    private function _loadModuleTranlsation($module, $locale, $forceReload)
    {
        $moduleFilePath = $this->_getModuleTranslationFile($module, $this->getLocale());
        $this->_addDataToTranslate($this->_getFileData($moduleFilePath), $module, 'MODULE');
        return $this;
    }

    /* from \Magento\Framework\Translate */

    protected function loadModuleTranslationByModulesList(array $modules)
    {
        foreach ($modules as $module) {
            $moduleFilePath = $this->_getModuleTranslationFile($module, $this->getLocale());
            $this->_addDataToTranslate($this->_getFileData($moduleFilePath), $module, 'MODULE');
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function _loadDbTranslation()
    {
        $data = $this->_translateResource->getTranslationArray(null, $this->getLocale());
        $this->_addDataToTranslate($data, false, 'DATABASE');
        return $this;
    }


    protected function _loadPackTranslation()
    {
        $data = $this->packDictionary->getDictionary($this->getLocale());
        $this->_addDataToTranslate($data, false, 'PackTranslation');
    }

    /**
     * @return $this
     */
    protected function _loadThemeTranslation()
    {
        if (!$this->_config['theme']) {
            return $this;
        }

        $file = $this->_getThemeTranslationFile($this->getLocale());
        if ($file) {
            $this->_addDataToTranslate($this->_getFileData($file), false, 'THEME');
        }
        return $this;
    }

    /* end \Magento\Framework\Translate */

    /**
     * @param $stringPattern
     * @return array
     */
    private function _matchTranslationInArray($stringPattern)
    {
        $results = [];
        $locales = $this->locales->toOptionArray();
        if (isset($locales['all']) || isset($local['en_US'])) {
            unset($locales['all']);
            unset($locales['en_US']);
        }
        if (!empty($this->getData())) {
            foreach ($this->getData() as $string => $locale) {
                foreach ($locale as $key => $data) {
                    $searchArray[$string . '+=' . $key] = $data['translate'];
                }
            }
        } else {
            $results['msg'] = 'No Data';
            return $results;
        }
        $searchResult = preg_grep($stringPattern, $searchArray);
        if (!empty($searchResult)) {
            foreach ($searchResult as $key => $value) {
                $key = explode('+=', $key);
                $results[$key[0]] = $this->getData()[$key[0]];
                foreach ($results[$key[0]] as $locale => $array) {
                    if ($array['translate'] != $value) {
                        unset($results[$key][0]);
                    }
                }
            }
        }
        return $results;
    }

    /**
     * @param $data
     * @param $moduleName
     * @param $translationSource
     * @return $this
     */
    protected function _addDataToTranslate($data, $moduleName, $translationSource)
    {
        $local = $this->getLocale();
        $d = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $key = str_replace('""', '"', $key ?? '');
                $value = str_replace('""', '"', $value ?? '');
                $this->_data[$key][$local] = [
                    'translate' => $value,
                    'source' => $translationSource . ' (' . $moduleName . ') '
                ];

                $d[$key][$local] = [
                    'translate' => $value,
                    'source' => $translationSource . ' (' . $moduleName . ') '
                ];
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function _cleanCache()
    {
        $this->_cache->remove($this->getCacheId(true));
        return $this;
    }

    /**
     * @param string $locale
     * @return mixed
     */
    protected function _getThemeTranslationFile($locale)
    {
        return $this->_viewFileSystem->getLocaleFileName(
            'i18n' . '/' . $locale . '.csv',
            ['area' => $this->getConfig('area')]
        );
    }
}
