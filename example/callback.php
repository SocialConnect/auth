<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

include_once __DIR__ . '/../vendor/autoload.php';

$configureProviders = include_once 'config.php';
$service = new \SocialConnect\Auth\Service($configureProviders, null);

$provider = strtolower($_GET['provider']);

switch ($provider) {
    case 'github':
    case 'vk':
        $provider = $service->getProvider($provider);
        break;
    default:
        throw new \Exception('Wrong $provider passed in url : ', $provider);
        break;
}

$code = $_GET['code'];

var_dump(array(
    'code' => $code
));