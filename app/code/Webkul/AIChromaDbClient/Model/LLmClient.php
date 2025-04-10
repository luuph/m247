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

class LLmClient
{
    protected const TEXT_EMBED_ENDPOINT = '/v1/embeddings';

    protected const TEXT_QUERY_ENDPOINT = '/v1/embeddings';

    protected const IMAGE_EMBED_ENDPOINT = '/v1/embeddings';

    /**
     * The base url for the LLm server.
     * @var string
     */
    protected $_baseUrl;

    /**
     * The host where the LLm server is running.
     * @var string
     */
    protected string $_host = '';

    /**
     * The port to send requests to.
     * @var int
     */
    protected int $_port = 0;

    /**
     * The host of the LLm server
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
     * The port of the LLm server
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
     * The base URL of the LLm server
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $this->_baseUrl = $this->_baseUrl ? $this->_baseUrl : $this->_host . ':' . $this->_port;
        return $this->_baseUrl;
    }

    /**
     * The URL of the LLm text embedding endpoint
     *
     * @return string
     */
    public function getTextEmbeddingUrl()
    {
        return $this->getBaseUrl() . self::TEXT_EMBED_ENDPOINT;
    }

    /**
     * The URL of the LLm image embedding endpoint
     *
     * @return string
     */
    public function getImageEmbeddingUrl()
    {
        return $this->getBaseUrl() . self::IMAGE_EMBED_ENDPOINT;
    }

    /**
     * The URL of the LLm text query endpoint
     *
     * @return string
     */
    public function getTextQueryUrl()
    {
        return $this->getBaseUrl() . self::TEXT_QUERY_ENDPOINT;
    }
}
