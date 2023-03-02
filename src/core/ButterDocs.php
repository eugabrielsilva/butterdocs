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
    class ButterDocs{

        /**
         * Markdown parser instance.
         * @var Parsedown
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
         * Creates a new ButterDocs application.
         */
        public function __construct(){
            // Load the settings
            define('APP_CONFIG', require_once('config.php'));

            // Sets the base URL
            $folder = trim(mb_substr($_SERVER['PHP_SELF'], 0, mb_strpos($_SERVER['PHP_SELF'], '/index.php')), '/');
            $this->baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . $folder . (!empty($folder) ? '/' : '');

            // Gets the version list
            foreach(glob('docs/*', GLOB_ONLYDIR) as $dir){
                $this->versionList[] = preg_replace('/docs\//', '', $dir, 1);
            }

            // Gets the last version
            $this->lastVersion = end($this->versionList);

            // Creates the parser
            if(APP_CONFIG['md_extra'] ?? true){
                $this->parser = new ParsedownExtra();
            }else{
                $this->parser = new Parsedown();
            }

            // Sets the parser options
            $this->parser->setBreaksEnabled(APP_CONFIG['md_breaks'] ?? true);
            $this->parser->setUrlsLinked(APP_CONFIG['md_urls'] ?? true);
        }

        /**
         * Runs ButterDocs application.
         */
        public function unleash(){
            // Gets the URL version
            if(!empty($_GET['version'])){
                $this->version = trim(mb_strtolower($_GET['version']));
            }else{
                $this->version = $this->lastVersion;
            }

            // Gets the URL route
            if(empty($_GET['route'])){
                $file = 'docs/' . $this->version . '/home.md';
                $this->route = 'home';
            }else if($_GET['route'] == 'search'){
                $this->route = 'search';
                return $this->search();
            }else{
                $file = trim(mb_strtolower($_GET['route']));
                $this->route = $file;
                $file = 'docs/' . $this->version . '/' . $file . '.md';
            }

            // Validate docs content
            if(!file_exists($file)){
                http_response_code(404);
                return $this->view('404.phtml', [
                    'title' => 'Page not found | ' . (APP_CONFIG['application'] ?? 'ButterDocs'),
                    'base_url' => $this->baseUrl
                ]);
            }

            // Gets docs content
            $content = file_get_contents($file);
            $title = trim(str_replace('#', '', strtok($content, "\n")));
            $content = $this->parser->text($content);

            // Validates menu content
            $menu_file = 'docs/' . $this->version . '/_menu.md';
            if(is_file($menu_file)){
                $menu = file_get_contents($menu_file);
            }else{
                $menu = $this->generateMenu();
            }

            // Gets menu content
            $menu = $this->parser->text($menu);

            // Replaces content
            $content = $this->doReplaces($content);
            $menu = $this->doReplaces($menu);

            // Includes the main view
            return $this->view('main.phtml', [
                'title' => $title . ' | ' . (APP_CONFIG['application'] ?? 'ButterDocs'),
                'menu' => $menu,
                'content' => $content,
                'application' => APP_CONFIG['application'] ?? 'ButterDocs',
                'git' => (APP_CONFIG['git_edit'] ?? true) ? (trim(APP_CONFIG['git_url'] ?? '', '/') . '/' . $file) : '',
                'base_url' => $this->baseUrl,
                'version' => $this->version,
                'version_list' => array_reverse($this->versionList),
                'last_version' => $this->lastVersion
            ]);
        }

        /**
         * Performs a search in the docs files.
         */
        private function search(){
            // Get search query
            $query = $_GET['q'] ?? '';
            if(empty($query)) return header('Location: ' . $this->baseUrl . $this->version);

            // Loop through each docs files
            $matches = [];
            foreach($this->globRecursive('docs/' . $this->version . '/*.md') as $file){
                // Ignore menu and home files
                if($file == 'docs/' . $this->version . '/_menu.md' || $file == 'docs/' . $this->version . '/home.md') continue;

                // Open file stream
                $handle = fopen($file, 'r');
                if(!$handle) continue;

                // Search in file
                while(!feof($handle)){
                    $buffer = fgets($handle);
                    if(mb_stripos($buffer, $query) !== false) $matches[$file][] = $buffer;
                }

                // Close the file stream
                fclose($handle);
            }

            // Parse each matched file
            foreach($matches as $key => $file){
                // Get title
                $content = file_get_contents($key);
                $title = trim(str_replace('#', '', strtok($content, "\n")));

                // Parse content
                foreach($file as &$item){
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

            // Validates menu content
            $menu_file = 'docs/' . $this->version . '/_menu.md';
            if(is_file($menu_file)){
                $menu = file_get_contents($menu_file);
            }else{
                $menu = $this->generateMenu();
            }

            // Gets menu content
            $menu = $this->parser->text($menu);
            $menu = $this->doReplaces($menu);

            // Includes the search view
            return $this->view('search.phtml', [
                'title' => 'Search Results | ' . (APP_CONFIG['application'] ?? 'ButterDocs'),
                'menu' => $menu,
                'results' => $matches,
                'search' => $query,
                'application' => APP_CONFIG['application'] ?? 'ButterDocs',
                'base_url' => $this->baseUrl,
                'version' => $this->version,
                'version_list' => array_reverse($this->versionList),
                'last_version' => $this->lastVersion
            ]);
        }

        /**
         * Generates the sidenav menu automatically.
         * @return string Returns the menu markdown.
         */
        private function generateMenu(){
            // Checks if the generate menu setting is enabled
            if(!(APP_CONFIG['generate_menu'] ?? true)) return '';

            // Stores the markdown result
            $result = '';

            // Loops through standalone files
            foreach(glob('docs/' . $this->version . '/*.md') as $file){
                // Adds the file
                $name = pathinfo($file, PATHINFO_FILENAME);
                if($name == 'home') continue;
                $name = str_replace('-', ' ', ucfirst($name));
                $link = rtrim(explode('/', $file, 3)[2], '.md');
                $result .= '- [' . $name . '](' . $link. ")\n";
            }

            // Loops through the version folders
            foreach(glob('docs/' . $this->version . '/*', GLOB_ONLYDIR) as $dir){

                // Gets the folder files
                $files = glob($dir . '/*.md');
                if(empty($files)) continue;

                // Creates the heading
                $name = str_replace('-', ' ', ucfirst(pathinfo($dir, PATHINFO_BASENAME)));
                $result .= "\n" . '### ' . $name . "\n";

                // Loops through the folder files
                foreach($files as $file){

                    // Adds the file
                    $name = str_replace('-', ' ', ucfirst(pathinfo($file, PATHINFO_FILENAME)));
                    $link = str_replace('.md', '', explode('/', $file, 3)[2]);
                    $result .= '- [' . $name . '](' . $link. ")\n";
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
        public function view(string $filename, array $parameters = []){
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
        private function doReplaces(string $content){
            // Replaces %%version%% tag
            $content = preg_replace('/(?<!\\\)%%version%%/i', $this->version, $content);

            // Replaces %%latest%% tag
            $content = preg_replace('/(?<!\\\)%%latest%%/i', $this->lastVersion, $content);

            // Replaces %%app%% tag
            $content = preg_replace('/(?<!\\\)%%app%%/i', APP_CONFIG['application'], $content);

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
        private function globRecursive(string $pattern){
            $files = glob($pattern);
            foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR) as $dir) {
                $files = array_merge($files, $this->globRecursive($dir . '/' . basename($pattern)));
            }
            return $files;
        }

    }
