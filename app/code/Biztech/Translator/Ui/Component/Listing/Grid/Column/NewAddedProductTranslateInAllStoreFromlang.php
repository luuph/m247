<?php
namespace Biztech\Translator\Ui\Component\Listing\Grid\Column;

class NewAddedProductTranslateInAllStoreFromlang extends \Magento\Ui\Component\Listing\Columns\Column
{

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Biztech\Translator\Helper\Language $languagehelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Biztech\Translator\Model\MasstranslateNewlyAddedProductsFactory $masstranslateNewlyAddedProductsFactory,
        array $components = [],
        array $data = []
    ) {
        $this->helperLanguage = $languagehelper;
        $this->_storeManager = $storeManager;
        $this->_masstranslateNewlyAddedProductsFactory = $masstranslateNewlyAddedProductsFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $fromlang = json_decode($item['lang_from']);
                $stores = json_decode($this->_masstranslateNewlyAddedProductsFactory->create()->load($item['id'])->getStoreIds());
                $store_view = '';
                $languages = $this->helperLanguage->getLanguages();
                foreach ($fromlang as $key => $value) {
                    if (!in_array($key, $stores)) {
                        continue;
                    }
                    if ($value=='') {
                        $store_view.= ucfirst("auto detect")."<br>";
                    } else {
                        $store_view.= ucfirst($languages[$value])."<br>";
                    }
                }
                $item['lang_from'] = $store_view;
            }
        }
        return $dataSource;
    }
}
