<?php
namespace Bss\CustomLayout\Block;

use Magento\Framework\View\Element\Html\Links;

class DropdownLinks extends Links
{
    public function getAccountLinks()
    {
        $parent = $this->getLayout()->getBlock('header.links');
        if ($parent) {
            return $parent->getLinks();
        }
        return [];
    }
}
