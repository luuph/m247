<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\App\Config\ValueFactory;

class Config extends \Magento\Framework\DataObject
{
    protected $storeManager;
    protected $scopeConfig;
    protected $backendModel;
    protected $transaction;
    protected $configValueFactory;
    protected $storeId;
    protected $storeCode;

    /**
     * Config constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ValueInterface $backendModel
     * @param Transaction $transaction
     * @param ValueFactory $configValueFactory
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ValueInterface $backendModel,
        Transaction $transaction,
        ValueFactory $configValueFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->backendModel = $backendModel;
        $this->transaction = $transaction;
        $this->configValueFactory = $configValueFactory;
//        $this->storeId = (int)$this->storeManager->getStore()->getId();
//        $this->storeCode = $this->storeManager->getStore()->getCode();
    }


    /**
     * @param $path
     * @return mixed
     */
//    public function getCurrentStoreConfigValue($path)
//    {
//        return $this->scopeConfig->getValue($path, 'store', $this->storeCode);
//    }


    /**
     * @param $path
     * @param $value
     * @throws bool
     */
//    public function setCurrentStoreConfigValue($path, $value)
//    {
//        $data = [
//            'path' => $path,
//            'scope' => 'stores',
//            'scope_id' => $this->storeId,
//            'scope_code' => $this->storeCode,
//            'value' => $value,
//        ];
//
//        $this->backendModel->addData($data);
//        $this->transaction->addObject($this->backendModel);
//        $this->transaction->save();
//    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }
}
