<?php
namespace Chill\Vortex\Block;

use Magento\Framework\View\Element\Html\Link;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\View\Element\Template;

class AdminLink extends Link
{
    protected $backendHelper;

    public function __construct(
        Template\Context $context,
        BackendHelper $backendHelper,
        array $data = []
    ) {
        $this->backendHelper = $backendHelper;
        parent::__construct($context, $data);
    }
    public function getHref()
    {
        return $this->backendHelper->getHomePageUrl();
    }

    public function getLabel()
    {
        return __('Admin');
    }
}
