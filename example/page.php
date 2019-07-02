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
        .top-buffer { margin-top:20px; }
    </style>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
    <div class="container top-buffer">
        <form action="/" method="post">
            <legend>Auth from Social Networks</legend>
            <?php foreach ($configureProviders['provider'] as $name => $parameters) : ?>
                <?php
                $enabled = true;
                if (isset($parameters['enabled'])) {
                    $enabled = (bool) $parameters['enabled'];
                }
                ?>
                <button class="btn btn-default col-lg-2 col-md-2 col-sm-4 col-xs-6" name="provider" type="submit" value="<?php echo strtolower($name); ?>"<?php echo (!$enabled) ? ' disabled="disabled"' : ''; ?>>
                    <i class="fa fa-<?php echo strtolower($name); ?>"></i> <?php echo $name; ?>
                </button>
            <?php endforeach; ?>
        </form>
    </div>
</body>
</html>
