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

    try {
        if (!empty($_POST['provider'])) {
            $providerName = $_POST['provider'];
        } else {
            throw new \Exception('No provider passed in POST Request');
        }

        $provider = $service->getProvider($providerName);
        header('Location: ' . $provider->makeAuthUrl());
    } catch (\Exception $e) {
        echo 'Failed to get' . $providerName . ' provider';
    }
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SocialConnect | Auth Example</title>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
    <form action="/" method="post">
        <?php foreach ($configureProviders['provider'] as $name => $parameters) : ?>
            <?php
                $enabled = true;
                if (isset($parameters['enabled'])) {
                    $enabled = (bool) $parameters['enabled'];
                }
            ?>
            <button name="provider" type="submit" value="<?php echo $name; ?>"<?php echo (!$enabled) ? ' disabled="disabled"' : ''; ?>>
                <i class="fa fa-<?php echo strtolower($name); ?>"></i> <?php echo $name; ?>
            </button>
        <?php endforeach; ?>
    </form>
</body>
</html>