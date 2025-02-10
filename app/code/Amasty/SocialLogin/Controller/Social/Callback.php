<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Controller\Social;

use Amasty\SocialLogin\Model\Login;
use Amasty\SocialLogin\Model\SocialData;
use Amasty\SocialLogin\Model\SocialList;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Storage\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;

class Callback implements ActionInterface
{
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        RedirectInterface $redirect,
        RequestInterface $request,
        ResponseInterface $response,
        UrlInterface $url
    ) {
        $this->redirect = $redirect;
        $this->request = $request;
        $this->response = $response;
        $this->url = $url;
    }

    /**
     * @return ResponseInterface
     */
    public function execute()
    {
        if ($this->isRedirectToAccount()) {
            $this->redirect->redirect($this->response, 'customer/account');
            
            return $this->response;
        }

        try {
            $storage = new Session();
            $requestParams = $this->request->getParams() ?: [];
            $socialParams = $storage->get(Login::AMSOCIAL_LOGIN_PARAMS) ?: [];
            $params = array_merge($requestParams, $socialParams);
            $socialPath = $this->url->getUrl(
                SocialData::AMSOCIALLOGIN_SOCIAL_LOGIN_PATH,
                ['_query' => $params]
            );
            $this->redirect->redirect($this->response, $socialPath);
        } catch (InvalidArgumentException $e) {
            $this->redirect->redirect($this->response, $this->redirect->getRefererUrl());
        }

        return $this->response;
    }

    /**
     * @param string $key
     * @param string|bool $value
     * @return bool
     */
    public function checkRequest(string $key, $value = null): bool
    {
        $param = $this->request->getParam($key, false);
        if ($value !== null) {
            return $param == $value;
        }

        return (bool) $param;
    }

    private function isRedirectToAccount(): bool
    {
        return $this->checkRequest('hauth_start', false)
            && (($this->checkRequest('error_reason', 'user_denied')
                    && $this->checkRequest('error', 'access_denied')
                    && $this->checkRequest('error_code', '200')
                    && $this->checkRequest('hauth_done', SocialList::NAME_FACEBOOK))
                || ($this->checkRequest('hauth_done', SocialList::NAME_TWITTER) && $this->checkRequest('denied'))
            );
    }
}
