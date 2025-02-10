<?php
namespace Biztech\Translator\Ui\Component\Listing\Grid\Column;

class MasstranslateInAllStoreTolang extends \Magento\Ui\Component\Listing\Columns\Column
{

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Biztech\Translator\Helper\Language $languagehelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Biztech\Translator\Model\MasstranslateinAllstoreFactory $masstranslateinAllstoreFactory,
        array $components = [],
        array $data = []
    ) {
        $this->helperLanguage = $languagehelper;
        $this->_storeManager = $storeManager;
        $this->_masstranslateinAllstoreFactory = $masstranslateinAllstoreFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $fromlang = json_decode($item['lang_to']);
                $stores = json_decode($this->_masstranslateinAllstoreFactory->create()->load($item['id'])->getStoreIds());
                $store_view = '';
                $languages = $this->helperLanguage->getLanguages();
                foreach ($fromlang as $key => $value) {
                    if (!in_array($key, $stores)) {
                        continue;
                    }
                    $store_view.= ucfirst($languages[$value])."<br>";
                }
                $item['lang_to'] = $store_view;
            }
        }
        return $dataSource;
    }
}
