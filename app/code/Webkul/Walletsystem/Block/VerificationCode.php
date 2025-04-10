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

namespace Webkul\Walletsystem\Block;

use Webkul\Walletsystem\Model\ResourceModel\Walletrecord;

/**
 * Webkul Walletsystem Class
 */
class VerificationCode extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Walletrecord
     */
    private $walletrecordModel;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Walletrecord\CollectionFactory $walletrecordModel
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Serialize\Serializer\Base64Json $base64
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Walletrecord\CollectionFactory $walletrecordModel,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->walletrecordModel = $walletrecordModel;
        $this->walletHelper = $walletHelper;
        $this->pricingHelper = $pricingHelper;
        $this->base64 = $base64;
    }

    /**
     * Use to get current url.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * GetIsSecure check is secure or not
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    /**
     * Get transfered parameterd passed in request
     *
     * @return array
     */
    public function getTransferParameters()
    {
        $params = [];
        $getEncodedParamData = $this->getRequest()->getParam('parameter');
        $params = $this->base64->unserialize(urldecode($getEncodedParamData));

        return $params;
    }

    /**
     * Get remaining total of a customer
     *
     * @param int $customerId
     * @return string
     */
    public function getWalletRemainingTotal($customerId)
    {
        $remainingAmount = 0;
        $walletRecordCollection = $this->walletrecordModel->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $record) {
                $remainingAmount = $record->getRemainingAmount();
            }
        }
        return $this->pricingHelper
            ->currency($remainingAmount, true, false);
    }
}
