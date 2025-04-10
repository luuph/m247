<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AIImageSearch
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AIImageSearch\Plugin\Elasticsearch7\SearchAdapter;

use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Exception\LocalizedException;

class Adapter
{
    /**
     * @var array
     */
    private static $emptyRawResponse = [
        "hits" =>
        [
            "hits" => []
        ],
        "aggregations" =>
        [
            "price_bucket" => [],
            "category_bucket" =>
            [
                "buckets" => []

            ]
        ]
    ];

    /**
     * Dependency Initilization
     *
     * @param Mapper $mapper
     * @param \Psr\Log\LoggerInterface $logger
     * @param ResponseFactory $responseFactory
     * @param ConnectionManager $connectionManager
     * @param AggregationBuilder $aggregationBuilder
     * @param \Webkul\AIImageSearch\Helper\Data $helper
     * @param QueryContainerFactory $queryContainerFactory
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     */
    public function __construct(
        private Mapper $mapper,
        private \Psr\Log\LoggerInterface $logger,
        private ResponseFactory $responseFactory,
        private ConnectionManager $connectionManager,
        private AggregationBuilder $aggregationBuilder,
        private \Webkul\AIImageSearch\Helper\Data $helper,
        private QueryContainerFactory $queryContainerFactory,
        private \Magento\Framework\App\Request\Http $httpRequest,
        private \Magento\Store\Model\StoreManagerInterface $storeManager,
        private \Magento\Framework\Message\ManagerInterface $messageManager,
        private \Magento\Framework\Session\SessionManagerInterface $session
    ) {
    }

