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
         * Holds the application base url.
         * @var string
         */
        private static $baseUrl;

        /**
         * Holds the current version.
         * @var string
         */
        private static $version;

        /**
         * Holds the version list.
         * @var array
         */
        private static $versionList;

        /**
         * Holds the latest version.
         * @var string
         */
        private static $lastVersion;

        /**
         * Holds the current route.
         * @var string
         */
        private static $route;

        /**
         * Creates a new ButterDocs application.
         */
        public function __construct(){
            // Sets the base URL
            $folder = trim(substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/index.php')), '/');
            self::$baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . $folder . (!empty($folder) ? '/' : '');

            // Gets the version list
            foreach(glob('docs/*', GLOB_ONLYDIR) as $dir){
                self::$versionList[] = preg_replace('/docs\//', '', $dir, 1);
            }

            // Gets the last version
            self::$lastVersion = end(self::$versionList);
        }

        /**
         * Runs ButterDocs application.
         */
        public function unleash(){
            // Gets the URL version
            if(!empty($_GET['version'])){
                self::$version = trim(strtolower($_GET['version']));
            }else{
                self::$version = self::$lastVersion;
            }

            // Gets the URL route
            if(empty($_GET['route'])){
                $file = 'docs/' . self::$version . '/home.md';
                self::$route = 'home';
            }else{
                $file = trim(strtolower($_GET['route']));
                self::$route = $file;
                $file = 'docs/' . self::$version . '/' . $file . '.md';
            }

            // Creates the parser
            $parser = new Parsedown();
            $parser->setBreaksEnabled(true);

            // Validate docs content
            if(!file_exists($file)){
                http_response_code(404);
                return $this->view('404.phtml', [
                    'title' => 'Page not found | ' . APP_CONFIG['application'],
                    'theme' => APP_CONFIG['theme'],
                    'base_url' => self::$baseUrl
                ]);
            }

            // Gets docs content
            $content = file_get_contents($file);
            $title = trim(str_replace('#', '', strtok($content, "\n")));
            $content = $parser->text($content);

            // Validates menu content
            $menu_file = 'docs/' . self::$version . '/menu.md';
            if(file_exists($menu_file)){
                $menu = file_get_contents($menu_file);
            }else{
                $menu = '';
            }

            // Gets menu content
            $menu = $parser->text($menu);

            // Replaces content
            $content = $this->doReplaces($content);
            $menu = $this->doReplaces($menu);

            // Includes the main view
            return $this->view('main.phtml', [
                'title' => $title . ' | ' . APP_CONFIG['application'],
                'menu' => $menu,
                'content' => $content,
                'theme' => APP_CONFIG['theme'],
                'application' => APP_CONFIG['application'],
                'git' => APP_CONFIG['git_edit'] ? (trim(APP_CONFIG['git_url'], '/') . '/' . $file) : '',
                'base_url' => self::$baseUrl,
                'version' => self::$version,
                'version_list' => array_reverse(self::$versionList),
                'last_version' => self::$lastVersion
            ]);
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
            $content = preg_replace('/(?<!\\\)%%version%%/i', self::$version, $content);
            
            // Replaces %%latest%% tag
            $content = preg_replace('/(?<!\\\)%%latest%%/i', self::$lastVersion, $content);

            // Replaces %%app%% tag
            $content = preg_replace('/(?<!\\\)%%app%%/i', APP_CONFIG['application'], $content);

            // Replaces %%route%% tag
            $content = preg_replace('/(?<!\\\)%%route%%/i', self::$route, $content);

            // Replaces tag ignores
            $content = preg_replace('/\\\%%(.+)%%/i', '%%$1%%', $content);

            // Returns result
            return $content;
        }

    }

?>