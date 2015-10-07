<?php

namespace Lib\Route;

use Enums\Enum as Enum;

class RouteLink {
    
    private $url; 
    private $controller;
    private $action;
    private $requestType;
    private $templateURI;
    
    public function __construct($url, $controller, $action, $requestType) {
        $this->url = $url;
        $this->templateURI = $this->url;
        $this->controller = $controller;
        $this->action = $action;
        $this->requestType = $requestType;
    }
    
    public function getUrl() {
        return $this->url;
    }
    
    public function getTemplateURI() {
        return $this->templateURI;
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
    
    /**
     * Replace URI params with \Lib\Route params
     */
    public function updateTemplateURI() {
        $this->templateURI = preg_replace('/:[^\/]+/i', '[^\/]+', $this->getTemplateURI());
    }
    
    public function URIisMatched($uri) {
        return $this->getUrl() == $uri . '/' || preg_match('@^' . $this->getTemplateURI() . '(\/|)$@i', $uri);
    }
    
    public function isGetRequest($method) {
        return ($method == Enum\RequestType::GET && ($this->getRequestType() == Enum\RequestType::GET));
    }
    
    public function isPostRequest($method) {
        $requestIsPost = (isset($_REQUEST['__method']) && $_REQUEST['__method'] == Enum\RequestType::POST);
        $methodIsPost = $method == Enum\RequestType::POST;
        $routeTypeIsPost = $this->getRequestType() == Enum\RequestType::POST;
        
        return ($methodIsPost && $requestIsPost && $routeTypeIsPost);
    }
    
    /**
     * Action is accessible and is not protected, private and etc.
     * @param type $controller
     * @return type
     */
    public function actionIsAccessible($controller) {
        $methodExists = method_exists($controller, $this->getAction());
        $isCallable = is_callable(array($controller, $this->getAction()));
        
        return ($methodExists && $isCallable);
    }
    
    /**
     * Generate $_REQUEST params.
     * We will explode real URI address and template address and match: Where sub parts are not equals.
     * @param type $uri
     */
    public function generateRequestParams($uri) {
        $URIParams = explode('/', $uri);
        $templateURIParams = explode('/', $this->getUrl());
      
        foreach ($URIParams as $key => $URIParam) {
            if (isset($templateURIParams[$key])) {
                if ($templateURIParams[$key] != $URIParam) {
                    $this->generateRequestParam($templateURIParams[$key], $URIParam);
                }
            }
        }
    }
    
    /**
     * Generate $_REQUEST params
     * @param type $templateURIParam
     * @param type $URIParam
     */
    public function generateRequestParam($templateURIParam, $URIParam) {
        $templateURIParam = str_replace(':', '', $templateURIParam);
        
        $_REQUEST[$templateURIParam] = $URIParam;
    }
}

?>