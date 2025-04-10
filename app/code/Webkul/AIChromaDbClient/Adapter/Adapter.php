<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIChromaDbClient
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AIChromaDbClient\Adapter;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Magento\Framework\Url\DecoderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\AIChromaDbClient\Model\ChromaDbClient;
use Webkul\AIChromaDbClient\Model\HTTP\CurlClient;
use Webkul\AIChromaDbClient\Model\LLmClient;
use Magento\Framework\Encryption\EncryptorInterface;

class Adapter
{
    /**
     * @var JsonHelper
     */
    public $jsonHelper;

    /**
     * @var CurlClient
     */
    public $curlClient;

    /**
     * @var ChromaDbClient
     */
    public $chromaDb;

    /**
     * @var LLmClient
     */
    public $llm;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @param JsonHelper            $jsonHelper
     * @param CurlClient            $curlClient
     * @param ChromaDbClient        $chromaDb
     * @param LLmClient             $llm
     * @param DecoderInterface      $decoder
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface    $encryptor
     */
    public function __construct(
        JsonHelper $jsonHelper,
        CurlClient $curlClient,
        ChromaDbClient $chromaDb,
        LLmClient $llm,
        DecoderInterface $decoder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface    $encryptor
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->curlClient = $curlClient;
        $this->chromaDb = $chromaDb;
        $this->llm = $llm;
        $this->decoder = $decoder;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->intializeClients();
    }

    /**
     * Get Admin configuration for AIChromaDbClient
     *
     * @param string $groupFieldSignature
     * @return string
     */
    public function getConfigValue($groupFieldSignature)
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        return $this->_scopeConfig->getValue(
            "aichromadbclient/$groupFieldSignature",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * GetApiKey function
     *
     * @return string
     */
    public function getApiKey()
    {
        $encryptedApiKey = $this->getConfigValueApiKey('aichromadbclient/general/apikey');
        return $this->decryptApiKey($encryptedApiKey);
    }

    /**
     * DecryptApiKey function
     *
     * @param string $encryptedApiKey
     * @return \Magento\Framework\Encryption\Encryptor
     */
    public function decryptApiKey($encryptedApiKey)
    {
        return $this->encryptor->decrypt($encryptedApiKey);
    }

    /**
     * Undocumented function
     *
     * @param string $path
     * @return string
     */
    public function getConfigValueApiKey($path)
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * To Add the host and port for the chromaDb and LLm
     *
     * @return void
     */
    public function intializeClients()
    {
        $chromaDbBaseUrl = $this->getConfigValue('general/chromadb_base_url');
        $llmBaseUrl = $this->getConfigValue('general/llm_base_url');
        $this->chromaDb = $this->chromaDb->setBaseUrl($chromaDbBaseUrl);
        $this->llm = $this->llm->setBaseUrl($llmBaseUrl);
    }

    /**
     * Curl Request will be sent to the server
     *
     * @param string $uri
     * @param string $method
     * @param string|null $params
     *
     * @return null|bool|array
     */
    public function curlRequest($uri, $method = 'GET', $params = null)
    {
        $apiKey = $this->getApiKey();
        $this->curlClient->addHeader("Content-Type", "application/json");
        $this->curlClient->addHeader("Authorization", "Bearer $apiKey");
        if ($method == 'POST') {
            $this->curlClient->post(
                uri: $uri,
                params: $params
            );
        } elseif ($method == 'DELETE') {
            $this->curlClient->delete(
                uri: $uri
            );
        } else {
            $this->curlClient->get(
                uri: $uri
            );
        }
        // handle curl response
        $result = $this->curlClient->getBody();
        if ($this->curlClient->getStatus() <= 200) {
            $unSerializedResult = $this->jsonHelper->unserialize($result);
            return $unSerializedResult;
        } else {
            throw new LocalizedException(__($result));
        }
    }

    /**
     * Create a new Collection in ChromaDb
     *
     * @param string $collectionName
     * @param array $metaData
     * @param bool $getOrCreate
     *
     * @return array
     * @throws Exception|LocalizedException
     */
    public function createCollection(string $collectionName, array $metaData = [], bool $getOrCreate = true)
    {
        // curl request body
        if (empty($collectionName)) {
            throw new LocalizedException(__("Collection name is required field !!"));
        }
        $requestBody['name'] = $collectionName;
        $requestBody['get_or_create'] = $getOrCreate;
        !empty($metaData) ? $requestBody['metadata'] = $metaData : '';
        $requestUrl = $this->chromaDb->getCollectionEndpoint();
        $postFields = $this->jsonHelper->serialize($requestBody);
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * Get a Collection in ChromaDb by Name
     *
     * @param string $collectionName
     *
     * @return array
     * @throws Exception|LocalizedException
     */
    public function getCollection(string $collectionName)
    {
        // curl request body
        if (empty($collectionName)) {
            throw new LocalizedException(__("Collection name is required field."));
        }
        $requestUrl = $this->chromaDb->getCollectionEndpoint() . $collectionName;
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'GET');
    }

    /**
     * Get all the Collections in ChromaDb
     *
     * @return array
     * @throws Exception|LocalizedException
     */
    public function getListCollections()
    {
        $requestUrl = $this->chromaDb->getCollectionEndpoint();
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'GET');
    }
    
