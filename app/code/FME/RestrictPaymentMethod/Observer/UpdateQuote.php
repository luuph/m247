<?php
namespace FME\RestrictPaymentMethod\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogRule\Model\RuleFactory;

class UpdateQuote implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
     */
    protected $ruleResource;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $helper;

    /**
     * UpdateQuote Observer constructor.
     * 
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\CatalogRule\Model\ResourceModel\Rule $rule
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \FME\RestrictPaymentMethod\Helper\Data $helper
     * 
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\CatalogRule\Model\ResourceModel\Rule $rule,
        \Magento\Customer\Model\Session $customerSession,
        \FME\RestrictPaymentMethod\Helper\Data $helper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->ruleResource = $rule;
        $this->_customerSession = $customerSession;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {

        if ($this->helper->isEnabledInFrontend()) {
            $ruleIds = array();
            $quoteItem = $observer->getEvent()->getQuoteItem();
            $productId = $quoteItem->getProductId();
            $websiteId = $quoteItem->getStore()->getWebsiteId();
            $quoteId = $quoteItem->getQuoteId();
            $customerGroupId = $this->getGroupId();
            $date = date('Y-m-d');
            $ruleId = null;
            $rules = $this->ruleResource->getRulesFromProduct($date, $websiteId, $customerGroupId, $productId);
            foreach ($rules as $rule) {
                $ruleIds[] = $rule['rule_id'];
            }
            if ($quoteId && !empty($ruleIds)) {
                $quote = $this->quoteRepository->get($quoteId);
                if (!$quote->getIsActive()) {
                    return;
                }
                $oldRuleIds = $quote->getData("fme_applied_rule_ids");
                if (!empty($oldRuleIds)) {
                    $oldRuleIdsArray = explode(", ", $oldRuleIds);
                    $difference = array_merge(array_diff($ruleIds, $oldRuleIdsArray), array_diff($oldRuleIdsArray, $ruleIds));
                    if (!empty($difference)) {
                        $ruleIds = array_merge($ruleIds, $difference);
                    }
                }
                $ruleids = implode(", ", $ruleIds);
                $quote->setData("fme_applied_rule_ids", $ruleids);
                $this->quoteRepository->save($quote);
            }
        }
    }
    public function getGroupId(){
        $customerGroup = 0;
        if ($this->_customerSession->isLoggedIn())
               $customerGroup=$this->_customerSession->getCustomer()->getGroupId();
        return $customerGroup;
    }
       
}
