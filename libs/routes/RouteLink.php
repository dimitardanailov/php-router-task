<?php

namespace Lib\Route;

class RouteLink {
    
    private $url; 
    private $controller;
    private $action;
    private $requestType;
    
    public function __construct($url, $controller, $action, $requestType) {
        $this->url = $url;
        $this->controller = $controller;
        $this->action = $action;
        $this->requestType = $requestType;
    }
    
    public function getUrl() {
        return $this->url;
    }
    
    public function getController() {
        return $this->controller;
    }
    
    public function getAction() {
        return $this->action;
    }
    
    public function getRequestType() {
        return $this->requestType;
    }
}

?>