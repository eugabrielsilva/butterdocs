<?php

/*
    ----------------------------------------------------------
    ButterDocs bootstrapper
    ----------------------------------------------------------
    This file imports and starts the ButterDocs application.
    We do not recommend editing this or the app may break.
    ----------------------------------------------------------
*/

// Include helpers
require_once('core/_helpers.php');

// Include dependencies
require_once('core/Parsedown.php');
require_once('core/ParsedownExtra.php');
require_once('core/ParsedownExtended.php');

// Include ButterDocs core
require_once('core/ButterDocs.php');

// Include the global configuration file
$config = require_once('config.php');

// Unleash ButterDocs!
$app = new ButterDocs($config);
$app->unleash();
