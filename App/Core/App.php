<?php

namespace App\Core;

class App
{
    protected $controller = 'App\Controllers\Home';
    protected $method = 'index';
    protected $params = [];
    
    public function __construct()
    {
        $url = $this->parseUrl() ?? ['Home'];
        $controllerName = ucfirst(strtolower($url[0])) . 'Controller';
        if (strtolower($url[0]) === 'home') {
            $controllerName = 'Home';
        }
        if (file_exists("../app/controllers/{$controllerName}.php")) {
            $this->controller = 'App\Controllers\\' . $controllerName;
            unset($url[0]);
        }
        require_once "../app/controllers/" . basename(str_replace('\\', '/', $this->controller)) . ".php";
        $this->controller = new $this->controller;
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
                // echo'does exist ???';
            }
        }
        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
}