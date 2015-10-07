<?php
namespace Lib\Route;

use \Exception as Exception;

class Router {
    
    const controllerNamespace = '\MVC\Controller\\';

    /**
     *
     * @var string $route
     * <div>Contains a list of routes</div>
     */
    public $routes;
    
    /**
     * Added a new route
     * @param \Lib\Route\RouteLink $routeLink
     */
    public function pushRoute (RouteLink $routeLink) {
        $this->routes[] = $routeLink;
    }
    
    /**
     * 
     */
    public function match() {
        $uri = $this->extractURI();
        $method = $_SERVER['REQUEST_METHOD'];
        
        foreach ($this->routes as $route) {
            
            if ($route->isGetRequest($method)) { 
                $route->updateTemplateURI();
                
                if ($route->URIisMatched($uri)) {
                    $route->generateRequestParams($uri);
                    $this->loadController($route);
                    break;
                }
            } else if ($route->isPostRequest($method)) {
                $this->loadController($route);
            }
        }
    }
    
    /**
     * Method try to load controller.
     * Controller is valid if you exist and we can create a new istance.
     * @param \Lib\Route\RouteLink $routeLink
     * @throws Exception
     */
    private function loadController(RouteLink $routeLink) {
        
        $controllerName = $routeLink->getController() . 'Controller';
        $filename = 'controllers/' . $controllerName . '.php';
        
        // Require Controller
        if (file_exists($filename)) {
            require_once $filename;
            
            $controllerNameWithNamespace = self::controllerNamespace . $controllerName;
            $controller = new $controllerNameWithNamespace();
            
            $this->loadAction($routeLink, $controller);
            
        } else {
            throw new Exception('Controller doesn\'t exist.');
        }
    }
    
    /**
     * Method try load action.
     * Action will be loaded if you exist and can be executed.
     * @param \Lib\Route\RouteLink $routeLink
     * @param type $controller
     * @throws Exception
     */
    private function loadAction(RouteLink $routeLink, $controller) {
        if ($routeLink->actionIsAccessible($controller)) {
            $action = $routeLink->getAction();
            
            // Load Action
            $controller->$action();
            
        } else {
            throw new Exception('Action is not accessible');
        }
    }
    
    /**
     * Extract URI param using DOCUMENT_ROOT.
     * @return type
     * @throws Exception
     */
    private function extractURI() {
        if (isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['REQUEST_URI'])) {
            $appfolder = str_ireplace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__));
            $folders = explode('/', $appfolder);
            $uri = $_SERVER['REQUEST_URI'];

            foreach ($folders as $folder) {
                $uri = str_ireplace($folder, '', $uri);
            }
            
            $uri = str_ireplace('//', '', $uri);
            
            return $uri;
        } else {
            throw new Exception('Invalid request param');
        }
    }
}

