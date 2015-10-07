<?php
namespace MVC\Controller;

use Enums\Enum as Enum;
use \MVC\Model as Model;
use \Lib\JSON;

class ArticleController {
    
    public $jsonHelper;
    public $model;


    public function __construct() {
        $this->jsonHelper = new \Lib\JSON\JsonHelper();
        $this->model = new Model\Article();
    }
    
    /**
     * GET /news
     * @return [{id, title, date, text}]
     */
    public function index() {
        $articles = $this->model->select('id, title, date, text')->prepare();
        
        $this->jsonHelper->responseJsonMessageByKeyAndValues('articles', $articles);
    }
    
    public function show() {        
        if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            $article = $this->model->getArticleId($id);
            
            if (!is_null($article)) {
                $this->jsonHelper->responseJsonMessageByKeyAndValues('article', $article);
            } else {
                $this->jsonHelper->responseCustomError(Enum\ResponseError::RECORD_NOT_EXIST, 'Article doesn\'t exist');
            }            
        } else {
            $this->jsonHelper->responseCustomError(Enum\ResponseError::INVALID_REQUEST, 'You need to have :id $_REQUEST param');
        }        
    }
    
    public function create() {
        $this->model->fillModelByArray($_REQUEST);
        $id = $this->model->insertRecord();
        
        $this->jsonHelper->responseJsonMessageByKeyAndValues('article_id', $id);
    }
}

?>
