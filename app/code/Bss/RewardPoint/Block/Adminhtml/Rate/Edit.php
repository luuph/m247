<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\RewardPoint\Block\Adminhtml\Rate;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class edit
 *
 * Bss\RewardPoint\Block\Adminhtml\Rate
 */
class Edit extends Container
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context      $context
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->registry     = $registry;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Department edit block
     *
     * @return void
     
     */
    protected function _construct()
    {
        $this->_objectId   = 'rate_id';
        $this->_blockGroup = 'Bss_RewardPoint';
        $this->_controller = 'adminhtml_rate';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Rate'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form',
                        ],
                    ],
                ],
            ],
            -100
        );
    }

    /**
     * Get header with Department name
     *
     * @return \Magento\Framework\Phrase
     
     */
    public function getHeaderText()
    {
        if ($this->registry->registry('rate_point')->getId()) {
            return __("Edit Rate '%1'", $this->escapeHtml($this->registry->registry('rate_point')->getName()));
        } else {
            return __('New Rate');
        }
    }

    /**
     * Get website
     *
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsites()
    {
        return $this->_storeManager->getWebsites();
    }

    /**
     * Get json base currency of website
     *
     * @return false|string
     */
    public function getJsonBaseCurrencyofWebsite()
    {
        $data = [];
        foreach ($this->getWebsites() as $website) {
            $data[$website->getId()] = $website->getBaseCurrencyCode();
        }
        return json_encode($data);
    }
}
