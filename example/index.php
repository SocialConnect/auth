<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

include_once __DIR__ . '/../vendor/autoload.php';

$configureProviders = include_once 'config.php';

$service = new \SocialConnect\Auth\Service($configureProviders, null);

$provider = $service->getProvider('Github');

header('Location: ' . $provider->makeAuthUrl());