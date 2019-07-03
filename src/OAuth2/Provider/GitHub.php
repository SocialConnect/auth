<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class GitHub extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'github';

    /**
     * @var array
     */
    protected $options = [
        /**
         * GitHub store only unverified and public email inside User
         * It's not possible to fetch user with email in GraphQL (new api)
         * For now, there is only one way, additional request for it by user/email API entrypoint
         *
         * It's disabled by default in SocialConnect 1.x, but you can enable it from configuration :)
         */
        'fetch_emails' => false
    ];

    public function getBaseUri()
    {
        return 'https://api.github.com/';
    }

    public function getAuthorizeUri()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    public function getRequestTokenUri()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken(string $body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        parse_str($body, $token);

        if (!is_array($token) || !isset($token['access_token'])) {
            throw new InvalidAccessToken('Provider API returned an unexpected response');
        }

        return new AccessToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('user', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'id' => 'id',
                'login' => 'username',
                'email' => 'email',
                'avatar_url' => 'pictureURL',
                'name' => 'fullname'
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $response);

        if ($this->getBoolOption('fetch_emails', false)) {
            $primaryEmail = $this->getPrimaryEmail($accessToken);
            if ($primaryEmail) {
                $user->email = $primaryEmail->email;
                $user->emailVerified = $primaryEmail->verified;
            }
        }

        return $user;
    }


    /**
     * @param AccessTokenInterface $accessToken
     * @return object
     * @throws InvalidResponse
     */
    public function getPrimaryEmail(AccessTokenInterface $accessToken)
    {
        $emails = $this->getEmails($accessToken);
        if ($emails) {
            foreach ($emails as $email) {
                if ($email->primary) {
                    return $email;
                }
            }
        }

        return null;
    }

    /**
     * @param AccessTokenInterface $accessToken
     * @return array
     * @throws InvalidResponse
     */
    public function getEmails(AccessTokenInterface $accessToken)
    {
        return $this->request('user/emails', $accessToken);
    }
}
