<?php

    // Load settings
    define('APP_CONFIG', require_once('config.php'));

    // Include Parsedown dependency
    require_once('core/Parsedown.php');
    require_once('core/ParsedownExtra.php');
    require_once('core/ParsedownExtended.php');

    // Include ButterDocs core
    require_once('core/ButterDocs.php');

    // Unleash ButterDocs!
    $app = new ButterDocs();
    $app->unleash();

?>