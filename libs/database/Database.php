<?php

namespace Lib\Database;
/*
 *
 */

class Database {

    /**
     *
     * @var string
     * <p>Contains the name of the table that coresponds to the current model</p>
     */
    protected $table;

    /**
     *
     * @var string
     * <p>Contains the name of the class that coresponds to the current model</p>
     */
    protected $object_of;

    /**
     * The SQL query to be prepared and executed
     *
     * @var object
     */
    protected $_query;

    /**
     * String that holds select condition
     *
     * @var string
     */
    protected $_select = '';

    /**
     * String that hold join condition
     * 
     * @var string
     */
    protected $_join = '';

    /**
     * An array that holds where conditions 'fieldname' => 'value'
     *
     * @var array
     */
    protected $_where = '';

    /**
     * String that hold GROUP BY condition
     * 
     * @var string
     */
    protected $_group = '';

    /**
     * String that hold having condition
     * 
     * @var string
     */
    protected $_having = '';

    /**
     * String that hold ORDER BY condition
     * 
     * @var string
     */
    protected $_order = '';

    /**
     * String that hold LIMIT condition
     * 
     * @var string
     */
    protected $_limit = '';

    /**
     * MySQLi instance
     *
     * @var object
     */
    static $_mysqli;

    /**
     * Create connection
     * @access public
     */

    /**
     * Dynamic type list for table data values
     *
     * @var array
     */
    protected $_paramTypeList;

    public function __construct() {
        if (empty(self::$_mysqli)) {
            self::$_mysqli = new mysqli(HOST, Article, PASSWORD, DATABASE);
            self::$_mysqli->query("SET NAMES " . ENCODING);
            self::$_mysqli->query("SET CHARACTER SET " . ENCODING);

            if (mysqli_connect_errno()) {
                header('HTTP/1.1 500 Not Found');
                print file_get_contents(DocumentRoot . '/public/error/500.html');
                exit;
            }
        }
    }

    /**
     * Function generate select condition statment
     * @access public
     * @example $Database->select('id,name');
     * @param string $values
     */
    public function select($values = '*') {
        $this->_select = 'SELECT ' . $values . ' FROM ' . DATABASE . '.`' . $this->table . '`';
        return $this;
    }

    /**
     * Function generate join condition statment
     * @access public
     * @example $Database->join('INNER JOIN table ON table.fk = this->table.pk');
     * @param string $join
     */
    public function join($join) {
        $this->_join = ' ' . $join;
        return $this;
    }

    /**
     * Function generate WHERE condition statment
     * @access public
     * @example $Database->where('id = ? AND name = ?');
     * @param string $join
     */
    public function where($whereClause) {
        $this->_where = ' WHERE ' . $whereClause;
        return $this;
    }

    /**
     * Function generate GROUP BY condition statment
     * @access public
     * @example $Database->group_by('id,name');
     * @param string $param 
     */
    public function group_by($param) {
        $this->_group = ' GROUP BY ' . $param;
        return $this;
    }

    /**
     * Function generate Having condition statment
     * @access public
     * @param string $param 
     */
    public function having($param) {
        $this->_having = ' HAVING ' . $param;
        return $this;
    }

    /**
     * Function generate Order By condition statment
     * @access public
     * @example $Database->order('id ASC,name DESC');
     * @param string $param 
     */
    public function order($param) {
        $this->_order = ' ORDER BY ' . $param;
        return $this;
    }

    /**
     * Function generate LIMIT condition statment
     * @access public
     * @example $Database->limit('0,30');
     * @param string $param 
     */
    public function limit($param) {
        $this->_limit = ' LIMIT ' . $param;
        return $this;
    }

    /**
     * Insert new record 
     * @param array $tableData Data containing information for inserting into the DB.
     * @return boolean Boolean indicating whether the insert query was completed succesfully.
     */
    public function create($tableData = NULL) {
        $this->_query = 'INSERT into ' . DATABASE . '.`' . $this->table . '`';

        $currentDate = date('Y-m-d H:i:s');

        $tableData['created_at'] = $currentDate;
        $tableData['updated_at'] = $currentDate;

        $keys = array_keys($tableData);
        $values = array_values($tableData);
        $num = count($keys);

        // wrap values in quotes
        foreach ($values as $key => $val) {
            $this->_paramTypeList .= $this->_determineType($val);
        }

        $this->_query .= '(' . implode($keys, ',') . ')';

        $this->_query .= ' VALUES(';
        while ($num !== 0) {
            ($num !== 1) ? $this->_query .= '?, ' : $this->_query .= '?);';
            $num--;
        }

        $stmt = $this->_buildQuery($tableData);

        ($stmt->affected_rows) ? $result = $stmt->insert_id : $result = false;
        return $result;
    }

