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
namespace Bss\RewardPoint\Plugin\Element;

use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Bss\RewardPoint\Model\Config\Source\Image as SourceImage;
use Magento\Framework\View\Asset\Repository;

class Image extends \Magento\Config\Block\System\Config\Form\Field\Image
{
    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * Image constructor.
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param Repository $assetRepo
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        Repository $assetRepo,
        UrlInterface $urlBuilder,
        $data = []
    ) {
        $this->assetRepo = $assetRepo;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data);
    }

    /**
     * Get url
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUrl()
    {
        if ($this->getId() == 'bssrewardpoint_frontend_point_icon') {
            $url = $this->getValue();
            if ($url == SourceImage::DEFAULT_ICON_VALUE) {
                $config = $this->getFieldConfig();
                /* @var $config array */
                if (isset($config['base_url'])) {
                    $url = $this->assetRepo->getUrl('Bss_RewardPoint::' . SourceImage::DEFAULT_ICON_VALUE);
                }
                return $url;
            }
        }

        return parent::_getUrl();
    }
}
