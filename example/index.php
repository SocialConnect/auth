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

$service = new \SocialConnect\Auth\Service($configureProviders, null);
$service->setHttpClient(new \SocialConnect\Common\Http\Client\Curl());

$app = new \Slim\Slim();
$app->any('/dump:params', function() {
    var_dump($_POST);
    var_dump($_GET);
    var_dump($_SERVER);
});
$app->get('/auth/cb/:provider/:params', function ($provider) use (&$configureProviders, $service) {
    $provider = strtolower($provider);
    switch ($provider) {
        case 'facebook':
        case 'github':
        case 'twitter':
        case 'vk':
        case 'instagram':
            $provider = $service->getProvider($provider);
            break;
        default:
            throw new \Exception('Wrong $provider passed in url : ' . $provider);
            break;
    }

    $accessToken = $provider->getAccessToken($_GET);
    var_dump($accessToken);

    $user = $provider->getIdentity($accessToken);
    var_dump($user);
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