<?php

namespace Test\OpenIDConnect\Provider;

use Psr\Http\Message\ResponseInterface;

class KeycloakTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OpenIDConnect\Provider\Keycloak::class;
    }

    public function getProviderConfiguration(): array
    {
        return parent::getProviderConfiguration() + [
            'baseUrl' => 'https://keycloak_server/auth',
            'realm' => 'your_master',
        ];
    }

    protected function getTestResponseForGetIdentity(): ResponseInterface
    {
        return $this->createResponse(
            json_encode([
                'sub' => 'uuid4',
            ])
        );
    }
}