    /**
     * Update new record 
     * @param array $tableData Data containing information for updating into the DB.
     * @param array $whereParams Data containing information for where params
     * @return boolean Boolean indicating whether the update query was completed succesfully.
     */
    public function update(array $tableData, array $whereParams) {
        $this->_query = 'UPDATE ' . DATABASE . '.`' . $this->table . '` SET ';

        $currentDate = date('Y-m-d H:i:s');

        $tableData['updated_at'] = $currentDate;

        $i = 1;

        foreach ($tableData as $key => $value) {
            // determines what data type the item is, for binding purposes.
            $this->_paramTypeList .= $this->_determineType($value);

            // prepares the reset of the SQL query.
            ($i === count($tableData)) ?
                            $this->_query .= $key . ' = ?' :
                            $this->_query .= $key . ' = ?, ';

            $i++;
        }

        $this->_query .= $this->_where . ';';

        foreach ($whereParams as $key => $val) {
            $tableData[$key] = $val;
            $this->_paramTypeList .= $this->_determineType($val);
        }

        $stmt = $this->_buildQuery($tableData);

        ($stmt->affected_rows) ? $result = true : $result = false;
        return $result;
    }

    /**
     * Delete query.
     *
     * @param string $tableData Data containing information for deleting into the DB.
     * @return boolean Indicates success. 0 or 1.
     */
    public function delete(array $tableData) {
        $this->_query = 'DELETE FROM ' . DATABASE . '.`' . $this->table . '` WHERE ';

        $keys = array_keys($tableData);
        $values = array_values($tableData);
        $num = count($keys);
        $count = 0;

        // wrap values in quotes
        foreach ($values as $key => $val) {
            $this->_paramTypeList .= $this->_determineType($val);

            if ($num === $count + 1) {
                $this->_query .= $keys[$count] . ' = ?';
            } else {
                $this->_query .= $keys[$count] . ' = ? AND ';
            }
            $count++;
        }

        $this->_query .= ';';

        $stmt = $this->_buildQuery($tableData);

        ($stmt->affected_rows) ? $result = true : $result = false;
        return $result;
    }

    /**
     * Delete query.
     * @param string $query sql code. Example: client_id = ' . $_POST['client_id'] . ' AND service_id NOT IN(' . $sentServicesString . ')
     * @return boolean Indicates success. 0 or 1.
     */
    public function deleteRecordByQuery($query) {
        $this->_query = 'DELETE FROM ' . DATABASE . '.`' . $this->table . '` ';
        $this->_query .= $query . ';';

//        echo $this->_query;

        $stmt = $this->_prepareQuery();
        $stmt->execute();
        $this->reset();

        ($stmt->affected_rows) ? $result = true : $result = false;
        return $result;
    }

    /**
     * Pass in a prepare and an array containing the parameters to bind to the prepaird statement.
     *
     * @param array $query Contains a user-provided query.
     * @param array $bindData All variables to bind to the SQL statment.
     * @return array Contains the returned rows from the query.
     */
    public function prepare($bindParams = NULL) {
        $this->_query = $this->genereteSelect();

//        echo $this->_query;

        $stmt = $this->_prepareQuery();

        if (gettype($bindParams) === 'array') {
            $params = array('');  // Create the empty 0 index
            foreach ($bindParams as $prop => $val) {
                $params[0] .= $this->_determineType($val);
                $params[] = &$bindParams[$prop];
            }
            call_user_func_array(array($stmt, 'bind_param'), $params);
        }

        $stmt->execute();
        $this->reset();

        $results = $this->_dynamicBindResults($stmt);
        return $results;
    }

