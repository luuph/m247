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

namespace Webkul\AIChromaDbClient\Model\HTTP;

use Magento\Framework\HTTP\Client\Curl;

class CurlClient extends Curl
{
    /**
     * Make GET request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     * @return void
     */
    public function delete($uri)
    {
        $this->makeRequest("DELETE", $uri);
    }
}
