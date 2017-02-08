<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

use LogicException;
use SocialConnect\Provider\Consumer;

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
        'facebook' => \SocialConnect\Auth\Provider\Facebook::class,
        'github' => \SocialConnect\Auth\Provider\GitHub::class,
        'instagram' => \SocialConnect\Auth\Provider\Instagram::class,
        'twitter' => \SocialConnect\Auth\Provider\Twitter::class,
        'google' => \SocialConnect\Auth\Provider\Google::class,
        'vk' => \SocialConnect\Auth\Provider\Vk::class,
        'slack' => \SocialConnect\Auth\Provider\Slack::class,
        'twitch' => \SocialConnect\Auth\Provider\Twitch::class,
        'px500' => \SocialConnect\Auth\Provider\Px500::class,
        'bitbucket' => \SocialConnect\Auth\Provider\Bitbucket::class,
        'amazon' => \SocialConnect\Auth\Provider\Amazon::class,
        'gitlab' => \SocialConnect\Auth\Provider\GitLab::class,
        'vimeo' => \SocialConnect\Auth\Provider\Vimeo::class,
        'digital-ocean' => \SocialConnect\Auth\Provider\DigitalOcean::class,
        'yandex' => \SocialConnect\Auth\Provider\Yandex::class,
        'mail-ru' => \SocialConnect\Auth\Provider\MailRu::class,
        'odnoklassniki' => \SocialConnect\Auth\Provider\Odnoklassniki::class,
        'steam' => \SocialConnect\Auth\Provider\Steam::class,
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
     * @return \SocialConnect\Provider\AbstractBaseProvider
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
         * @var $provider \SocialConnect\Provider\AbstractBaseProvider
         */
        $provider = new $providerClassName(
            $service->getHttpClient(),
            $consumer,
            array_merge(
                $parameters,
                $service->getConfig()
            )
        );

        return $provider;
    }
}
