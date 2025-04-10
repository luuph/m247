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

namespace Webkul\AIChromaDbClient\Model;

class ChromaDbClient
{
    protected const COLLECTION_ENDPOINT = '/api/v1/collections/';

    /**
     * The base url for the ChromaDB server.
     * @var string
     */
    protected string $_baseUrl;

    /**
     * The host where the ChromaDB server is running.
     * @var string
     */
    protected string $_host = '';

    /**
     * The port to send requests to.
     * @var int
     */
    protected int $_port = 0;

    /**
     * The database to use for the instance.
     * @var string
     */
    protected string $_database = 'default_database';

    /**
     * The tenant to use for the instance.
     * @var string
     */
    protected string $_tenant = 'default_tenant';

    /**
     * The collection Id to use for fetching collection detail.
     * @var string
     */
    protected string $_collectionId = '';

    /**
     * The host of the ChromaDB server
     *
     * @param string $host
     * @return self
     */
    public function setHost(string $host): self
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * The port of the ChromaDB server
     *
     * @param string $port
     * @return self
     */
    public function setPort(int $port): self
    {
        $this->_port = $port;
        return $this;
    }

    /**
     * The database of the ChromaDB server
     *
     * @param string $database
     * @return self
     */
    public function setDatabase(string $database): self
    {
        $this->_database = $database;
        return $this;
    }

    /**
     * The tenant of the ChromaDB server
     *
     * @param string $tenant
     * @return self
     */
    public function setTenant(string $tenant): self
    {
        $this->_tenant = $tenant;
        return $this;
    }

    /**
     * The tenant of the ChromaDB server
     *
     * @param string $collectionId
     * @return self
     */
    public function setCollectionId(string $collectionId): self
    {
        $this->_collectionId = $collectionId;
        return $this;
    }

    /**
     * Set Base Url for request
     *
     * @param string $baseUrl
     * @return self
     */
    public function setBaseUrl($baseUrl): self
    {
        $this->_baseUrl = $baseUrl;
        return $this;
    }

    /**
     * The base URL of the ChromaDB server
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $this->_baseUrl = $this->_baseUrl ? $this->_baseUrl : $this->_host . ':' . $this->_port;
        return $this->_baseUrl;
    }

    /**
     * The Connection Url for CollectionEndpoint
     *
     * @param bool $withCollectionId
     * @return string
     */
    public function getCollectionEndpoint($withCollectionId = false)
    {
        return $withCollectionId ?
            $this->getBaseUrl() . self::COLLECTION_ENDPOINT . $this->_collectionId :
            $this->getBaseUrl() . self::COLLECTION_ENDPOINT;
    }
}
