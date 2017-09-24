<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Auth;

use LogicException;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\Consumer;
use SocialConnect\OAuth1;
use SocialConnect\OAuth2;
use SocialConnect\OpenID;
use SocialConnect\OpenIDConnect;

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
        // OAuth1
        'twitter'       => OAuth1\Provider\Twitter::class,
        'px500'         => OAuth1\Provider\Px500::class,
        'tumblr'        => OAuth1\Provider\Tumblr::class,
        'atlassian'     => OAuth1\Provider\Atlassian::class,
        // OAuth2
        'facebook'      => OAuth2\Provider\Facebook::class,
        'google'        => OAuth2\Provider\Google::class,
        'github'        => OAuth2\Provider\GitHub::class,
        'instagram'     => OAuth2\Provider\Instagram::class,
        'vk'            => OAuth2\Provider\Vk::class,
        'smashcast'     => OAuth2\Provider\SmashCast::class,
        'slack'         => OAuth2\Provider\Slack::class,
        'steein'        => OAuth2\Provider\Steein::class,
        'twitch'        => OAuth2\Provider\Twitch::class,
        'bitbucket'     => OAuth2\Provider\Bitbucket::class,
        'amazon'        => OAuth2\Provider\Amazon::class,
        'gitlab'        => OAuth2\Provider\GitLab::class,
        'vimeo'         => OAuth2\Provider\Vimeo::class,
        'digital-ocean' => OAuth2\Provider\DigitalOcean::class,
        'yandex'        => OAuth2\Provider\Yandex::class,
        'mail-ru'       => OAuth2\Provider\MailRu::class,
        'microsoft'     => OAuth2\Provider\Microsoft::class,
        'odnoklassniki' => OAuth2\Provider\Odnoklassniki::class,
        'discord'       => OAuth2\Provider\Discord::class,
        'reddit'        => OAuth2\Provider\Reddit::class,
        // OpenID
        'steam'         => OpenID\Provider\Steam::class,
        // OpenIDConnect - currently disabled before 1.1 release
        //'google'        => OpenIDConnect\Provider\Google::class,
        'pixelpin'      => OpenIDConnect\Provider\PixelPin::class,
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
            $service->getSession(),
            $consumer,
            array_merge(
                $parameters,
                $service->getConfig()
            )
        );

        return $provider;
    }

    /**
     * Register new provider to Provider's collection
     *
     * @param AbstractBaseProvider $provider
     */
    public function register(AbstractBaseProvider $provider)
    {
        $this->providers[$provider->getName()] = get_class($provider);
    }
}
