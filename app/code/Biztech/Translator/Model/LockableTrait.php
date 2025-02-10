<?php

namespace Biztech\Translator\Model;

use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * Basic lock feature for commands.
 *
 */
trait LockableTrait
{
    /** @var Lock */
    private $lock;

    /**
     * Locks a command.
     *
     * @return bool
     */
    private function lock($name = null, $blocking = false)
    {
        if (!class_exists(SemaphoreStore::class)) {
            throw new RuntimeException('To enable the locking feature you must install the symfony/lock component.');
        }
        if (null !== $this->lock) {
            throw new LogicException('A lock is already in place.');
        }
        if (SemaphoreStore::isSupported()) {
            $store = new SemaphoreStore();
        } else {
            $store = new FlockStore();
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadataInterface = $objectManager->create('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadataInterface->getVersion();
        if (version_compare($version, '2.3.1', '=')) {
            $this->lock = (new LockFactory($store))->createLock($name ?: $this->getName());
        } elseif (version_compare($version, '2.3.3', '>')) {
            $this->lock = (new LockFactory($store))->createLock($name ?: $this->getName());
        } else {
            $this->lock = (new Factory($store))->createLock($name ?: $this->getName());
        }
        if (!$this->lock->acquire($blocking)) {
            $this->lock = null;

            return false;
        }
        return true;
    }
    /**
     * Releases the command lock if there is one.
     */
    private function release()
    {
        if ($this->lock) {
            $this->lock->release();
            $this->lock = null;
        }
    }
}
