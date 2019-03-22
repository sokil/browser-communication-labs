<?php

namespace Sokil\Slim;

class View extends \Slim\View
{
    private $_layout;
    
    private $_layoutName = 'default';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_layout = new \Slim\View;
    }
    
    public function setLayout($name)
    {
        $this->_layoutName = $name;
        
        return $this;
    }
    
    public function setTemplatesDirectory($dir)
    {
        parent::setTemplatesDirectory($dir);
        
        // modify layouts dir
        $this->_layout->setTemplatesDirectory($this->getTemplatesDirectory() . '/layouts');
    }
    
    public function fetch($template)
    {
        $this->_layout->setData(array(
            'content'   => parent::fetch($template),
        ));
        
        return $this->_layout->fetch($this->_layoutName . '.php');
    }
}