<?php

namespace MVC\Controller;

class ArticleController {
    
    /**
     * GET /news
     * @return [{id, title, date, text}]
     */
    public function index() {
        $articles = Article::db()->select('id, title, date, text');
        
        var_dump($articles);
    }
    
}

?>
