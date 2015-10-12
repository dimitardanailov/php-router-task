<?php

namespace Lib\JSON;

use \stdClass;

class JsonHelper 
{
    function jsonInputReader($convertedToAssocArray = false) 
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, $convertedToAssocArray);
        
        return $data;
    }
    
    function responseDefaultError()
    {
        $error = $this->defaultError();
        $this->responseJsonMessage($error);
    }
    
    function defaultError()
    {
        $error = $this->generateError(\Enum\ResponseError::INVALID_REQUEST, i18n::$texts['invalidData']);
        
        return $error;
    }
    
    function generateError($HTTPCODE, $message)
    {
        $error = new stdClass();
        $error->HTTPCODE = $HTTPCODE;
        $error->message = $message;
        
        return $error;
    }
    
    function responseCustomError($HTTPCODE, $message) {
        $message = $this->generateError($HTTPCODE, $message);
        
        $this->responseJsonMessage($message);
    }
    
    function generateSuccessCode()
    {
        $message = new stdClass();
        $message->HTTPCODE = 200;
        
        return $message;
    }
    
    function responseJsonMessageByKeyAndValues($key, $values) {
        $message = $this->generateSuccessCode();
        $message->{$key} = $values;
        
        $this->responseJsonMessage($message);
    }
    
    function responseJsonMessage($message)
    {
        header('HTTP/1.1 ' . $message->HTTPCODE);
        header('Content-type: application/json');
        
        echo json_encode($message);
    }
}

?>