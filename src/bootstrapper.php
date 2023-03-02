<?php

    // Include Parsedown dependency
    require_once('core/Parsedown.php');
    require_once('core/ParsedownExtra.php');

    // Include ButterDocs core
    require_once('core/ButterDocs.php');

    // Unleash ButterDocs!
    $app = new ButterDocs();
    $app->unleash();

?>