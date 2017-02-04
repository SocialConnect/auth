<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth\Provider;

use LogicException;
use SocialConnect\Auth\Provider\OAuth1\AbstractProvider as OAuth1AbstractProvider;
use SocialConnect\Auth\Provider\OAuth2\AbstractProvider as OAuth2AbstractProvider;
use SocialConnect\Auth\Service;

/**
 * Class Factory
 * @package SocialConnect\Auth\Provider
 */
class CollectionFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $providers = [
        'facebook' => '\SocialConnect\Facebook\Provider',
        'github' => '\SocialConnect\Github\Provider',
        'instagram' => '\SocialConnect\Instagram\Provider',
        'twitter' => '\SocialConnect\Twitter\Provider',
        'google' => '\SocialConnect\Google\Provider',
        'vk' => '\SocialConnect\Vk\Provider',
        'slack' => '\SocialConnect\Slack\Provider',
        'twitch' => '\SocialConnect\Twitch\Provider',
        'px500' => '\SocialConnect\Px500\Provider',
        'bitbucket' => '\SocialConnect\Bitbucket\Provider',
        'amazon' => '\SocialConnect\Amazon\Provider',
        'gitlab' => '\SocialConnect\Gitlab\Provider',
        'vimeo' => '\SocialConnect\Vimeo\Provider',
        'digital-ocean' => '\SocialConnect\DigitalOcean\Provider',
        'yandex' => '\SocialConnect\Yandex\Provider',
        'mail-ru' => '\SocialConnect\MailRu\Provider',
        'odnoklassniki' => '\SocialConnect\Odnoklassniki\Provider',
        'steam' => '\SocialConnect\Steam\Provider',
    ];

    /**
     * @param array $providers
     */
    public function __construct(array $providers = null)
    {
        if ($providers) {
            $this->providers = $providers;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->providers[$id]);
    }

    /**
     * @param string $id
     * @param array $parameters
     * @param Service $service
     * @return OAuth1AbstractProvider|OAuth2AbstractProvider
     */
    public function factory($id, array $parameters, Service $service)
    {
        $consumer = new Consumer($parameters['applicationId'], $parameters['applicationSecret']);

        if (isset($parameters['applicationPublic'])) {
            $consumer->setPublic($parameters['applicationPublic']);
        }

        $id = strtolower($id);

        if (!isset($this->providers[$id])) {
            throw new LogicException('Provider with $id = ' . $id . ' doest not exist');
        }

        $providerClassName = $this->providers[$id];

        /**
         * @var $provider AbstractBaseProvider
         */
        $provider = new $providerClassName($service, $consumer);

        if (isset($parameters['scope'])) {
            $provider->setScope($parameters['scope']);
        }

        if (isset($parameters['fields'])) {
            $provider->setFields($parameters['fields']);
        }

        return $provider;
    }
}
