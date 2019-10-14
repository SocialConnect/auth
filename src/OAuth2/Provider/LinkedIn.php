<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author: Bogdan Popa https://github.com/icex <bogdan@pixelwattstudio.com>
 */
declare(strict_types=1);

namespace SocialConnect\OAuth2\Provider;

use SocialConnect\Common\ArrayHydrator;
use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Common\Entity\User;

class LinkedIn extends \SocialConnect\OAuth2\AbstractProvider
{
    const NAME = 'linkedin';

    /**
     * @var array
     */
    protected $options = [
        /**
         * It's needed additional API call to fetch email, by default it's disabled
         */
        'fetch_emails' => false
    ];

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://api.linkedin.com/v2/';
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
    public function prepareRequest(string $method, string $uri, array &$headers, array &$query, AccessTokenInterface $accessToken = null): void
    {
        $headers['Content-Type'] = 'application/json';

        if ($accessToken) {
            $headers['Authorization'] = "Bearer {$accessToken->getToken()}";
        }
    }

    protected function fetchPrimaryEmail(AccessTokenInterface $accessToken, User $user)
    {
        $response = $this->request(
            'GET',
            'emailAddress',
            [
                'q' => 'members',
                'projection' => '(elements*(primary,type,handle~))'
            ],
            $accessToken
        );

        if (isset($response['elements'])) {
            $element = array_shift($response['elements']);
            if ($element && isset($element['handle~']) && isset($element['handle~']['emailAddress'])) {
                $user->email = $element['handle~']['emailAddress'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $query = [];

        // @link https://docs.microsoft.com/en-us/linkedin/shared/integrations/people/profile-api?context=linkedin/consumer/context
        $fields = $this->getArrayOption(
            'identity.fields',
            [
                'id',
                'firstName',
                'lastName',
                'profilePicture(displayImage~:playableStreams)',
            ]
        );
        if ($fields) {
            $query['projection'] = '(' . implode(',', $fields) . ')';
        }

        $response = $this->request(
            'GET',
            'me',
            $query,
            $accessToken
        );

        $hydrator = new ArrayHydrator([
            'id'           => 'id',
            'emailAddress' => 'email',
            'firstName'    => static function ($value, User $user) {
                if ($value['localized']) {
                    $user->firstname = array_pop($value['localized']);
                }
            },
            'lastName'     => static function ($value, User $user) {
                if ($value['localized']) {
                    $user->lastname = array_pop($value['localized']);
                }
            },
            'profilePicture'     => static function ($value, User $user) {
                if (isset($value['displayImage~']) && isset($value['displayImage~']['elements'])) {
                    $biggestElement = array_shift($value['displayImage~']['elements']);
                    if (isset($biggestElement['identifiers'])) {
                        $biggestElementIdentifier = array_pop($biggestElement['identifiers']);
                        if (isset($biggestElementIdentifier['identifier'])) {
                            $user->pictureURL = $biggestElementIdentifier['identifier'];
                        }
                    }
                }
            },
        ]);

        /** @var User $identity */
        $identity = $hydrator->hydrate(new User(), $response);

        if ($this->getBoolOption('fetch_emails', false)) {
            $this->fetchPrimaryEmail($accessToken, $identity);
        }

        return $identity;
    }
}
