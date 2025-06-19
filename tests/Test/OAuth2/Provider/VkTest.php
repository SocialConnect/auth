<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Message\ResponseInterface;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\Provider\Session\SessionInterface;

class VkTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\Vk::class;
    }

    public function testGetIdentitySuccess()
    {
        $mockedHttpClient = $this->mockClientResponse(
            json_encode(
                [
                    'user' => [
                        'user_id' => $expectedId = 12321312312312,
                        'first_name' => $expectedFirstname = 'Dmitry',
                        'last_name' => $expectedLastname = 'Patsura',
                        'sex' => 1,
                        'birthday' => $birthday = '01.03.1993',
                    ]
                ]
            )
        );

        $result = $this->getProvider($mockedHttpClient)->getIdentity(
            new AccessToken(
                [
                    'access_token' => '123456789'
                ]
            )
        );

        parent::assertInstanceOf(\SocialConnect\Common\Entity\User::class, $result);
        parent::assertSame($expectedId, $result->id);
        parent::assertSame($expectedFirstname, $result->firstname);
        parent::assertSame($expectedLastname, $result->lastname);
        parent::assertSame('female', $result->getSex());
        parent::assertSame($birthday, $result->getBirthday()->format('d.m.Y'));
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestResponseForGetIdentity(): ResponseInterface
    {
        return $this->createResponse(
            json_encode([
                'id' => 12345,
            ])
        );
    }

    public function testGetAccessTokenResponseInternalServerErrorFail()
    {
        $this->expectException(InvalidResponse::class);
        $this->expectExceptionMessage('API response with error code');

        $client = $this->mockClientResponse(
            null,
            500
        );

        $session = $this->mockSession(['abc', 'device_id']);

        $this->getProvider($client, $session)->getAccessToken('XXXXXXXXXXXX');
    }
}
