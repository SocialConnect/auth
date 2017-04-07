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
use SocialConnect\OpenIDConnect\Exception\InvalidJWT;
use SocialConnect\Common\Http\Client\Client;
use Exception;

/**
 * Class Provider
 * @package SocialConnect\Google
 */
class PixelPin extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getOpenIdUrl()
    {
        return 'https://login.pixelpin.io/.well-known/openid-configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://login.pixelpin.io/';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        return 'https://login.pixelpin.io/connect/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTokenUri()
    {
        return 'https://login.pixelpin.io/connect/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pixelpin';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity(AccessTokenInterface $accessToken)
    {
        $response = $this->httpClient->request(
            $this->getBaseUri() . 'connect/userinfo',
            [
            'access_token' => $accessToken->getToken()
            ],
            Client::GET,
            [
                'Authorization' => 'Bearer ' . $accessToken->getToken()
            ]
        );

        if (!$response->isSuccess()) {
            throw new InvalidResponse(
                'API response with error code',
                $response
            );
        }

        $body = $response->getBody();

        //throw new InvalidJWT($body);

        $result = json_decode($body);

        $sub2         = $result->sub;

        $firstName = (isset($result->given_name) ? $result->given_name : false);
        if($firstName === false)
        {
            $given_name2         = 'Not Set';
        }
        else
        {
            $given_name2          = $result->given_name;
        }

        $familyName = (isset($result->family_name) ? $result->family_name : false);
        if($familyName === false)
        {
            $family_name2          = 'Not Set';
        }
        else
        {
            $family_name2          = $result->family_name;
        }

        $Email = (isset($result->email) ? $result->email : false);
        if($Email === false)
        {
            $email2          = 'Not Set';
        }
        else
        {
            $email2          = $result->email;
        }

        $display_name = (isset($result->displayName) ? $result->displayName : false);
        if($display_name === false)
        {
            $displayName2         = 'Not Set';
        }
        else
        {
            $displayName2         = $result->displayName;
        }

        $Gender = (isset($result->gender) ? $result->gender : false);
        if($Gender === false)
        {
            $gender2          = 'Not Set';
        }
        else
        {
            $gender2          = $result->gender;
        }

        $phone_number = (isset($result->phone_number) ? $result->phone_number : false);
        if($phone_number === false)
        {
            $phoneNumber2          = 'Not Set';
        }
        else
        {
            $phoneNumber2          = $result->phone_number;
        }

        $birth_date = (isset($result->birthdate) ? $result->birthdate : false);
        if($birth_date === false)
        {
            $birthdate2          = 'Not Set';
        }
        else
        {
            $birthdate2          = $result->birthdate;
        }

        $json_address = (isset($result->address) ? $result->address : false);
        if($json_address === false)
        {
            $streetAddress2 = 'Not Set';
            $townCity2      = 'Not Set';
            $region2        = 'Not Set';
            $postalCode2    = 'Not Set';
            $country2       = 'Not Set';
        }
        else
        {
            $jsonAddress          = $result->address;

            $decodeAddress = json_decode($jsonAddress);

            $street_address = (isset($decodeAddress->street_address) ? $decodeAddress->street_address : false);
            if($street_address === false)
            {
                $streetAddress2          = 'Not Set';
            }
            else
            {
                $streetAddress2          = $decodeAddress->street_address;
            }

            $town_city = (isset($decodeAddress->locality) ? $decodeAddress->locality : false);
            if($town_city === false)
            {
                $townCity2          = 'Not Set';
            }
            else
            {
                $townCity2          = $decodeAddress->locality;
            }

            $Region = (isset($decodeAddress->region) ? $decodeAddress->region : false);
            if($Region === false)
            {
                $region2          = 'Not Set';
            }
            else
            {
                $region2          = $decodeAddress->region;
            }

            $postal_code = (isset($decodeAddress->postal_code) ? $decodeAddress->postal_code : false);
            if($postal_code === false)
            {
                $postalCode2          = 'Not Set';
            }
            else
            {
                $postalCode2          = $decodeAddress->postal_code;
            }

            $Country = (isset($decodeAddress->country) ? $decodeAddress->country : false);
            if($Country === false)
            {
                $country2          = 'Not Set';
            }
            else
            {
                $country2          = $decodeAddress->country;
            }
        }

        $sub         = (string)$sub2;
        $given_name  = (string)$given_name2;
        $family_name = (string)$family_name2;
        $email       = (string)$email2;
        $displayName = (string)$displayName2;
        $gender    = (string)$gender2;
        $phoneNumber = (string)$phoneNumber2;
        $birthdate  = (string)$birthdate2;
        $streetAddress = (string)$streetAddress2;
        $townCity = (string)$townCity2;
        $region = (string)$region2;
        $postalCode = (string)$postalCode2;
        $country = (string)$country2;

        $newResult = array(
            "sub" => $sub ,
            "given_name" => $given_name,
            "family_name" => $family_name,
            "email" => $email ,
            "display_name" => $displayName,
            "gender" => $gender ,
            "phone_number" => $phoneNumber,
            "birthdate" => $birthdate ,
            "street_address" => $streetAddress,
            "town_city" => $townCity,
            "region" => $region,
            "postal_code" => $postalCode,
            "country" => $country,
        );

        $encodeNewResult = json_encode($newResult);
        $decodeNewResult = json_decode($encodeNewResult);



        $hydrator = new ObjectMap(
            [
                'sub' => 'id',
                'given_name' => 'firstname',
                'family_name' => 'lastname',
                'email' => 'email',
                'display_name' => 'fullname',
                'gender' => 'gender',
                'phone_number' => 'phone',
                'birthdate' => 'birthdate',
                'street_address' => 'address',
                'town_city' => 'townCity',
                'region'   => 'region',
                'postal_code' => 'postalCode',
                'country' => 'country'
            ]
        );

        return $hydrator->hydrate(new User(), $decodeNewResult);
    }
}