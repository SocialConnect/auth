<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 * @author Alexander Fedyashov <a@fedyashov.com>
 */

namespace SocialConnect\OpenIDConnect\Provider;

use SocialConnect\Provider\AccessTokenInterface;
use SocialConnect\Provider\Exception\InvalidResponse;
use SocialConnect\OpenIDConnect\AbstractProvider;
use SocialConnect\Common\Entity\User;
use SocialConnect\Common\Hydrator\ObjectMap;

/**
 * Class Provider
 * @package SocialConnect\Google
 */
class Google extends AbstractProvider
{
    /**
     * @return array
     */
    public function getKeys()
    {
        return [
            [
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => '80cb59bd57804cdfd86954b7412e0fe37a05e9e6',
                'n'   => 'tfi9-AYfQm6DfbYYGn_QYkmPhK7JagW3NQaChmbu2PCdQCq8qY4ZLyrXtZij3sX71FzqrKOPXN3FTuPWYgmSST6qluMPcPVp-IVvQKv2Zh6ecAMHyH4tgfvJ-chfwFS00zgaNqnMUSsz5LSo4GbiEtVnJOTH3CJM9zbe5xA2HhGUq5PX9uugFNgC2ruDGiSPFRb07_PUwklBmdqE_Mhz7KFyjPAuJenbacyomQEXg4k53VruXMzCquBeVQe77QcdboFPJBTKmMGZVFbOZO49voj_lq0pje3HDZvFK2HljALcuTVn3_6tIHgVbC5AY6CTmRzhLTYFcW0jtF-Cw0a-jQ',
                'e'   => 'AQAB'
            ],
            [
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => '8fa17a75998155d5702b3715f559c513bb81bd1b',
                'n'   => '1K5pkvWNTBhYME97YmILhLPzEaR2v95XCXdCz1rpFNyQGy25nVriQI917VwUOPRI4NWwE8aBJ_-UjcrVO4r-4yiCWxiT9hKYH955RCjS5FcI5WeBszsl8DVzdE-50_3iHSBNZjuSAekqMWFS2W7Qj_CoACHc4Taq1z_S8vtsKjyghxleifqynprvFB2MatueaDEpoJ2znur-A2LcedYlrfzcWxoVelT83ruam50mfVJvMbtk-4s5LvQWu2I099Yuwnh_8hnr3vChtc2dLNP0bEMosVThldKcjkm2adFDg0kSSwRHDlOHPne1l1yzVPJ8hIeiKj30LTr_c0TUF5TQQw',
                'e'   => 'AQAB'
            ],
            [
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => '11fe9f68d828d8533728984412a0116a828110f9',
                'n'   => 'zl1Rry4crEQpRvBIskbh-A5KO7aezkvx0TJxEk8rzLRpaNUjdRz_Tfq08vgC6usvG6n2PcOsQ3ka7hvIQvM4L5SkOiJN4xC9gGUq30F2jJTwYvSTFJtd-hYMmbd5K4Ghq12FgL9vIOXwCt-UXUkQTN1YNBBwHfrIAUJpnunHedpuAU1dykm8GYcR-yE2XWDNqGmBn7x8y3bOiGzuyY9ncmplFX-6tjwuOuplxS2M7cvJl0p7eIWsT4wsQsKVCRCaFq9hYADDm4HmmcGaZJMPdFG6bJOoHQW-2Jy9u7qrFy5DULnE8BTEsVLyMPAO5K63siFaIZT4IUMnEL2IfJwgeQ',
                'e'   => 'AQAB'
            ],
            [
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => 'de97f30514c9e858bc0487763f73f7e0c5fa0ab1',
                'n'   => '1r8QOq8iQpMCjhLlF3w8SykIwWYiKBdchHbNIFszJGvNcVoOiHEe7dGNu8ByWXsyK0Sil3XgcDaXUCrIikH9DAxEMVewS37GO_qdFHpX29jOu398N4j2skSCgFvBFBZdihcD1LLkTYCMiUfGIPRZVrGOefn89uDGTHz9w912HiWcl-rhi8rxMfTXEpQ4thNRZNZOkD4j00XWfB5C6aVbYa2ry0T_S7biLu0NXiqKSUu5_8L3yWZBZesKZLZ76xDZZ_TWBhcrxrCLxwjG6id8dbM74BAAmLpOT93ortQaR8V4t5vOr3xKg04sgks9xN932C7KiGLr3jgMuuUzxxVDnQ',
                'e'   => 'AQAB'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://www.googleapis.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'oauth2/v1/userinfo',
            [
                'access_token' => $accessToken->getToken()
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $body = $response->getBody();
        $result = json_decode($body);

        $hydrator = new ObjectMap(
            [
                'id' => 'id',
                'given_name' => 'firstname',
                'family_name' => 'lastname',
                'email' => 'email',
                'name' => 'fullname',
                'gender' => 'sex',
            ]
        );

        return $hydrator->hydrate(new User(), $result);
    }
}
