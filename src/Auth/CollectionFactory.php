<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Auth;

use LogicException;
use SocialConnect\OAuth1;
use SocialConnect\OAuth2;
use SocialConnect\OpenID;
use SocialConnect\OpenIDConnect;

class CollectionFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $providers = [
        // OAuth1
        OAuth1\Provider\Twitter::NAME       => OAuth1\Provider\Twitter::class,
        OAuth1\Provider\Px500::NAME         => OAuth1\Provider\Px500::class,
        OAuth1\Provider\Tumblr::NAME        => OAuth1\Provider\Tumblr::class,
        OAuth1\Provider\Atlassian::NAME     => OAuth1\Provider\Atlassian::class,
        OAuth1\Provider\Trello::NAME        => OAuth1\Provider\Trello::class,
        // OAuth2
        OAuth2\Provider\Facebook::NAME      => OAuth2\Provider\Facebook::class,
        //OAuth2\Provider\Google::NAME        => OAuth2\Provider\Google::class,
        OAuth2\Provider\GitHub::NAME        => OAuth2\Provider\GitHub::class,
        OAuth2\Provider\Instagram::NAME     => OAuth2\Provider\Instagram::class,
        OAuth2\Provider\Vk::NAME            => OAuth2\Provider\Vk::class,
        OAuth2\Provider\SmashCast::NAME     => OAuth2\Provider\SmashCast::class,
        OAuth2\Provider\Slack::NAME         => OAuth2\Provider\Slack::class,
        OAuth2\Provider\Steein::NAME        => OAuth2\Provider\Steein::class,
        OAuth2\Provider\Twitch::NAME        => OAuth2\Provider\Twitch::class,
        OAuth2\Provider\Bitbucket::NAME     => OAuth2\Provider\Bitbucket::class,
        OAuth2\Provider\Amazon::NAME        => OAuth2\Provider\Amazon::class,
        OAuth2\Provider\GitLab::NAME        => OAuth2\Provider\GitLab::class,
        OAuth2\Provider\Vimeo::NAME         => OAuth2\Provider\Vimeo::class,
        OAuth2\Provider\DigitalOcean::NAME  => OAuth2\Provider\DigitalOcean::class,
        OAuth2\Provider\Yandex::NAME        => OAuth2\Provider\Yandex::class,
        OAuth2\Provider\MailRu::NAME        => OAuth2\Provider\MailRu::class,
        OAuth2\Provider\Microsoft::NAME     => OAuth2\Provider\Microsoft::class,
        OAuth2\Provider\Odnoklassniki::NAME => OAuth2\Provider\Odnoklassniki::class,
        OAuth2\Provider\Discord::NAME       => OAuth2\Provider\Discord::class,
        OAuth2\Provider\Reddit::NAME        => OAuth2\Provider\Reddit::class,
        OAuth2\Provider\LinkedIn::NAME      => OAuth2\Provider\LinkedIn::class,
        OAuth2\Provider\Yahoo::NAME         => OAuth2\Provider\Yahoo::class,
        OAuth2\Provider\WordPress::NAME     => OAuth2\Provider\WordPress::class,
        OAuth2\Provider\Meetup::NAME        => OAuth2\Provider\Meetup::class,
        // OpenID
        OpenID\Provider\Steam::NAME         => OpenID\Provider\Steam::class,
        // OpenIDConnect
        OpenIDConnect\Provider\Apple::NAME        => OpenIDConnect\Provider\Apple::class,
        OpenIDConnect\Provider\Google::NAME       => OpenIDConnect\Provider\Google::class,
        OpenIDConnect\Provider\Keycloak::NAME     => OpenIDConnect\Provider\Keycloak::class,
        OpenIDConnect\Provider\PixelPin::NAME     => OpenIDConnect\Provider\PixelPin::class,
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
     * @param string $id
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
        $id = strtolower($id);

        if (!isset($this->providers[$id])) {
            throw new LogicException('Provider with $id = ' . $id . ' doest not exist');
        }

        /** @var string $providerClassName */
        $providerClassName = $this->providers[$id];

        /** @var \SocialConnect\Provider\AbstractBaseProvider $provider */
        $provider = new $providerClassName(
            $service->getHttpStack(),
            $service->getSession(),
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
     * @param string $providerName
     * @param string $providerClass
     */
    public function register(string $providerName, string $providerClass)
    {
        $this->providers[$providerName] = $providerClass;
    }
}
