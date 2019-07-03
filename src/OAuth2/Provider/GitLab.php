<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class GitLab extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'gitlab';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://gitlab.com/api/v3/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://gitlab.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://gitlab.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getScopeInline()
    {
        return implode('+', $this->scope);
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        if (empty($body)) {
            throw new InvalidAccessToken('Provider response with empty body');
        }

        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
        }

        throw new InvalidAccessToken('Provider response with not valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request('user', [], $accessToken);

        $hydrator = new ObjectMap(
            [
                'user_id' => 'id',
                'name' => 'fullname',
                'avatar_url' => 'pictureURL'
            ]
        );

        return $hydrator->hydrate(new User(), $response);
    }
}
