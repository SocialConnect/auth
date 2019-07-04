<?php
/**
 * SocialConnect project
 * @author: Andreas Heigl https://github.com/heiglandreas <andreas@heigl.org>
 */

namespace Test\OAuth2\Provider;

use Psr\Http\Client\ClientInterface;
use SocialConnect\Common\Http\Response;
use SocialConnect\OAuth2\AccessToken;
use SocialConnect\OAuth2\Provider\Meetup;
use SocialConnect\Provider\Consumer;
use SocialConnect\Provider\Session\SessionInterface;

class MeetupTest extends AbstractProviderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getProviderClassName()
    {
        return \SocialConnect\OAuth2\Provider\Meetup::class;
    }

    /** @covers \SocialConnect\OAuth2\Provider\Meetup::parseToken */
    public function testParsingToken()
    {
        $expires = time() + 200;
        $body = json_encode([
            'access_token' => 'foo',
            'expires_in'   => 200,
            'user_id'      => 'bar',
            'expires'      => $expires,
        ]);

        $meetup = new Meetup(
            self::createMock(ClientInterface::class),
            self::createMock(SessionInterface::class),
            self::createMock(Consumer::class),
            []
        );

        $token = $meetup->parseToken($body);

        self::assertInstanceOf(AccessToken::class, $token);
        self::assertAttributeEquals('foo', 'token', $token);
        self::assertAttributeEquals($expires, 'expires', $token);
        self::assertAttributeEquals('bar', 'uid', $token);
    }

    /**
     * @expectedException \SocialConnect\Provider\Exception\InvalidAccessToken
     * @covers \SocialConnect\OAuth2\Provider\Meetup::parseToken
     */
    public function testParsingTokenFailsWithInvalidBody()
    {
        $meetup = new Meetup(
            self::createMock(ClientInterface::class),
            self::createMock(SessionInterface::class),
            self::createMock(Consumer::class),
            []
        );

        $meetup->parseToken(json_encode(false));
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestResponseForGetIdentity(): Response
    {
        return new Response(
            200,
            [],
            json_encode([
                'id' => 12345,
                'name' => 'Dmitry',
                'gender' => 'sex',
                'photo' => [
                    'photo_link' => 'http://test.com/1.jpg'
                ],
            ])
        );
    }
}
