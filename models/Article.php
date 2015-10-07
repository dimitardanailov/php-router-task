<?php

namespace MVC\Model;

class Article extends \MVC\Model\BaseModel {
    
    protected $database;
    
    // Don't forget to fill this array
    protected $fillable = ['title', 'date', 'text'];
    
    // Properties
    public $id;
    public $title;
    public $date;
    public $text;
    public $created_at;
    public $updated_at;
    
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
    
    protected function getProperty($key) {
        return $this->{$key};
    }
    
    protected function setProperty($key, $value) {
        $this->{$key} = trim($value);
    }
    
    public function getArticleId($id) {
        $this->select('id, title, date, text');
        $this->where('id = ?');
        $this->limit(1);
        $article = $this->prepare(array('id' => $id));
        
        if (!empty($article)) {
            return $article[0];
        } else {
            return null;
        }
    }
}
?>
