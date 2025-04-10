<?php
namespace Mageplaza\Shopbybrand\Plugin;

use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Mageplaza\Shopbybrand\Helper\Data;
use Magento\Framework\App\RequestInterface;

/**
 * Check and return IsMemorizingAllowed
 */
class ToolbarMemorizerPlugin
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param Data $helperData
     * @param RequestInterface $request
     */
    public function __construct(Data $helperData, RequestInterface $request)
    {
        $this->request = $request;
        $this->helperData = $helperData;
    }

    /**
     * After plugin to override the isMemorizingAllowed method
     *
     * @param ToolbarMemorizer $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsMemorizingAllowed(ToolbarMemorizer $subject, $result)
    {
        if (array_key_exists('brand_key', $this->request->getParams()))
        {
            return false;
        }
        return $result;
    }
}
