<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model\Di;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerMetaProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ViewModelWrapper implements ArgumentInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManagerInterface;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $getShared;

    /**
     * @var bool
     */
    private $isProxy;

    /**
     * @var object
     */
    private $subject;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ObjectManagerMetaProvider
     */
    private $diMetaProvider;

    public function __construct(
        ObjectManagerInterface $objectManagerInterface,
        Manager $moduleManager,
        ObjectManagerMetaProvider $diMetaProvider,
        string $name = '',
        bool $getShared = false,
        bool $isProxy = false
    ) {
        $this->objectManagerInterface = $objectManagerInterface;
        $this->moduleManager = $moduleManager;
        $this->diMetaProvider = $diMetaProvider;
        $this->name = $name;
        $this->getShared = $getShared;
        $this->isProxy = $isProxy;
    }

    /**
     * @return bool|mixed
     */
    public function __call(string $name, array $arguments)
    {
        $result = false;

        if ($this->canCreateObject()) {
            $object = $this->getSubject();
            //phpcs:ignore
            $result = call_user_func_array([$object, $name], $arguments);
        }

        return $result;
    }

    public function getSubject(): object
    {
        if ($this->isProxy && $this->subject) {
            return $this->subject;
        }

        if ($this->getShared) {
            $object = $this->objectManagerInterface->get($this->name);
        } else {
            $object = $this->objectManagerInterface->create($this->name);
        }

        if ($this->isProxy) {
            $this->subject = $object;
        }

        return $object;
    }

    private function isVirtualType(string $class): bool
    {
        $type = $this->diMetaProvider->getInstanceType($class);

        return $type !== $class;
    }

    private function canCreateObject(): bool
    {
        $canAutoload = (class_exists($this->name) || interface_exists($this->name))
            && $this->moduleManager->isEnabled($this->getModuleName($this->name));
        $canGetObjectByDI = $this->isVirtualType($this->name);

        return $this->name && ($canAutoload || $canGetObjectByDI);
    }

    private function getModuleName(string $class): string
    {
        $class = ltrim($class, '\\');
        $parts = preg_split('@[\\\_]@', $class);
        $parts = array_filter($parts);

        if (count($parts) < 2) {
            throw new \InvalidArgumentException(
                (string)__('Provided argument is not in PSR-0 or underscore notation.')
            );
        }

        return sprintf(
            '%1s_%2s',
            ucfirst($parts[0]),
            ucfirst($parts[1])
        );
    }
}