    /**
     * Table Exist
     * 
     * @param string $tableName
     * @return boolean Indicates success. 0 or 1.
     */
    function tableExist($tableName) {
        $exist = false;

        $query = 'SHOW tables WHERE Tables_in_' . DATABASE . ' = "' . $tableName . '"';

        $stmt = self::$_mysqli->prepare($query);

        $stmt->execute();

        $parameters = array();
        $results = array();

        $meta = $stmt->result_metadata();

        while ($field = $meta->fetch_field()) {
            $parameters[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $parameters);

        while ($stmt->fetch()) {
            $object = new $this->object_of();
            foreach ($row as $key => $val) {
                $object->{"$key"} = $val;
            }
            array_push($results, $object);
        }

        if ($results) {
            $exist = true;
        }

        unset($query, $results, $parameters, $stmt);

        return $exist;
    }

    /**
     * Performs a query on the database
     * @return objects 
     */
    function query() {
        $objects = array();

        $this->_query = $this->genereteSelect();

        $result = self::$_mysqli->query($this->_query);

        if ($result) {
            /* fetch object array */
            while ($object = $result->fetch_object($this->object_of)) {
                $objects[] = $object;
            }
            /* free result set */
            $result->close();
        }

        unset($result);

        $this->reset();

        return $objects;
    }

    /**
     * Update Multiple Rows With Different Values and a Single SQL Query.<br/>
     * For more info visit :<br/>http://www.karlrixon.co.uk/articles/sql/update-multiple-rows-with-different-values-and-a-single-sql-query/
     */
    function updateMultiRows($query) {
        return self::$_mysqli->query($query);
    }

    /**
     *
     * <p>Same as find but separates the results in pages</p>
     */
    public function paginate(array $params = NULL, $prepared = true, array $bindParams = NULL, $separateInPages = true) {

        if (!isset($params['page_size'])) {
            $params['page_size'] = Pagination::$config['page_size'];
        }

        if (!isset($params['page_var'])) {
            $params['page_var'] = Pagination::$config['page_var'];
        }

        if ($separateInPages) {
            $links = $this->separateInPages($params['page_size'], $params['page_var'], $prepared, $bindParams);
        }

        $limit = $this->genLimit($params['page_size'], $params['page_var']);
        $this->limit($limit);

        if ($prepared) {
            $objects = $this->prepare($bindParams);
        } else {
            $objects = $this->query();
        }

        if ($separateInPages && isset($objects[0])) {
            $objects[0]->azaret_paginate = $links;
        }

        return $objects;
    }

    /**
     * <p>Calls a previously defined method using has_many, has_one or belongs to methods and returns an array of objects related to the current object.</p>
     * @param String $method the name of the defined method
     * @return Mixed An array of objects or a single object that is related to the current one.
     */
    public function relation($method) {
        $fn = $this->{$method};
        if (function_exists($fn)) {
            return $fn($this);
        } else {
            print "Warning: Unknown method '$method'.";
        }
    }

    /**
     * set autocommit to off 
     */
    function autocommitOff() {
        self::$_mysqli->autocommit(FALSE);
    }

    /**
     * Commit transaction
     */
    function commitTransaction() {
        self::$_mysqli->commit();
    }

    /**
     * rollback transaction
     */
    function commitRollback() {
        self::$_mysqli->rollback();
    }

    /*     * * Protected Methods ** */

    /**
     * <p>Creates a relation ship between the current model and a model tha has many related elements</p>
     * @param String $method the name of the method which will be defined
     * @param array $relationship Contains the elements needet to create a relationship
     * Elements: <br />
     * model - the name of the model thet the current one is related to<br />
     * key - the column name that the current model will connect through<br />
     * foreign_key - the column name in the related model to which the current model will connect to.<br />
     */
    protected function has_many($method, array $relationship) {
        $this->{$method} = create_function('$object', 'if(empty($object->' . $method . '_collection)){' .
                '$object->' . $method . '_collection = ' . $relationship['model'] . '::db()->select()->where("' . $relationship['foreign_key'] . '=?")->prepare(array($object->' . $relationship['key'] . '));' .
                '}' .
                'return $object->' . $method . '_collection;'
        );
    }

    /**
     * <p>Creates a relation ship between the current model and a model tha has one related element</p>
     * @param String $method the name of the method which will be defined
     * @param array $relationship Contains the elements needet to create a relationship
     * Elements: <br />
     * model - the name of the model thet the current one is related to<br />
     * key - the column name that the current model will connect through<br />
     * foreign_key - the column name in the related model to which the current model will connect to.<br />
     */
    protected function has_one($method, array $relationship) {
        $this->{$method} = create_function('$object', 'if(empty($object->' . $method . '_collection)){' .
                '$object->' . $method . '_collection = current(' . $relationship['model'] . '::db()->select()->where("' . $relationship['foreign_key'] . '=?")->limit(1)->prepare(array($object->' . $relationship['key'] . ')));' .
                '}' .
                'return $object->' . $method . '_collection;'
        );
    }

    /**
     * <p>Creates a relation ship between the current model and a model that it belongs to</p>
     * @param String $method the name of the method which will be defined
     * @param array $relationship Contains the elements needet to create a relationship
     * Elements: <br />
     * model - the name of the model thet the current one is related to<br />
     * key - the column name that the current model will connect through<br />
     * foreign_key - the column name in the related model to which the current model will connect to.<br />
     */
    protected function belongs_to($method, array $relationship) {
        $this->{$method} = create_function('$object', 'if(empty($object->' . $method . '_collection)){' .
                '$object->' . $method . '_collection = current(' . $relationship['model'] . '::db()->select()->where("' . $relationship['foreign_key'] . '=?")->limit(1)->prepare(array($object->' . $relationship['key'] . ')));' .
                '}' .
                'return $object->' . $method . '_collection;'
        );
    }

    /*     * * Private Methods ** */

    /**
     * This method is needed for prepared statements. They require
     * the data type of the field to be bound with "i" s", etc.
     * This function takes the input, determines what type it is,
     * and then updates the param_type.
     *
     * @param mixed $item Input to determine the type.
     * @return string The joined parameter types.
     */
    private function _determineType($item) {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';
                break;

            case 'integer':
                return 'i';
                break;

            case 'blob':
                return 'b';
                break;

            case 'double':
                return 'd';
                break;
        }
    }

