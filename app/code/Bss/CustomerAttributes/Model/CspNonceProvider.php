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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 224-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Model;

class CspNonceProvider
{
    const CSP_HELPER_CLASS = "Magento\Csp\Helper\CspNonceProvider";

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Check isset class \Magento\Csp\Helper\CspNonceProvider
     *
     * @return bool
     */
    public function issetCspNonceProvider()
    {
        if (class_exists(\Magento\Csp\Helper\CspNonceProvider::class)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get class or null
     *
     * @param $objectName
     * @param $data
     * @return mixed|null
     */
    public function getObjectInstance($objectName, $data = [])
    {
        if ($this->issetCspNonceProvider()) {
            return $this->objectManager->create(
                $objectName,
                $data
            );
        }
        return null;
    }

    /**
     * Check and set Nonce if magento version > 247
     *
     * @param $attributes
     * @param $version
     * @param $data
     * @return void
     */
    public function checkAndSetNonce(&$attributes, $version, $data = [])
    {
        if (version_compare($version, '2.4.7', '>=')) {
            $cspClass = $this->getObjectInstance(
                self::CSP_HELPER_CLASS,
                $data
            );
            if ($cspClass) {
                $nonce = $cspClass->generateNonce();
                $attributes['nonce'] = $nonce;
            }
        }
    }
}
