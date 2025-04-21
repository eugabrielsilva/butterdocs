<?php

/*
    ----------------------------------------------------------
    Global documentation settings
    ----------------------------------------------------------
    These settings are used as a fallback if no config file
    is provided for a specific version folder.
    ----------------------------------------------------------
*/

return [

    // Application title
    'application' => 'ButterDocs',

    // Documentation starting point file
    'start_point' => 'README',

    // Documentation menu file
    'menu_file' => '_menu',

    // Generate menu automatically if not provided
    'generate_menu' => true,

    // Show "Edit this page on GitHub" button
    'git_edit' => true,

    // GitHub edit URL (including branch)
    'git_url' => 'https://github.com/eugabrielsilva/butterdocs/tree/master',

    // Enable single line breaks
    'md_breaks' => true,

    // Enable automatic URL links
    'md_urls' => true,

    // Enable search in content
    'search' => true,

    // Logo location
    'logo_file' => '../assets/images/logo.png',

    // Dark mode logo location
    'dark_logo_file' => '../assets/images/logo-dark.png',

    // Favicon image location
    'favicon_file' => '../assets/images/favicon.png'

];
