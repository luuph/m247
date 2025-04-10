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

namespace Webkul\Walletsystem\Ui\Component\Listing\Column;
 
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Webkul\Walletsystem\Model\Wallettransaction;
 
class BankTransferActions extends Column
{
    /** Url path */
    public const  VIEWTRANSACTIONPATH = 'walletsystem/wallet/view';
    public const  BANKTRANSFERUPDATEPATH = 'walletsystem/wallet/banktransfer';
    public const  BANKTRANSFERCANCELPATH = 'walletsystem/wallet/disapprove';
 
    /** @var UrlInterface */
    protected $urlBuilder;
 
    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     * @param string             $viewUrl
     * @param string             $cancelUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $viewUrl = self::VIEWTRANSACTIONPATH,
        $cancelUrl = self::BANKTRANSFERCANCELPATH
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->viewUrl = $viewUrl;
        $this->cancelUrl = $cancelUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                            $this->viewUrl,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __("View Transaction")
                    ];
                    $item[$name]['wk-ws-cancel-transaction'] = [
                        'href' => "#".$item['entity_id'],
                        'label' => __("Cancel Transaction"),
                    ];
                    $item[$name]['update'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::BANKTRANSFERUPDATEPATH,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __("Approve Transaction Status"),
                        'confirm' => [
                            'title' => __("Approve Transaction Status"),
                            'message' => __("Are you sure to update status of Transaction?")
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
