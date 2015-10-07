<?php

namespace MVC\Model;

class Article {
    
    public $database;
    
    public function __construct() {
        parent::__construct();
        
        $this->object_of = "Article";
        $this->table = "articles";
    }

    /**
     * <p>Gives access to the model's database functionality</p>
     * @return object
     */
    public static function db() {
        $instance = new Article();
        $instance->database = new \Lib\Database\Database();
        
        return $instance;
    }
}
?>
