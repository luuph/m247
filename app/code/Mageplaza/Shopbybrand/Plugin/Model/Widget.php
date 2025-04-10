<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Widget\Model\Widget\Instance;

/**
 * Class Widget
 * @package Mageplaza\Shopbybrand\Plugin\Model
 */
class Widget
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Json
     */
    protected $json;

    /**
     * Widget constructor.
     *
     * @param RequestInterface $request
     * @param Json $json
     */
    public function __construct(
        RequestInterface $request,
        Json $json
    ) {
        $this->request = $request;
        $this->json    = $json;
    }

    /**
     * @param Instance $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterBeforeSave(Instance $subject, $result)
    {
        if ($this->request->getParam('code') == 'mpbrand_advanced_widget') {
            $parameters = $result->getData('widget_parameters');
            $parameters = $this->getParameterData($this->json->unserialize($parameters));

            $result->setData('widget_parameters', $this->json->serialize($parameters));
        }

        return $result;
    }

    /**
     * @param $parameters
     *
     * @return mixed
     */
    protected function getParameterData($parameters)
    {
        $parameters['display_style']    = $this->request->getParam('display_style');
        $parameters['slider_width']     = $this->request->getParam('slider_width');
        $parameters['slider_height']    = $this->request->getParam('slider_height');
        $parameters['next_prev_button'] = $this->request->getParam('next_prev_button');
        $parameters['show_dots_nav']    = $this->request->getParam('show_dots_nav');
        $parameters['auto_play']        = $this->request->getParam('auto_play');
        $parameters['auto_timeout']     = $this->request->getParam('auto_timeout');
        $parameters['page_column']      = $this->request->getParam('page_column');
        $parameters['limit_brands']     = $this->request->getParam('limit_brands');

        return $parameters;
    }
}