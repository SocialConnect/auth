<?php
    $configureProviders = include 'config.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SocialConnect | Auth Example</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .top-buffer { margin-top: 40px; }
    </style>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<?php
    $configured = [];
    $supported = [];

    foreach ($configureProviders['provider'] as $name => $parameters) {
        if (isset($parameters['applicationId']) && $parameters['applicationId'] !== '') {
            $configured[$name] = $parameters;
        } else {
            $supported[$name] = $parameters;
        }
    }
?>
<body>
    <div class="container top-buffer">
        <h1 style="text-align: center;">Hello! It's a demo project for `socialconnect/auth`</h1>
        <h2 style="text-align: center;">
            <a href="https://socialconnect.lowl.io/installation.html" target="_blank">Getting Started</a>
            ::
            <a href="https://socialconnect.lowl.io/" target="_blank">Documentation</a>
            ::
            <a href="https://github.com/socialconnect/auth" target="_blank">GitHub</a>
        </h2>

        <div class="row top-buffer">
            <form action="/" method="post">
                <legend>Configured providers, you can login in with it</legend>
                <?php foreach ($configured as $name => $parameters) : ?>
                    <button class="btn btn-default col-lg-2 col-md-2 col-sm-4 col-xs-6" name="provider" type="submit" value="<?php echo strtolower($name); ?>">
                        <i class="fa fa-<?php echo strtolower($name); ?>"></i> <?php echo $name; ?>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>

        <div class="row top-buffer">
            <legend>Supported, but not configured</legend>
            <?php foreach ($supported as $name => $parameters) : ?>
                <button class="btn btn-default col-lg-2 col-md-2 col-sm-4 col-xs-6" name="provider" type="submit" value="<?php echo strtolower($name); ?>" disabled="disabled">
                    <i class="fa fa-<?php echo strtolower($name); ?>"></i> <?php echo $name; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
