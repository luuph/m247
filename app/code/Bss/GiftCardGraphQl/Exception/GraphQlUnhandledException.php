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
namespace Bss\GiftCardGraphQl\Exception;

use Magento\Framework\Exception\AggregateExceptionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use GraphQL\Error\ClientAware;

/**
 * Exception for GraphQL to be thrown when catch a unknown exception
 */
class GraphQlUnhandledException extends LocalizedException implements AggregateExceptionInterface, ClientAware
{
    const EXCEPTION_CATEGORY = 'graphql-unhandled';

    /**
     * @var boolean
     */
    private $isSafe;

    /**
     * The array of errors that have been added via the addError() method
     *
     * @var \Magento\Framework\Exception\LocalizedException[]
     */
    private $errors = [];

    /**
     * Initialize object
     *
     * @param Phrase $phrase
     * @param \Exception|null $cause
     * @param int $code
     * @param boolean $isSafe
     */
    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0, $isSafe = true)
    {
        $this->isSafe = $isSafe;
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @inheritdoc
     */
    public function isClientSafe() : bool
    {
        return $this->isSafe;
    }

    /**
     * @inheritdoc
     */
    public function getCategory() : string
    {
        return self::EXCEPTION_CATEGORY;
    }

    /**
     * Add child error if used as aggregate exception
     *
     * @param LocalizedException $exception
     * @return $this
     */
    public function addError(LocalizedException $exception): self
    {
        $this->errors[] = $exception;
        return $this;
    }

    /**
     * Get child errors if used as aggregate exception
     *
     * @return LocalizedException[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
