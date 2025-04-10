<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Controller\Plugin\Order\Creditmemo;

/**
 * Webkul Walletsystem Class
 */
class Save
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Initialize dependencies
     *
     * @param MagentoFrameworkAppRequestHttp $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }
    /**
     * Before Execute
     *
     * @param \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save $subject
     */
    public function beforeExecute(
        \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save $subject
    ) {
        $params = $this->request->getPost();
        $params->creditmemo['do_offline']=1;
        $this->request->setPost($params);
        $params = $this->request->getPost();
    }
}
