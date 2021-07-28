<?php

    /*
        ------------------------
        Documentation settings
        ------------------------
    */

    define('APP_CONFIG', [

        // Application title
        'application' => 'ButterDocs',

        // Theme to use (must be a valid theme from "assets/css/themes" folder)
        'theme' => 'light',

        // Show "Edit this page on GitHub" button
        'git_edit' => true,

        // GitHub edit URL (including branch)
        'git_url' => 'https://github.com/eugabrielsilva/butterdocs/edit/master',

        // Enable support to Markdown Extra
        'md_extra' => true,

        // Enable single line breaks
        'md_breaks' => true,

        // Enable automatic URL links
        'md_urls' => true

    ]);

    /*
        -----------------------------
        Do not edit below this line!
        -----------------------------
    */

    // Load the bootstrapper
    require_once('src/bootstrapper.php');

    // Unleash ButterDocs!
    $app = new ButterDocs();
    $app->unleash();

?>