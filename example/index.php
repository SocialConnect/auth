<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

error_reporting(-1);
ini_set('display_errors', 1);

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/vendor/autoload.php';
$configureProviders = include_once 'config.php';

$httpClient = new \SocialConnect\Common\Http\Client\Curl();

/**
 * By default We are using Curl class from SocialConnect/Common
 * but you can use Guzzle wrapper ^5.3|^6.0
 */
//$httpClient = new \SocialConnect\Common\Http\Client\Guzzle(
//    new \GuzzleHttp\Client()
//);

/**
 * Why We need Cache decorator for HTTP Client?
 * Providers like OpenID & OpenIDConnect require US
 * to request OpenID specification (and JWK(s) for OpenIDConnect)
 *
 * It's not a good idea to request it every time, because it's unneeded round trip to the server
 * if you are using OpenID or OpenIDConnect we suggest you to use cache
 *
 * If you don`t use providers like (Steam) from OpenID or OpenIDConnect
 * you may skip this because it's not needed
 */
$httpClient = new \SocialConnect\Common\Http\Client\Cache(
    $httpClient,
    /**
     * Please dont use FilesystemCache for production/stage env, only for local testing!
     * It doesnot support cache expire (remove)
     */
    new \Doctrine\Common\Cache\FilesystemCache(
        __DIR__ . '/cache'
    )
);

/**
 * By default collection factory is null, in this case Auth\Service will create
 * a new instance of \SocialConnect\Auth\CollectionFactory
 * you can use custom or register another providers by CollectionFactory instance
 */
$collectionFactory = null;

$service = new \SocialConnect\Auth\Service(
    $httpClient,
    new \SocialConnect\Provider\Session\Session(),
    $configureProviders,
    $collectionFactory
);

$app = new \Slim\App(
    [
        'settings' => [
            'displayErrorDetails' => true
        ]
    ]
);

$app->any('/dump', function() {
    dump($_POST);
    dump($_GET);
    dump($_SERVER);
});

$app->get('/auth/cb/{provider}/', function (\Slim\Http\Request $request) use (&$configureProviders, $service) {
    $provider = strtolower($request->getAttribute('provider'));

    if (!$service->getFactory()->has($provider)) {
        throw new \Exception('Wrong $provider passed in url : ' . $provider);
    }

    $provider = $service->getProvider($provider);

    $accessToken = $provider->getAccessTokenByRequestParameters($_GET);
    dump($accessToken);

    dump($accessToken->getUserId());
    dump($accessToken->getExpires());

    $user = $provider->getIdentity($accessToken);
    dump($user);
});

$app->get('/', function () {
    include_once 'page.php';
});

$app->post('/', function () use (&$configureProviders, $service) {
    try {
        if (!empty($_POST['provider'])) {
            $providerName = $_POST['provider'];
        } else {
            throw new \Exception('No provider passed in POST Request');
        }

        $provider = $service->getProvider($providerName);

        header('Location: ' . $provider->makeAuthUrl());
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    exit;
});

$app->run();
