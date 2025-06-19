<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;

class Vk extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'vk';

    /**
     * {@inheritdoc}
     */
    protected $requestHttpMethod = 'POST';

    /**
     * {@inheritdoc}
     */
    protected bool $pkce = true;

    public function getBaseUri()
    {
        return 'https://id.vk.com/';
    }

    public function getAuthorizeUri()
    {
        return 'https://id.vk.com/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://id.vk.com/oauth2/auth';
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, ?AccessTokenInterface $accessToken = null): void
    {
        if ($accessToken) {
            $query['access_token'] = $accessToken->getToken();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [
            'client_id' => $this->consumer->getKey(),
        ];

        $response = $this->request('POST', 'oauth2/user_info', $query, null, [
            'access_token' => $accessToken->getToken(),
        ]);

        $hydrator = new ArrayHydrator([
            'user_id' => 'id',
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'birthday' => static function ($value, User $user) {
                list($day, $month, $year) = array_map(
                    fn (string $value) => (int) $value,
                    explode('.', $value),
                );
                $user->setBirthday(
                    (new \DateTime())->setDate($year, $month, $day)->setTime(12, 0)
                );
            },
            'sex' => static function ($value, User $user) {
                $user->setSex($value === 1 ? User::SEX_FEMALE : User::SEX_MALE);
            },
            'email' => 'email',
            'screen_name' => 'username',
            'avatar' => 'pictureURL',
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $response['user']);

        return $user;
    }
}
