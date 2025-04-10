<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_GiftCardGraphQl
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCardGraphQl\Model\Resolver;

use Bss\GiftCardGraphQl\Exception\GraphQlUnhandledException;
use Magento\Config\Model\Config\Source\Locale\Timezone;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GetTimezonesResolver
 */
class GetTimezonesResolver implements ResolverInterface
{
    /**
     * @var Timezone
     */
    protected $timeZone;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * GetTimezonesResolver constructor.
     *
     * @param Timezone $timeZone
     * @param LoggerInterface $logger
     */
    public function __construct(Timezone $timeZone, LoggerInterface $logger)
    {

        $this->timeZone = $timeZone;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            return $this->timeZone->toOptionArray();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new GraphQlUnhandledException(__("Something went wrong. Please check the log."));
        }
    }
}
