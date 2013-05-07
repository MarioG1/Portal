<?php
class Design {

    /**
     * Path to the content folder
     *
     * @var string
     */
    private $content_folder;

    /**
     * Holds all loaded templates
     *
     * @var Statistics_Twig_Template[]
     */
    private $templates;

    /**
     * Holds all loaded modules
     *
     * @var array
     */
    private $modules;

    /**
     * Reference for the main twig environment
     *
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Reference to the main design template
     *
     * @var Statistics_Twig_Template
     */
    private $index;

    /**
     * An new design. Everything depends on this.<br>
     * The default template folders are templates/$design and templates/views/$designs <br>
     * The default content folder is contents/$design
     *
     * @param string       $design
     * @param string       $content_folder
     * @param string|array $template_path
     */
    function __construct($design, $content_folder = null, $template_path = null) {
        global $lang;

        $tpls = array(
            __ROOT__ . 'templates/' . $design . '/views',
            __ROOT__ . 'templates/' . $design
        );

        if($template_path != null) {
            if(is_array($template_path))
                $tpls = $template_path;
            elseif(is_string($template_path))
                $tpls = array($template_path);
        }


        $twig_loader = new Twig_Loader_Filesystem($tpls);
        $this->twig = new Twig_Environment($twig_loader, array(
                                                              'base_template_class' => 'Statistics_Twig_Template',
                                                              'debug'               => DEBUG,
                                                              'cache'               => __ROOT__ . 'cache/twig'
                                                         ));
        if(DEBUG)
            $this->twig->addExtension(new Twig_Extension_Debug());

        $this->twig->addExtension(new Statistics_Twig_Extension());
        $this->twig->getExtension('core')->setTimezone(Util::getOption('timezone', fTimestamp::getDefaultTimezone()));

        if(is_null($content_folder) || !is_string($content_folder))
            $this->content_folder = __ROOT__ . 'contents/' . $design . '/';
        else
            $this->content_folder = $content_folder;

        $this->templates = array();
        $this->modules = array();

        $this->index = $this->loadTemplate('index', '__main__');
    }

    /**
     * Loads the called module
     *
     * @param $content
     *
     * @return Module|null
     * @throws fEnvironmentException
     * @throws fProgrammerException
     */
    private function loadModule($content) {
        if(strpos($content, '.php') === false)
            $content .= '.php';

        $path = $this->content_folder . $content;


        if(!file_exists($path)) {
            throw new fProgrammerException(
                'The path specified for %1$s, %2$s, does not exist on the filesystem',
                $content,
                $path
            );
        }

        if(!is_readable($path)) {
            throw new fEnvironmentException(
                'The path specified for %1$s, %2$s, is not readable',
                $content,
                $path
            );
        }

        $this->modules[] = $content;

        try {
            return new Module($content, $this->content_folder, $this);
        } catch(fException $e) {
            fCore::debug(array('design error: ', $e));

            if(fRequest::isAjax())
                die('ajax_error');

            if(!fMessaging::check('*', '{errors}'))
                fMessaging::create('critical', '{errors}', $e);
        }

        return null;
    }

    /**
     * Loads the given template and returns it.
     *
     * @param string$template
     * @param null  $name
     *
     * @return Statistics_Twig_Template
     */
    public function loadTemplate($template, $name = null) {
        if(strpos($template, '.tpl') === false)
            $template .= '.tpl';

        $tpl = $this->twig->loadTemplate($template);

        if($name == null)
            $name = str_replace('.tpl', '', $template);

        $this->templates[$name] = $tpl;

        return $tpl;
    }

    /**
     * Returns the index template.
     *
     * @return Statistics_Twig_Template
     */
    public function getIndex() {
        return $this->index;
    }

    /**
     * Returns true of the template key 'tpl' is set.
     *
     * @return bool
     */
    public function isTplSet() {
        return isset($this->templates['tpl']);
    }

    /**
     * If $content is cached the execution will stop immediately and the cached content will be echoed.
     *
     * @param $content
     */
    private function displayCached($content) {
        global $cache;

        if(DEVELOPMENT || fMessaging::check('*', '{errors}'))
            return;

        if(fRequest::get('name', 'string') != '' && $content != 'error.php')
            $content = $content . '_' . fRequest::get('name', 'string');

        $cached = $cache->get($content . '.' . DB_TYPE . '.cache');

        if($cached == null)
            return;

        fCore::debug('returned cached: ' . $cached);

        try {
            $file = new fFile(__ROOT__ . 'cache/files/' . $content . '.' . DB_TYPE . '.cache');
            $time = $file->getMTime();
            $text = '
                <small>
                    <em>cached (' . $time->format('H:i:s') . ')</em>
                </small>
             ';
            $cached = preg_replace('%<small .*id="execution_time".*>(.*)</small>%si', $text, $cached);
        } catch(fValidationException $e) {
        }

        exit($cached);
    }

    /**
     * Renders the design displays it.
     *
     * @param $content
     */
    public function display($content) {
        global $cache;

        $error = false;
        $err = $tpl = $this->twig->loadTemplate('error.tpl');

        if(fMessaging::check('*', '{errors}')) {
            $error = true;
            $tpl = $err;
            $content = 'error';
        }

        $this->displayCached($content);

        $this->loadModule($content);

        if(isset($this->templates['tpl']))
            $tpl = $this->templates['tpl'];

        $output = $this->templates['__main__']->render(array('tpl' => $tpl));

        if(fRequest::get('name', 'string') != '' && $content != 'error.php')
            $content = $content . '_' . fRequest::get('name', 'string');

        if(!DEVELOPMENT
           && !is_null($cache)
           && !$error
           && !fRequest::isAjax()
           && !fMessaging::check('*', '{errors}')
           && !fMessaging::check('no-cache', '{cache}')
           && $content != 'error.php'
        ) {
            if($cache->set($content . '.' . DB_TYPE . '.cache', $output, Util::getOption('cache.pages', 60)))
                fCore::debug('cached for ' . Util::getOption('cache.pages', 60) . ' seconds: ' . $content);
        }

        fMessaging::retrieve('no-cache', '{cache}');

        echo $output;
    }
}