<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Model;

use Hybridauth\Hybridauth;
use Hybridauth\User\Profile;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url;

class SocialData
{
    /**
     * @deprecared moved to SocialList::TYPE_GENERAL
     */
    public const GENERAL = SocialList::TYPE_GENERAL;

    /**
     * @deprecared moved to SocialList::TYPE_APPLE
     */
    public const APPLE = SocialList::TYPE_APPLE;

    public const AMSOCIALLOGIN_SOCIAL_LOGIN_PATH = 'amsociallogin/social/login';

    /**
     * @var array
     */
    private $socialDataRegistry;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @var SocialList
     */
    private $socialList;

    public function __construct(
        Url $urlBuilder,
        ConfigData $configData,
        StoreResolver $storeResolver,
        SocialList $socialList
    ) {
        $this->configData = $configData;
        $this->urlBuilder = $urlBuilder;
        $this->storeResolver = $storeResolver;
        $this->socialList = $socialList;
    }

    /**
     * @return array array(['type' => '', 'label' => '', 'url' => ''])
     */
    public function getEnabledSocials(): array
    {
        if ($this->socialDataRegistry === null) {
            $this->socialDataRegistry = [];
            foreach ($this->socialList->getList() as $type => $label) {
                if (!$this->isSocialEnabled($type)) {
                    continue;
                }

                $sortOrder = (int) $this->configData->getConfigValue($type . '/sort_order');
                /* when two socials have one sort order*/
                while (true) {
                    if (array_key_exists($sortOrder, $this->socialDataRegistry)) {
                        $sortOrder++;
                    } else {
                        break;
                    }
                }

                $this->socialDataRegistry[$sortOrder] = [
                    'type' => $type,
                    'label' => $label,
                    'url' => $this->urlBuilder->getUrl(self::AMSOCIALLOGIN_SOCIAL_LOGIN_PATH, [
                        '_query' => ['type' => $type],
                        '_secure' => true
                    ])
                ];
            }
            ksort($this->socialDataRegistry);
        }

        return $this->socialDataRegistry;
    }

    /**
     * Is Social allowed to be visible.
     */
    public function isSocialEnabled(string $type): bool
    {
        return $this->configData->getConfigValue($type . '/enabled')
            && ($type === SocialList::TYPE_APPLE || $this->isSocialConfigured($type));
    }

    /**
     * @return Profile
     * @throws LocalizedException
     */
    public function getUserProfile(string $type): Profile
    {
        if (!class_exists(Hybridauth::class)) {
            throw new LocalizedException(
                __('Additional Social Login package is not installed or need to be updated. '
                    . 'Please, run the following command in the SSH: composer require hybridauth/hybridauth:^3.8.0')
            );
        }

        $socialName = $this->socialList->getNameByType($type);
        $redirectUrlType = $this->configData->getConfigValue('general/use_new_url') ? SocialList::TYPE_GENERAL : $type;
        $config = [
            'callback' => $this->getRedirectUrl($redirectUrlType),
            'providers' => [$socialName => $this->getProviderData($type)],
            'debug_mode' => false
        ];

        $auth = new Hybridauth($config);
        if ($type === \Amasty\SocialLogin\Model\SocialList::TYPE_APPLE) {
            $adapter = $auth->getAdapter($socialName);
            $user = $adapter->getUserProfile();
            if (!$user->identifier) {
                $adapter->authenticate();
                $user = $adapter->getUserProfile();
            }
        } else {
            $adapter = $auth->authenticate($socialName);
            $user = $adapter->getUserProfile();
        }

        $adapter->disconnect();

        return $user;
    }

    public function getProviderData(string $type): array
    {
        $apiKey = $this->configData->getConfigValue($type . '/api_key');
        $apiSecret = $this->configData->getSecretKey($type);
        $config = $this->getSocialConfig($type);
        $config['enabled'] = true;
        $config['keys'] = ['id' => $apiKey, 'key' => $apiKey, 'secret' => $apiSecret];

        return $config;
    }

    /**
     * @return string[]
     * @deprecared moved to separated class SocialList
     * @see SocialList
     */
    public function getAllSocialTypes(): array
    {
        return $this->socialList->getList();
    }

    public function getBaseAuthUrl(): string
    {
        $store = $this->storeResolver->getStore();

        return (string) $this->urlBuilder->getUrl('amsociallogin/social/callback', [
            '_nosid'  => true,
            '_scope'  => $store->getId(),
            '_secure' => $store->isUrlSecure()
        ]);
    }

    public function getNewBaseAuthUrl(): string
    {
        $store = $this->storeResolver->getStore();

        return (string) $this->urlBuilder->getUrl('amsociallogin/social/login', [
            '_nosid'  => true,
            '_scope'  => $store->getId(),
            '_secure' => $store->isUrlSecure()
        ]);
    }

    public function getSocialConfig(string $type): array
    {
        $result = [];
        $apiData = [
            SocialList::TYPE_FACEBOOK => ['trustForwarded' => false, 'scope' => implode(',', [
                'email',
                'public_profile',
                'user_birthday',
                'user_gender'
            ])],
            SocialList::TYPE_TWITTER => ['includeEmail' => true],
            SocialList::TYPE_LINKEDIN => ['fields' => ['id', 'first-name', 'last-name', 'email-address']],
            SocialList::TYPE_GOOGLE => [
                'scope' => 'email profile',
                'authorize_url_parameters' => [
                    'approval_prompt' => 'force',
                ],
            ],
            SocialList::TYPE_PAYPAL => ['scope' => 'openid profile email']
        ];

        if ($type && array_key_exists($type, $apiData)) {
            $result = $apiData[$type];
        }

        return $result;
    }

    /**
     * Get redirect URL by social type.
     */
    public function getRedirectUrl(string $type): string
    {
        $authUrl = $this->getBaseAuthUrl();
        $name = $this->socialList->getNameByType($type);

        switch ($name) {
            case SocialList::TYPE_GENERAL:
            case SocialList::NAME_PINTEREST:
                $newUrl = $this->getNewBaseAuthUrl();
                break;
            case SocialList::NAME_FACEBOOK:
                $param = 'hauth_done=' . $name;
                break;
            case SocialList::NAME_TWITCH:
                $param = 'hauth_done=Twitch';
                break;
            case SocialList::NAME_TWITTER:
            case SocialList::NAME_INSTAGRAM:
            case SocialList::NAME_MICROSOFT:
                $param = '';
                break;
            default:
                $param = 'hauth.done=' . $name;
        }

        return $newUrl ?? $authUrl . ($param ? (strpos($authUrl, '?') ? '&' : '?') . $param : '');
    }

    /**
     * @param $userProfile
     * @param string $type
     *
     * @return array
     */
    public function createUserData($userProfile, string $type): array
    {
        $user = get_object_vars($userProfile);
        $user['displayName'] = $user['displayName'] ?: __('New User');
        $name = explode(' ', $user['displayName']);
        $user['firstname'] = $user['firstName'] ?: array_shift($name);
        $user['lastname'] = $user['lastName'] ?: array_shift($name);
        $user['type'] = $type;

        return $user;
    }

    private function isSocialConfigured(string $type): bool
    {
        return $this->configData->getConfigValue($type . '/api_key')
            && $this->configData->getSecretKey($type);
    }
}
