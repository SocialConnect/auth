<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

include_once __DIR__ . '/../vendor/autoload.php';

$configureProviders = include_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = new \SocialConnect\Auth\Service($configureProviders, null);
    $service->setHttpClient(new \SocialConnect\Common\Http\Client\Guzzle());

    $provider = $service->getProvider('Github');

    header('Location: ' . $provider->makeAuthUrl());
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SocialConnect | Auth Example</title>
</head>
<body>
    <form action="/" method="post">
        <?php foreach ($configureProviders['provider'] as $name => $parameters) : ?>
            <button name="provider" type="submit" value="<?php echo $name; ?>">
                <?php echo $name; ?>
            </button>
        <?php endforeach; ?>
    </form>
</body>
</html>