    /**
     * Build SQL query.
     * 
     * @param array $tableData Should contain an array of data for insert,delete
     */
    private function _buildQuery(array $tableData) {
        $stmt = $this->_prepareQuery();

        $this->_bindParams[0] = $this->_paramTypeList;

        foreach ($tableData as $prop => $val) {
            $this->_bindParams[] = &$tableData[$prop];
        }

        call_user_func_array(array($stmt, 'bind_param'), $this->_bindParams);

        $stmt->execute();

        $this->reset();

        return $stmt;
    }

    /**
     * This helper method takes care of prepared statements' "bind_result method
     * , when the number of variables to pass is unknown.
     *
     * @param object $stmt Equal to the prepared statement object.
     * @return array The results of the SQL fetch.
     */
    private function _dynamicBindResults($stmt) {
        $parameters = array();
        $results = array();

        $meta = $stmt->result_metadata();

        while ($field = $meta->fetch_field()) {
            $parameters[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $parameters);

        while ($stmt->fetch()) {
            $object = new $this->object_of();
            foreach ($row as $key => $val) {

                $object->{"$key"} = $val;
            }
            array_push($results, $object);
        }

        return $results;
    }

    /**
     * Function generate SQL code
     */
    private function genereteSelect() {
        return $this->_select . $this->_join . $this->_where . $this->_group . $this->_having . $this->_order . $this->_limit;
    }

    /**
     * Reset states after an execution
     *
     * @return object Returns the current instance.
     */
    private function reset() {
        $this->_group = '';
        $this->_having = '';
        $this->_join = '';
        $this->_limit = '';
        $this->_order = '';
        $this->_select = '';
        $this->_where = '';
        $this->_bindParams = array('');  // Create the empty 0 index
        unset($this->_query);
        unset($this->_paramTypeList);
    }

    /**
     * Method attempts to prepare the SQL query
     * and throws an error if there was a problem.
     */
    private function _prepareQuery() {
        $stmt = self::$_mysqli->prepare($this->_query);

        //echo $this->_query;
        if (!$stmt) {
            //echo $this->_query;
            //trigger_error("Problem preparing query ($this->_query) ".self::$_mysqli->error, E_USER_ERROR);
            echo 'Problem preparing query';
            exit();
        }
        return $stmt;
    }

    private function separateInPages($page_size, $page_var, $prepared, $bindParams) {
        $query = 'SELECT count(*) as count FROM ' . DATABASE . '.`' . $this->table . '`' . $this->_join . $this->_where . $this->_group . $this->_having;

        if ($prepared === true) {
            $stmt = self::$_mysqli->prepare($query);
            if (!$stmt) {
                //trigger_error("Problem preparing query ($query) ".self::$_mysqli->error, E_USER_ERROR);
                echo 'Problem preparing query';
                exit();
            }

            if (gettype($bindParams) === 'array') {
                $params = array('');  // Create the empty 0 index
                foreach ($bindParams as $prop => $val) {
                    $params[0] .= $this->_determineType($val);
                    $params[] = &$bindParams[$prop];
                }
                call_user_func_array(array($stmt, 'bind_param'), $params);
            }

            $stmt->execute();

            $result = $this->_dynamicBindResults($stmt);

            $size = $result[0]->count;
        } else {
            $result = self::$_mysqli->query($query);

            $row = $result->fetch_array();

            $size = $row[0];

            $result->close();

            unset($row);
        }

        unset($query);

        if ($size > 0) {

            $title = null;

            if (!empty(Pagination::$config['title'])) {
                $title = 'title="' . Pagination::$config['title'] . ' - ' . Locales::$text['page'] . ' ';
            }

            $links = '<div class="' . Pagination::$config['css_class'] . '">';
            $count = ceil($size / $page_size);

            if (isset($_REQUEST[$page_var]))
                $page = $_REQUEST[$page_var];
            else
                $page = 1;

            $argum = null;
            if ($_GET)
                foreach ($_GET as $key => $val) {
                    if ($key != $page_var) {
                        if (is_array($val)) {
                            foreach ($val as $array_value) {
                                $argum.='&' . $key . '[]=' . $array_value;
                            }
                        } else {
                            $argum.='&' . $key . '=' . $val;
                        }
                    }
                }

            //REQUEST
            if (Pagination::$config['use_get'] === false) {
                $flag = true;

                if (!isset($_REQUEST[$page_var])) {
                    $_REQUEST[$page_var] = 1;
                    $flag = false;
                }

                $url = $_SERVER['REQUEST_URI'];
                $linksInfo = explode('/', $url);

                if ($flag == true) {
                    unset($linksInfo[sizeof($linksInfo) - 1]);
                }
                $url = implode('/', $linksInfo) . '/';

                $arguments = null;
                if (!empty($argum)) {
                    $arguments = '?' . ltrim($argum, '&');
                }

                $previous = ($page - 1);
                $next = ($page + 1);

                if ($page > 1)
                    $links.='<a class="pagination_previous" href="' . $url . '1' . $arguments . '" ' . $title . '1">' . Pagination::$config['first'] . '</a> 
                                    <a class="pagination_previous" href="' . $url . $previous . $arguments . '" ' . $title . $previous . '">' . Pagination::$config['previous'] . '</a>';

                for ($i = ($page - 2); $i <= $count && $i <= $page + 3; $i++) {
                    if ($i > 0) {
                        if ($i != $page)
                            $links.=' <a class="numbers" href="' . $url . $i . $arguments . '" ' . $title . $i . '">' . $i . '</a> ';
                        else
                            $links.=' <b class="active_page">' . $i . '</b>';
                    }
                }
                if ($page < $count)
                    $links.='
                    <a class="pagination_next" href="' . $url . ($page + 1) . $arguments . '" ' . $title . $next . '">' . Pagination::$config['next'] . '</a> 
                    <a class="pagination_next" href="' . $url . $count . $arguments . '" ' . $title . $count . '">' . Pagination::$config['last'] . '</a>';
            }
            //GET
            else {
                $previous = ($page - 1);
                $next = ($page + 1);

                if ($page > 1)
                    $links.='<a class="pagination_previous" 
                                        href="?' . $page_var . '=1' . $argum . '" 
                                        ' . $title . '1">' . Pagination::$config['first'] . '</a> 
                                     <a class="pagination_previous" 
                                        href="?' . $page_var . '=' . ($page - 1) . $argum . '" 
                                        ' . $title . $previous . '">' . Pagination::$config['previous'] . '</a>';

                for ($i = ($page - 2); $i <= $count && $i <= $page + 3; $i++) {
                    if ($i > 0) {
                        if ($i != $page)
                            $links.=' <a class="numbers" href="?' . $page_var . '=' . $i . $argum . '" ' . $title . $i . '">' . $i . '</a> ';
                        else
                            $links.=' <b class="active_page">' . $i . '</b>';
                    }
                }
                if ($page < $count)
                    $links.='<a class="pagination_next" href="?' . $page_var . '=' . ($page + 1) . $argum . '" ' . $title . $next . '">' . Pagination::$config['next'] . '</a>  
                                          <a class="pagination_next" href="?' . $page_var . '=' . $count . $argum . '" ' . $title . $count . '">' . Pagination::$config['last'] . '</a>';
            }

            $links.='</div>';
            return $links;
        }
        return null;
    }

    private function genLimit($page_size, $page_var) {
        if (isset($_REQUEST[$page_var])) {
            $_REQUEST[$page_var] = (int) $_REQUEST[$page_var];
        } else {
            $_REQUEST[$page_var] = 1;
        }

        $_REQUEST[$page_var] = (int) $_REQUEST[$page_var];
        if ($_REQUEST[$page_var])
            $page = $_REQUEST[$page_var];
        else
            $page = 1;
        $from = (($page * $page_size) - $page_size);
        return $from . ',' . $page_size;
    }

}

?>