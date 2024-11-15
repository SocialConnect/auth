<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace Test\Provider;

use SocialConnect\Common\Entity\User;
use SocialConnect\Provider\AbstractBaseProvider;
use SocialConnect\Provider\AccessTokenInterface;

class ProviderMock extends AbstractBaseProvider
{
    /**
     * @return string
     */
    public function getBaseUri()
    {
        // TODO: Implement getBaseUri() method.
        return '';
    }

    /**
     * Return Provider's name
     *
     * @return string
     */
    public function getName()
    {
        return 'fake';
    }

    /**
     * @param array $requestParameters
     * @return \SocialConnect\Provider\AccessTokenInterface
     */
    public function getAccessTokenByRequestParameters(array $requestParameters)
    {
        // TODO: Implement getAccessTokenByRequestParameters() method.
    }

    /**
     * @return string
     */
    public function makeAuthUrl(): string
    {
        // TODO: Implement makeAuthUrl() method.
        return '';
    }

    /**
     * Get current user identity from social network by $accessToken
     *
     * @param AccessTokenInterface $accessToken
     * @return \SocialConnect\Common\Entity\User
     *
     * @throws \SocialConnect\Provider\Exception\InvalidResponse
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        // TODO: Implement getIdentity() method.
        return new User();
    }

    /**
     * @inheritDoc
     */
    public function createAccessToken(array $information)
    {
        // TODO: Implement createAccessToken() method.
    }
}
