<?php

declare(strict_types=1);

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
 * @package    Bss_Core
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Core\Block\Adminhtml;

use Bss\Core\Helper\Api;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Serialize\Serializer\Json;

class Extensions extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Bss_Core::extensions.phtml';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Api
     */
    private $apiHelper;

    private $extensionRepository;

    public function __construct(
        \Bss\Core\Model\ExtensionInfoRepository $extensionRepository,
        Template\Context $context,
        Json $serializer,
        Api $apiHelper,
        array $data = [],
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->apiHelper = $apiHelper;
        $this->extensionRepository = $extensionRepository;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    public function getPromotionsDataJson(): string
    {
        return $this->serializer->serialize($this->getPromotionsData());
    }

    private function getPromotionsData(): array
    {
        return $this->prepareDataPromotions($this->extensionRepository->getExtensionInfo('promotion'));
    }

    /**
     * Add utm code to url
     *
     * @param array $dataPromotions
     * @return mixed
     */
    public function prepareDataPromotions($dataPromotions)
    {
        foreach ($dataPromotions as &$dataPromotion) {
            foreach ($dataPromotion['modules'] as &$module) {
                if ($module['url'] && filter_var($module['url'], FILTER_VALIDATE_URL)) {
                    $currentUrl = parse_url($module['url']);
                    if (isset($currentUrl['query']) && preg_match('~(utm_source|utm_medium)~', $currentUrl['query'])) {
                        continue;
                    }
                    $paramsUtm = ['utm_source' => 'demostore', 'utm_medium' => 'referral'];
                    $queryString = http_build_query($paramsUtm);
                    $module['url'] = $module['url'] . '?' . $queryString;
                }
            }
        }
        return $dataPromotions;
    }
}
