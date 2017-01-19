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

$service = new \SocialConnect\Auth\Service($configureProviders, new \SocialConnect\Auth\Provider\CollectionFactory());
$service->setHttpClient(new \SocialConnect\Common\Http\Client\Curl());

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
