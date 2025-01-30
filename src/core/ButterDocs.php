<?php

/**
 * ButterDocs application core.
 * @category Documentation parser
 * @package eugabrielsilva/butterdocs
 * @author Gabriel Silva
 * @copyright Copyright (c) 2021
 * @license MIT
 * @link https://github.com/eugabrielsilva/butterdocs
 * @version 1.0
 */
class ButterDocs
{

    /**
     * Markdown parser instance.
     * @var ParsedownExtended
     */
    private $parser;

    /**
     * Holds the application base url.
     * @var string
     */
    private $baseUrl;

    /**
     * Holds the current version.
     * @var string
     */
    private $version;

    /**
     * Holds the version list.
     * @var array
     */
    private $versionList = [];

    /**
     * Holds the latest version.
     * @var string
     */
    private $lastVersion;

    /**
     * Holds the current route.
     * @var string
     */
    private $route;

    /**
     * Application configuration.
     * @var array
     */
    private static $appConfig = [];

    /**
     * Current version configuration.
     * @var array
     */
    private static $versionConfig = [];

    /**
     * Creates a new ButterDocs application.
     * @param array $appConfig App configuration.
     */
    public function __construct(array $appConfig)
    {
        // Saves the app config
        self::$appConfig = $appConfig;

        // Sets the base URL
        $folder = trim(mb_substr($_SERVER['PHP_SELF'], 0, mb_strpos($_SERVER['PHP_SELF'], '/index.php')), '/');
        $this->baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . $folder . (!empty($folder) ? '/' : '');

        // Gets the version list
        $versions = glob('docs/*', GLOB_ONLYDIR) ?? [];
        foreach ($versions as $dir) $this->versionList[] = preg_replace('~^docs\/~', '', $dir, 1);

        // Gets the last version
        $this->lastVersion = end($this->versionList);

        // Creates the parser
        $this->parser = new ParsedownExtended();

        // Sets the parser options
        $this->parser->setBreaksEnabled(get_config('md_breaks', true));
        $this->parser->setUrlsLinked(get_config('md_urls', true));
    }

    /**
     * Runs ButterDocs application.
     */
    public function unleash()
    {
        // Gets the version from the URL
        if (!empty($_GET['version'])) {
            $this->version = trim(mb_strtolower($_GET['version']));
        } else {
            $this->version = $this->lastVersion;
        }

        // Gets the version configuration file, if exists
        $config = 'docs/' . $this->version . '/config.php';
        if (file_exists($config)) self::$versionConfig = require_once($config);

        // Gets the URL route
        if (empty($_GET['route'])) {
            $startPoint = get_config('start_point', 'README');
            $file = 'docs/' . $this->version . '/' . $startPoint . '.md';
            $this->route = pathinfo($startPoint, PATHINFO_FILENAME);
        } else if ($_GET['route'] == 'search' && (get_config('search', true))) {
            $this->route = 'search';
            return $this->search();
        } else {
            $file = trim(mb_strtolower($_GET['route']));
            $this->route = $file;
            $file = 'docs/' . $this->version . '/' . $file . '.md';
        }

        // Validate docs content
        if (!file_exists($file)) {
            http_response_code(404);
            return $this->view('404.phtml', [
                'title' => get_lang('not_found', 'Page not found') . ' | ' . get_config('application', 'ButterDocs'),
                'base_url' => $this->baseUrl
            ]);
        }

        // Sets the parser Toc URL
        $this->parser->setTocUrl($this->route);

        // Gets docs content
        $content = @file_get_contents($file) ?? '';
        $title = trim(str_replace('#', '', strtok($content, "\n")));
        $content = $this->parser->text($content);
        $content = $this->doReplaces($content);

        // Includes the main view
        return $this->view('main.phtml', [
            'title' => $title . ' | ' . get_config('application', 'ButterDocs'),
            'menu' => $this->getMenu(),
            'content' => $content,
            'application' => get_config('application', 'ButterDocs'),
            'git' => get_config('git_edit', true) ? (trim(get_config('git_url', ''), '/') . '/' . $file) : '',
            'base_url' => $this->baseUrl,
            'version' => $this->version,
            'version_list' => array_reverse($this->versionList),
            'last_version' => $this->lastVersion
        ]);
    }

