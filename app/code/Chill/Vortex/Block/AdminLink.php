<?php
namespace Chill\Vortex\Block;

use Magento\Framework\View\Element\Html\Link;

class AdminLink extends Link
{
    public function getHref()
    {
        return $this->getUrl('admin'); // URL sẽ dẫn đến trang admin
    }

    public function getLabel()
    {
        return __('Admin');
    }
}
