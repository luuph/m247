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

namespace Webkul\Walletsystem\Ui\Component;

/**
 * Class ExportButton
 * Button to export the transactions
 */
class ExportButton extends \Magento\Ui\Component\AbstractComponent
{
    /**
     * Component name
     */
    public const  NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Initialize dependencies
     *
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Request\Http $request,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

   /**
    * Prepare
    */
    public function prepare()
    {
        $customerId = $this->request->getParam('customer_id');
        if (isset($customerId)) {
            $configData = $this->getData('config');
            if (isset($configData['options'])) {
                $configOptions = [];
                foreach ($configData['options'] as $configOption) {
                    $configOption['url'] = $this->urlBuilder->getUrl(
                        $configOption['url'],
                        ["customer_id"=>$customerId]
                    );
                    $configOptions[] = $configOption;
                }
                $configData['options'] = $configOptions;
                $this->setData('config', $configData);
            }
        }
        $senderType = $this->request->getParam('sender_type');
        if (isset($senderType)) {
            $configData = $this->getData('config');
            if (isset($configData['options'])) {
                $configOptions = [];
                foreach ($configData['options'] as $configOption) {
                    $configOption['url'] = $this->urlBuilder->getUrl(
                        $configOption['url'],
                        ["sender_type"=>$senderType]
                    );
                    $configOptions[] = $configOption;
                }
                $configData['options'] = $configOptions;
                $this->setData('config', $configData);
            }
        }
        parent::prepare();
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
