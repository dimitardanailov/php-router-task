<?php

use Enums\Enum as Enum;

class RouteLinkTest extends PHPUnit_Framework_TestCase
{
    public function testPositiveRequestTypeIsPutOrDeleteRequest()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/:id', 'Article', 'update', Enum\RequestType::PUT);
        
        // Assert
        $this->assertEquals(true, $routeLink->requestTypeIsPutOrDeleteRequest());
    }
    
    public function testNagitiveRequestTypeIsPutOrDeleteRequest()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/:id', 'Article', 'update', Enum\RequestType::GET);
        
        // Assert
        $this->assertEquals(false, $routeLink->requestTypeIsPutOrDeleteRequest());
    }
    
    public function testPositiveURIisMatched()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('users/:id/comments/:commentId', 'User', 'getUserComments', Enum\RequestType::GET);
        $routeLink->updateTemplateURI();
        $URI = 'users/5/comments/12';
        
        // Assert
        $this->assertEquals(true, $routeLink->URIisMatched($URI));
    }
    
    public function testNagitiveURIisMatched()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('users/:id/comments/:commentId/:title', 'User', 'getUserComments', Enum\RequestType::GET);
        $routeLink->updateTemplateURI();
        $URI = 'users/5/comments/12';
        
        // Assert
        $this->assertEquals(false, $routeLink->URIisMatched($URI));
    }
    
    public function testPositiveIsGetRequest()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('users/:id/comments/:commentId/:title', 'User', 'getUserComments', Enum\RequestType::GET);
        $method = 'GET';
        
        // Assert
        $this->assertEquals(true, $routeLink->isGetRequest($method));
    }
    
    public function testNegativeIsGetRequest()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('users/:id/comments/:commentId/:title', 'User', 'getUserComments', Enum\RequestType::POST);
        $method = 'GET';
        
        // Assert
        $this->assertEquals(false, $routeLink->isGetRequest($method));
    }
    
    public function testPositiveIsValidPostRequest()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/', 'Aricles', 'create', Enum\RequestType::POST);
        $method = 'POST';
        $_REQUEST['__method'] = 'POST';
        
        // Assert
        $this->assertEquals(true, $routeLink->isValidRequest($method, Enum\RequestType::POST));
    }
    
    public function testPositiveIsValidPutRequest()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/:id', 'Aricles', 'update', Enum\RequestType::PUT);
        $method = 'POST';
        $_REQUEST['__method'] = 'PUT';
        
        // Assert
        $this->assertEquals(true, $routeLink->isValidRequest($method, Enum\RequestType::PUT));
    }
    
    public function testNegativeIsValidPostRequestWithoutPostParams()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/', 'Aricles', 'create', Enum\RequestType::POST);
        $method = 'POST';
        
        // Assert
        $this->assertEquals(false, $routeLink->isValidRequest($method, Enum\RequestType::POST));
    }
    
    public function testNegativeIsValidPostRequest()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/', 'Aricles', 'create', Enum\RequestType::GET);
        $method = 'POST';
        $_REQUEST['__method'] = 'POST';
        
        // Assert
        $this->assertEquals(false, $routeLink->isValidRequest($method, Enum\RequestType::POST));
    }
    
    public function testPositiveActionIsAccessible()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/', 'Aricles', 'index', Enum\RequestType::GET);
        $controller = new \MVC\Controller\ArticleController();
        
        // Assert
        $this->assertEquals(true, $routeLink->actionIsAccessible($controller));
    }
    
    public function testNegativeActionIsAccessible()
    {
        // Arrange
        $routeLink = new Lib\Route\RouteLink('news/', 'Aricles', 'foo', Enum\RequestType::GET);
        $controller = new \MVC\Controller\ArticleController();
        
        // Assert
        $this->assertEquals(false, $routeLink->actionIsAccessible($controller));
    }
}

?>