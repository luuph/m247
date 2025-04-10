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

namespace Webkul\Walletsystem\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;

/**
 * Class Options
 */
class PayeeStatus implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @var array
     */
    protected $data;
    
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    
    /**
     *
     * @var UrlPath
     */
    protected $urlPath;
    
    /**
     * @var string
     */
    protected $paramName;
    
    /**
     * @var array
     */
    protected $additionalData = [];

   /**
    * Constructor
    *
    * @param UrlInterface $urlBuilder
    * @param array $data
    */
    public function __construct(
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        if ($this->options === null) {
            $options = $this->getOptionArray();
            $this->prepareData();
            foreach ($options as $option) {
                $this->options[$option['value']] = [
                    'type' => 'payee_status' . $option['value'],
                    'label' => __($option['label']),
                ];
                $this->options[$option['value']]['url'] = $this->urlBuilder->getUrl(
                    $this->urlPath,
                    [$this->paramName => $option['value']]
                );
                $this->options[$option['value']] = array_merge_recursive(
                    $this->options[$option['value']],
                    $this->additionalData
                );
            }
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }

    /**
     * Get Option Array function
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [
            [
                'value' => \Webkul\Walletsystem\Model\WalletPayee::PAYEE_STATUS_ENABLE,
                'label' => 'Enabled'
            ], [
                'value' => \Webkul\Walletsystem\Model\WalletPayee::PAYEE_STATUS_DISABLE,
                'label' => 'Disabled'
            ]
        ];
        return $options;
    }
}
