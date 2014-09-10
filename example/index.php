<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

include_once __DIR__ . '/../vendor/autoload.php';

$service = new \SocialConnect\Auth\Service(array(

), null);

$provider = $service->getProvider('Github');