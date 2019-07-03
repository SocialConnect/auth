<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

class LinkedIn extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'linkedin';

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.linkedin.com/v1/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://www.linkedin.com/oauth/v2/authorization';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRequest(array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $query['format'] = 'json';

        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->request(
            'people/~:(id,first-name,last-name,email-address,picture-url,location:(name))',
            [],
            $accessToken
        );

        $hydrator = new ObjectMap(
            [
                'id'           => 'id',
                'emailAddress' => 'email',
                'firstName'    => 'firstname',
                'lastName'     => 'lastname',
                'pictureUrl'   => 'pictureURL',
            ]
        );

        return $hydrator->hydrate(new User(), $response);
    }
}