    /**
     * Delete a Collection in ChromaDb by Name
     *
     * @param string $collectionName
     * @return array
     * @throws Exception|LocalizedException
     */
    public function deleteCollection(string $collectionName)
    {
        // curl request body
        if (empty($collectionName)) {
            throw new LocalizedException(__("Collection name is required field."));
        }
        $requestUrl = $this->chromaDb->getCollectionEndpoint() . $collectionName;
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'DELETE');
    }

    /**
     * Get count collection Items by collectionId
     *
     * @param string $collectionId
     * @return array
     * @throws Exception|LocalizedException
     */
    public function countCollectionItems(string $collectionId)
    {
        // curl request body
        if (empty($collectionId)) {
            throw new LocalizedException(__("Collection Id is required field."));
        }
        $requestUrl = $this->chromaDb->getCollectionEndpoint(true) . '/count';
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'GET');
    }

    /**
     * Upsert Data To Collection Items
     *
     * @param string $collectionId
     * @param array  $requestBody
     * @return array
     * @throws Exception|LocalizedException
     */
    public function upsertCollectionItems(string $collectionId, array $requestBody)
    {
        // curl request body
        if (empty($collectionId)) {
            throw new LocalizedException(__("Collection Id is required field."));
        }
        if (empty($requestBody['ids'])) {
            throw new LocalizedException(__("RequestBody Ids field is required field."));
        }
        $this->chromaDb->setCollectionId($collectionId);
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->chromaDb->getCollectionEndpoint(true) . '/upsert';
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * Add Data To Collection Items
     *
     * @param string $collectionId
     * @param array $requestBody
     * @return array
     * @throws Exception|LocalizedException
     */
    public function addCollectionItems(string $collectionId, array $requestBody)
    {
        // curl request body
        if (empty($collectionId)) {
            throw new LocalizedException(__("Collection Id is required field."));
        }
        if (empty($requestBody['ids'])) {
            throw new LocalizedException(__("RequestBody Ids field is required field."));
        }
        $this->chromaDb->setCollectionId($collectionId);
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->chromaDb->getCollectionEndpoint(true) . '/add';
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * Get Data from Collection Items
     *
     * @param string $collectionId
     * @param array $requestBody
     * @return array
     * @throws Exception|LocalizedException
     */
    public function getCollectionItems(string $collectionId, array $requestBody)
    {
        // curl request body
        if (empty($collectionId)) {
            throw new LocalizedException(__("Collection Id is required field."));
        }
        $this->chromaDb->setCollectionId($collectionId);
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->chromaDb->getCollectionEndpoint(true) . '/get';
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * Update Data To Collection Items
     *
     * @param string $collectionId
     * @param array  $requestBody
     * @return array
     * @throws Exception|LocalizedException
     */
    public function updateCollectionItems(string $collectionId, array $requestBody)
    {
        // curl request body
        if (empty($collectionId)) {
            throw new LocalizedException(__("Collection Id is required field."));
        }
        if (empty($requestBody['ids'])) {
            throw new LocalizedException(__("RequestBody Ids field is required field."));
        }
        $this->chromaDb->setCollectionId($collectionId);
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->chromaDb->getCollectionEndpoint(true) . '/update';
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * Delete Data To Collection Items
     *
     * @param string $collectionId
     * @param array  $requestBody
     * @return array
     * @throws Exception|LocalizedException
     */
    public function deleteCollectionItems(string $collectionId, array $requestBody)
    {
        // curl request body
        if (empty($collectionId)) {
            throw new LocalizedException(__("Collection Id is required field."));
        }
        $this->chromaDb->setCollectionId($collectionId);
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->chromaDb->getCollectionEndpoint(true) . '/delete';
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * Query Data of Collection Items
     *
     * @param string $collectionId
     * @param array  $requestBody
     * @return array
     * @throws Exception|LocalizedException
     */
    public function queryCollectionItems(string $collectionId, array $requestBody)
    {
        // curl request body
        if (empty($collectionId)) {
            throw new LocalizedException(__("Collection Id is required field."));
        }
        if (empty($requestBody['query_embeddings'])) {
            throw new LocalizedException(__("RequestBody QueryEmbedding field is required field."));
        }
        $this->chromaDb->setCollectionId($collectionId);
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->chromaDb->getCollectionEndpoint(true) . '/query';
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * To Generate Text Embeddings
     *
     * @param string $text
     * @return array
     * @throws Exception|LocalizedException
     */
    public function createTextEmbeddings(string $text)
    {
        if (empty($text)) {
            throw new LocalizedException(__("Text is required field for embedding"));
        }
        $requestBody['input'] = $text;
        $requestBody['model'] = 'text-embeddings';
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->llm->getTextEmbeddingUrl();
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * To Generate Text Embeddings
     *
     * @param string $imageBase64
     *
     * @return array
     * @throws Exception|LocalizedException
     */
    public function createImageEmbeddings(string $imageBase64)
    {
        if (!$this->isBase64($imageBase64)) {
            throw new LocalizedException(__("Provided String is not valid Base64"));
        }
        if (empty($imageBase64)) {
            throw new LocalizedException(__("Image's Base64 is required field for embedding"));
        }
        $requestBody['input'] = $imageBase64;
        $requestBody['model'] = 'image-model';
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->llm->getImageEmbeddingUrl();
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }

    /**
     * To Check if the string is a base64 encoded string
     *
     * @param string $base64String
     * @return
     */
    private function isBase64(string $base64String)
    {
        // Decode the string in strict mode and check the results
        $decoded = $this->decoder->decode($base64String);
        if (false === $decoded || empty($decoded)) {
            return false;
        }
        return true;
    }

    /**
     * To Generate Text Embeddings and extract data
     *
     * @param string $text
     * @return array
     * @throws Exception|LocalizedException
     */
    public function textQueryEmbeddings(string $text)
    {
        if (empty($text)) {
            throw new LocalizedException(__("Text is required field for embedding and extract data"));
        }
        $requestBody['input'] = $text;
        $requestBody['model'] = 'query-embeddings';
        $postFields = $this->jsonHelper->serialize($requestBody);
        $requestUrl = $this->llm->getTextQueryUrl();
        // curl request
        return $this->curlRequest(uri: $requestUrl, method: 'POST', params: $postFields);
    }
}