    /**
     * Add seller product to seller collection
     *
     * @param \Magento\Framework\Search\AdapterInterface $subject
     * @param callable $proceed
     * @param RequestInterface $request
     */
    public function aroundQuery(
        \Magento\Framework\Search\AdapterInterface $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        try {
            $catalogSearchUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::DEFAULT_URL_TYPE
            ) . 'catalogsearch/result/?q=';
            $catalogSearchFilterUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::DEFAULT_URL_TYPE
            ) . 'catalogsearch/result/index/?';
            $graphqlSearchUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::DEFAULT_URL_TYPE
            ) . 'graphql';
            $catalogUrllength = strlen($catalogSearchUrl);
            $catalogFilterUrllength = strlen($catalogSearchFilterUrl);
            $graphqlUrllength = strlen($graphqlSearchUrl);
            $currentUrl = $this->storeManager->getStore()->getCurrentUrl();
            if ($catalogSearchFilterUrl == substr($currentUrl, 0, $catalogFilterUrllength) ||
                $catalogSearchUrl == substr($currentUrl, 0, $catalogUrllength) ||
                $graphqlSearchUrl == substr($currentUrl, 0, $graphqlUrllength)
            ) {
                $updatedQuery = $this->mapper->buildQuery($request);
                $fileContentInBase64 = 0;
                if (!empty($updatedQuery['body']['query']['bool']['should'][1]['match']['_search']['query'])) {
                    $searchQuery = $updatedQuery['body']['query']['bool']['should'][1]['match']['_search']['query'];
                    $isBase64Image = substr($searchQuery, 0, 13);
                    if ($isBase64Image == 'base64Image: ') {
                        $fileContentInBase64 = substr($searchQuery, 13);
                    }
                }
                if (!$fileContentInBase64) {
                    $this->session->start();
                    $croppedImagePath = $this->session->getWkAIImageSearchCroppedImage();
                    $imageSearch = $this->session->getWkAIImageSearch();
                    if ($imageSearch && !empty($croppedImagePath)) {
                        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        );
                        $fileContentInBase64 = $this->helper->getFileInBase64Encode($mediaUrl . $croppedImagePath);
                    }
                }
                if ($fileContentInBase64) {
                    $productIds = $this->getRelatedProductIds($fileContentInBase64);
                    $client = $this->connectionManager->getConnection();
                    $aggregationBuilder = $this->aggregationBuilder;
                    $updatedQuery = $this->mapper->buildQuery($request);
                    unset($updatedQuery['body']['query']['bool']['should']);
                    unset($updatedQuery['body']['query']['bool']['minimum_should_match']);
                    if ($catalogSearchFilterUrl != substr($currentUrl, 0, $catalogFilterUrllength)) {
                        unset($updatedQuery['body']['sort']);
                        $updatedQuery['body']['sort'][0]['_script']['type'] = 'number';
                        $setSortOrderAttribute = 'params.sortOrder.indexOf(doc["_id"].value)';
                        $updatedQuery['body']['sort'][0]['_script']['script']['inline'] = $setSortOrderAttribute;
                        $updatedQuery['body']['sort'][0]['_script']['script']['params']['sortOrder'] = $productIds;
                        unset($updatedQuery['body']['sort'][0]['_score']);
                    } else {
                        $requestParams = $this->httpRequest->getParams();
                        if (!empty($requestParams['product_list_dir']) &&
                            empty($requestParams['product_list_order']) &&
                            $requestParams['product_list_dir'] == 'asc'
                        ) {
                            $productIds = array_reverse($productIds);
                            unset($updatedQuery['body']['sort']);
                            $updatedQuery['body']['sort'][0]['_script']['type'] = 'number';
                            $setSortOrderAttribute = 'params.sortOrder.indexOf(doc["_id"].value)';
                            $updatedQuery['body']['sort'][0]['_script']['script']['inline'] = $setSortOrderAttribute;
                            $updatedQuery['body']['sort'][0]['_script']['script']['params']['sortOrder'] = $productIds;
                            unset($updatedQuery['body']['sort'][0]['_score']);
                        }
                    }
                    $updatedQuery['body']['query']['bool']['filter'] = ['ids' => ['values' => $productIds]];
                    $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $updatedQuery]));
                    try {
                        $rawResponse = $client->query($updatedQuery);
                    } catch (\Exception $e) {
                        $this->logger->debug(
                            "Webkul_AIImageSearch::Elasticsearch7_SearchAdapter_Adapter aroundQuery " . $e->getMessage()
                        );
                        $rawResponse = self::$emptyRawResponse;
                    }
                    $rawDocuments = $rawResponse['hits']['hits'] ?? [];
                    $queryResponse = $this->responseFactory->create(
                        [
                            'documents' => $rawDocuments,
                            'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                            'total' => $rawResponse['hits']['total']['value'] ?? 0
                        ]
                    );
                    return $queryResponse;
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug(
                "Webkul_AIImageSearch::Elasticsearch7_SearchAdapter_Adapter aroundQuery " . $e->getMessage()
            );
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $proceed($request);
    }

    /**
     * Get Related Product Ids
     *
     * @param string $fileContentInBase64
     * @return array
     */
    private function getRelatedProductIds($fileContentInBase64)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $collectionName = $this->helper::COLLECTION_NAME;
        $adapter = $this->helper->getAdapter();
        $adapter->intializeClients();
        $adapter->createCollection($collectionName);
        $response = $adapter->createImageEmbeddings($fileContentInBase64);
        if (!empty($response['error'])) {
            throw new LocalizedException(__($response['error']));
        }
        $collection = $adapter->getCollection($collectionName);
        $collectionId = $collection['id'];
        $resultCount = $this->helper->getConfig('no_of_results', $storeId);
        $embedding = [];
        if (!empty($response['data'][0]['embedding'])) {
            $embedding = $response['data'][0]['embedding'];
            $response = $adapter->queryCollectionItems(
                $collectionId,
                [
                    'n_results' => 300,
                    'query_embeddings' => [$embedding],
                    'include' => ["documents", "embeddings", "metadatas", "distances"]
                ]
            );
        }
        $productIds = [];
        $accuracy = $this->helper->getConfig('distance', $storeId) / 2;
        if ($accuracy < 0.3) {
            $accuracy = 0.3;
        }
        $noResultWithAccuracy = false;
        foreach ($response['distances'][0] as $key => $distance) {
            if ($distance < $accuracy && $resultCount > count($productIds)) {
                if (!in_array($response['metadatas'][0][$key]['product_id'], $productIds)) {
                    $productIds[] = $response['metadatas'][0][$key]['product_id'];
                }
                continue;
            }
            if (!count($productIds)) {
                $noResultWithAccuracy = true;
            }
            if ($noResultWithAccuracy == false || $resultCount <= count($productIds)) {
                break;
            } else {
                if (!in_array($response['metadatas'][0][$key]['product_id'], $productIds)) {
                    $productIds[] = $response['metadatas'][0][$key]['product_id'];
                }
            }
        }
        return $productIds;
    }
}
