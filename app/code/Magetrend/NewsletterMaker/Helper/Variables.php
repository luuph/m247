<?php
/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

namespace Magetrend\NewsletterMaker\Helper;

class Variables
{
    const REGISTRY_NEWSLETTER_ID = 'mt_newslettermaker_newsletter_id';

    const REGISTRY_QUEUE_ID = 'mt_newslettermaker_queue_id';

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    public $frontendUrlBuilder;

    /**
     * Variables constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $frontendUrlBuilder
    ) {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->registry = $registry;
    }

    /**
     * Retunrs link to online version
     * @param $subscriber
     * @return string
     */
    public function getOnlineLink($subscriber)
    {
        if (!$subscriber || !$subscriber->getId()) {
            return '#';
        }

        if ($this->registry->registry(self::REGISTRY_NEWSLETTER_ID)) {
            $key = $this->registry->registry(self::REGISTRY_NEWSLETTER_ID).'_'.$subscriber->getCode();
            return $this->frontendUrlBuilder->setScope($subscriber->getStoreId())
                ->getDirectUrl('newsletters/online/view/nid/'.$key, ['_nosid' => true]);
        }

        if ($this->registry->registry(self::REGISTRY_QUEUE_ID)) {
            $key = $this->registry->registry(self::REGISTRY_QUEUE_ID).'_'.$subscriber->getCode();
            return $this->frontendUrlBuilder->setScope($subscriber->getStoreId())
                ->getDirectUrl('newsletters/online/view/id/'.$key, ['_nosid' => true]);
        }

        return '#';
    }
}
