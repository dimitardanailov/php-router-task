<?php

namespace MVC\Model;

class Article extends \Lib\Database\Database {
    
    public $database;
    
    public function __construct() {
        $this->object_of = "\MVC\Model\Article";
        $this->table = "articles";
        $this->database = new \Lib\Database\Database();
    }

    /**
     * <p>Gives access to the model's database functionality</p>
     * @return object
     */
    public static function db() {
        $instance = new \MVC\Model\Article();
        
        return $instance;
    }
    
    public function getArticleId($id) {
        $this->select('id, title, date, text');
        $this->where('id = ?');
        $this->limit(1);
        $picture = $this->prepare(array('id' => $id));

        if (!empty($picture)) {
            return $picture[0];
        } else {
            return null;
        }
    }
}
?>
