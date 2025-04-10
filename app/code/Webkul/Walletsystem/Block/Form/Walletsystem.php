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

namespace Webkul\Walletsystem\Block\Form;

/**
 * Webkul Walletsystem Block
 */
class Walletsystem extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_Walletsystem::form/walletsystem.phtml';

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Webkul\Knockout\Model\WalletPaymentConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $checkoutModel;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Walletsystem\Model\WalletPaymentConfigProvider $configProvider
     * @param \Magento\Framework\Serialize\Serializer\Json $serializeJson
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializerInterface
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $checkoutModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Walletsystem\Model\WalletPaymentConfigProvider $configProvider,
        \Magento\Framework\Serialize\Serializer\Json $serializeJson,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface = null,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $checkoutModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->configProvider = $configProvider;
        $this->serializeJson = $serializeJson;
        $this->serializer = $serializerInterface ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\JsonHexTag::class);
        $this->checkoutModel = $checkoutModel;
    }

    /**
     * Get Js Layout
     *
     * @return object
     */
    public function getJsLayout()
    {
        return $this->serializer->serialize($this->jsLayout);
    }

    /**
     * Serialize json
     *
     * @param Array $data
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function serializeJson($data)
    {
        $this->serializeJson->serialize($data);
    }

    /**
     * Get customer config
     *
     * @return mixed
     */
    public function getCustomConfig()
    {
        
        return $this->configProvider->getConfig();
    }

    /**
     * Check order is MultiShipping
     *
     * @return bool
     */
    public function getIsMultiShipping()
    {
        return $this->checkoutModel->getCheckoutSession()->getQuote()->getIsMultiShipping();
    }
}
