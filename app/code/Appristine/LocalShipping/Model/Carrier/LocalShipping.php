<?php
namespace Appristine\LocalShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class LocalShipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'localshipping';
	
	protected $_logger;
    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;


    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_objectManager = $objectManager;
		$this->_logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
		
		$shippingPrice = $this->getConfigData('price');
		$method = $this->_rateMethodFactory->create();
		$method->setCarrier($this->_code);
		$method->setCarrierTitle($this->getConfigData('title'));
		$method->setMethod($this->_code);
		$method->setMethodTitle($this->getConfigData('name'));
		
        // $city_found = $this->_objectManager->create('\Appristine\LocalShipping\Model\LocalShipping')->getCollection()
        //                                     ->addFieldToFilter('city', strtolower(str_replace(" ", "", $request->getDestCity())));

        $destCity = $request->getDestCity();
        $city = strtolower(str_replace(" ", "", $destCity ?? '')); // Use an empty string if $destCity is null

        $city_found = $this->_objectManager->create('\Appristine\LocalShipping\Model\LocalShipping')
            ->getCollection()
            ->addFieldToFilter('city', $city);


        if(!empty($city_found->getData())){
            $shippingPrice = 0.00;
        }
        //var_dump($shippingPrice);die;
		$method->setPrice($shippingPrice);
		$method->setCost($shippingPrice);
		$result->append($method);
        

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
		
        return [$this->_code=> $this->getConfigData('name')];
    }
}
