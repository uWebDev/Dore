<?php

namespace Dore\Core\Handler;


/**
 * Manages passing variables to templates and rendering them
 * @package Core
 */
class View
{

    /**
     * View helper
     */
    protected $helper;

    /**
     * Full path to template file
     * @var string
     */
    protected $path;

    /**
     * The name of the view.
     * @var string
     */
    public $name;

    /**
     * Stores all the variables passed to the view
     * @var array
     */
    protected $_data = array();

    /**
     * Constructs the view
     *
     * @param string $name The name of the template to use
     */
    public function __construct($name = 'debug')
    {
        $this->setTemplate($name);
    }

    /**
     * It sets the pattern used for rendering
     *
     * @param string $name The name of the template to use
     *
     * @throws \Exception If specified template is not found
     */
    public function setTemplate($name)
    {
        $this->name = $name;
        $file = __DIR__ . DS . 'Views' . DS . $name . '.php';
        if (!is_file($file)) {
            throw new \Exception("View {$name} not found.");
        }
        $this->path = $file;
    }

    /**
     * Manages storing the data passed to the view as properties
     *
     * @param string $key Property name
     * @param string $val Property value
     *
     * @return void
     */
    public function __set($key, $val)
    {
        $this->_data[$key] = $val;
    }

    /**
     * Manages checking whether a dynamic property has been defined or not
     *
     * @param string $key Property name
     *
     * @return boolean
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * Manages accessing passed data as properties
     *
     * @param string $key Property name
     *
     * @return mixed   Property value
     * @throws \Exception If the property is not found
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        throw new \Exception("Value {$key} not set for view {$this->name}");
    }

    /**
     * Renders the template, all dynamically set properties
     * will be available inside the view file as variables.
     * Aliases form a View Helper will be added automatically.
     * Example:
     * <code>
     * $view = $this->pixie->view('frontpage');
     * $view->title = "Page title";
     * echo $view->render();
     * </code>
     * @return string Rendered template
     */
    public function render()
    {
        extract($this->_data);
        ob_start();
        include($this->path);
        return ob_get_clean();
    }

}
