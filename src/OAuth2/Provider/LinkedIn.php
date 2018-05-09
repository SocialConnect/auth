<?php
/**
 * SocialConnect project
 *
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\Http\Client\Client;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidAccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;
use SocialConnect\OAuth2\AccessToken;

class LinkedIn extends \SocialConnect\OAuth2\AbstractProvider
{
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
        return 'linkedin';
    }

    /**
     * {@inheritdoc}
     */
    public function parseToken($body)
    {
        $result = json_decode($body, true);
        if ($result) {
            return new AccessToken($result);
        }

        throw new InvalidAccessToken('AccessToken is not a valid JSON');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'people/~:(id,first-name,last-name,email-address,picture-url,location:(name))',
            [
                'format' => 'json'
            ],
            Client::GET,
            [
                'Authorization' => 'Bearer ' . $accessToken->getToken(),
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $result = $response->json();
        if (!$result) {
            throw new InvalidResponse(
                'API response is not a valid JSON object',
                $response
            );
        }

        $hydrator = new ObjectMap(
            [
                'id'           => 'id',
                'emailAddress' => 'email',
                'firstName'    => 'firstname',
                'lastName'     => 'lastname',
                'pictureUrl'   => 'pictureURL',
            ]
        );

        /** @var User $user */
        $user = $hydrator->hydrate(new User(), $result);

        // @todo Remove in 2.0, didnt remove because it's was a bug and I am not interested to break semver!
        $user->picture = $user->pictureURL;

        return $user;
    }
}
