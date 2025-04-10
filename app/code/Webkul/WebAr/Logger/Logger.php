<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_WebAr
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\WebAr\Logger;

class Logger extends \Monolog\Logger
{
    /**
     * Intialize dependencies
     *
     * @param string             $name
     * @param HandlerInterface[] $handlers
     * @param callable[]         $processors
     * @return void
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(
        $name = 'webkulWebArLogHandler',
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct(
            $name,
            $handlers,
            $processors
        );
    }
}
