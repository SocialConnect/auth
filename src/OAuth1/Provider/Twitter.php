<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth1\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;

class Twitter extends \SocialConnect\OAuth1\AbstractProvider
{
    const NAME = 'twitter';

    public function getBaseUri()
    {
        return 'https://api.twitter.com/1.1/';
    }

    public function getAuthorizeUri()
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    public function getRequestTokenUri()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function getRequestTokenAccessUri()
    {
        return 'https://api.twitter.com/oauth/access_token';
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $result = $this->request(
            'GET',
            'account/verify_credentials.json',
            [
                // String is expected because Twitter is awful
                'include_email' => 'true'
            ],
            $accessToken
        );

        $hydrator = new ArrayHydrator([
            'id' => 'id',
            'name' => 'fullname',
            'email' => 'email',
            'screen_name' => 'username',
            'profile_image_url_https' => 'pictureURL'
        ]);

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result);

        // When set to true email will be returned in the user objects as a string.
        // If the user does not have an email address on their account,
        // or if the email address is not verified, null will be returned.
        $user->emailVerified = true;

        return $user;
    }
}