    /**
     * Performs a search in the docs files.
     */
    private function search()
    {
        // Get search query
        $query = trim($_GET['q'] ?? '');
        if (empty($query)) return header('Location: ' . $this->baseUrl . $this->version);

        // Loop through each docs files
        $matches = [];
        foreach ($this->globRecursive('docs/' . $this->version . '/*.md') as $file) {
            // Ignore menu and starting point files
            $startPointFile = 'docs/' . $this->version . '/' . get_config('start_point', 'README') . '.md';
            $menuFile = $file == 'docs/' . $this->version . '/' . get_config('menu_file', '_menu') . '.md';
            if ($file == $startPointFile || $file == $menuFile) continue;

            // Open file stream
            $handle = fopen($file, 'r');
            if (!$handle) continue;

            // Search in file
            while (!feof($handle)) {
                $buffer = fgets($handle);
                if (mb_stripos($buffer, $query) !== false) $matches[$file][] = $buffer;
            }

            // Close the file stream
            fclose($handle);
        }

        // Parse each matched file
        foreach ($matches as $key => $file) {
            // Get title
            $content = @file_get_contents($key) ?? '';
            $title = trim(str_replace('#', '', strtok($content, "\n")));

            // Parse content
            foreach ($file as &$item) {
                $item = $this->parser->text($item);
                $item = $this->doReplaces($item);
            }

            // Parse URL
            $url = rtrim(explode('/', $key, 3)[2], '.md');

            // Return results
            $matches[$key] = [
                'title' => $title ?? '',
                'url' => $url,
                'results' => $file
            ];
        }

        // Includes the search view
        return $this->view('search.phtml', [
            'title' => get_lang('search_results', 'Search Results') . ' | ' . get_config('application', 'ButterDocs'),
            'menu' => $this->getMenu(),
            'results' => $matches,
            'search' => $query,
            'application' => get_config('application', 'ButterDocs'),
            'base_url' => $this->baseUrl,
            'version' => $this->version,
            'version_list' => array_reverse($this->versionList),
            'last_version' => $this->lastVersion
        ]);
    }

    /**
     * Gets the menu content.
     * @return string Menu markdown.
     */
    private function getMenu()
    {
        // Validates menu content
        $menu_file = 'docs/' . $this->version . '/' . get_config('menu_file', '_menu') . '.md';
        if (is_file($menu_file)) {
            $menu = @file_get_contents($menu_file) ?? '';
        } else {
            $menu = $this->generateMenu();
        }

        // Gets menu content
        $menu = $this->parser->text($menu);
        $menu = $this->doReplaces($menu);
        return $menu;
    }

    /**
     * Generates the sidenav menu automatically.
     * @return string Returns the menu markdown.
     */
    private function generateMenu()
    {
        // Checks if the generate menu setting is enabled
        if (!get_config('generate_menu', true)) return '';

        // Stores the markdown result
        $result = '';

        // Loops through standalone files
        foreach (glob('docs/' . $this->version . '/*.md') as $file) {
            // Adds the file
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($name == 'README') continue;
            $name = str_replace('-', ' ', ucfirst($name));
            $link = rtrim(explode('/', $file, 3)[2], '.md');
            $result .= '- [' . $name . '](' . $link . ")\n";
        }

        // Loops through the version folders
        foreach (glob('docs/' . $this->version . '/*', GLOB_ONLYDIR) as $dir) {

            // Gets the folder files
            $files = glob($dir . '/*.md');
            if (empty($files)) continue;

            // Creates the heading
            $name = str_replace('-', ' ', ucfirst(pathinfo($dir, PATHINFO_BASENAME)));
            $result .= "\n" . '### ' . $name . "\n";

            // Loops through the folder files
            foreach ($files as $file) {

                // Adds the file
                $name = str_replace('-', ' ', ucfirst(pathinfo($file, PATHINFO_FILENAME)));
                $link = str_replace('.md', '', explode('/', $file, 3)[2]);
                $result .= '- [' . $name . '](' . $link . ")\n";
            }
        }

        // Returns the result
        return $result;
    }

    /**
     * Renders a view file.
     * @param string $filename View filename.
     * @param array $parameters (Optional) Associative array of parameters to pass to the view.
     */
    public function view(string $filename, array $parameters = [])
    {
        ob_start();
        extract($parameters);
        include('src/view/' . $filename);
        echo ob_get_clean();
    }

    /**
     * Performs the content replacements.
     * @param string $content Content to perform replacements.
     * @return string Returns the new content.
     */
    private function doReplaces(string $content)
    {
        // Replaces %%version%% tag
        $content = preg_replace('/(?<!\\\)%%version%%/i', $this->version, $content);

        // Replaces %%latest%% tag
        $content = preg_replace('/(?<!\\\)%%latest%%/i', $this->lastVersion, $content);

        // Replaces %%app%% tag
        $content = preg_replace('/(?<!\\\)%%app%%/i', get_config('application', 'ButterDocs'), $content);

        // Replaces %%route%% tag
        $content = preg_replace('/(?<!\\\)%%route%%/i', $this->route, $content);

        // Replaces tag ignores
        $content = preg_replace('/\\\%%(.+)%%/i', '%%$1%%', $content);

        // Returns result
        return $content;
    }

    /**
     * Recursively find pathnames matching a pattern.
     * @param string $pattern Pattern to match.
     * @return array|bool Returns the files as an array, false on errors.
     */
    private function globRecursive(string $pattern)
    {
        $files = glob($pattern) ?? [];
        $folders = glob(dirname($pattern) . '/*', GLOB_ONLYDIR) ?? [];
        foreach ($folders as $dir) $files = array_merge($files, $this->globRecursive($dir . '/' . basename($pattern)));
        return $files;
    }

    /**
     * Gets a configuration variable.
     * @param string $key Configuration key to get.
     * @param mixed $default (Optional) Default value to return if not found.
     * @return mixed Returns the configuration variable or the default value if not found.
     */
    public static function getConfig(string $key, $default = null)
    {
        if (isset(self::$versionConfig[$key])) return self::$versionConfig[$key];
        if (isset(self::$appConfig[$key])) return self::$appConfig[$key];
        return $default;
    }
}
