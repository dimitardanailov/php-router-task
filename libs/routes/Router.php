<?php
namespace Lib\Route;

class Router {
    
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
        var_dump($_SERVER);
        
        foreach ($this->routes as $route) {
            

            switch ($route->getRequestType()) {
                case \Enum\RequestType::GET:
                    echo 'here';
                    break;
                default:
                    break;
            } 
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

