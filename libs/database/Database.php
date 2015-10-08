<?php

/*
 *
 *
 * Copyright (c) 2010 158, Ltd.
 * All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification are not permitted.
 *
 * Neither the name of 158, Ltd. or the names of contributors
 * may be used to endorse or promote products derived from this software
 * without specific prior written permission.
 *
 * This software is provided "AS IS," without a warranty of any kind. ALL
 * EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING
 * ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED. 158 AND ITS LICENSORS
 * SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF
 * USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES. IN NO
 * EVENT WILL 158 OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE, PROFIT
 * OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL, INCIDENTAL OR
 * PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE THEORY OF
 * LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE SOFTWARE, EVEN
 * IF 158 HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.
 *
 * Any violation of the copyright rules above will be punished by the lay.
 */

namespace Lib\Database;

/*
 *
*/
class Database {
    /**
     * Used for binding parameters with prepared queries
     * @var array 
     */
    protected $_bindParams;
    /**
     * Saves the methods that represent the relation between models
     * @var array 
     */
    protected $relations = array();
    
    /**
     *
     * @var string
     * <p>Contains the name of the table that coresponds to the current model</p>
     */
    protected $table;
    /**
     *
     * @var string
     * <p>Contains the name of the class that coresponds to the current model. The default is stdClass</p>
     */
    protected $object_of = 'stdClass';

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
     * String that holds the FROM clause
     * @var string 
     */
    protected $_from = '';

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
     * Access to a validation helper class
     * @var type 
     */
    private $validation;
    
    /**
     * Dynamic type list for table data values
     *
     * @var array
     */
    protected $_paramTypeList = null;
    
    /**
     * Holds the strict types of database attributes
     * @var array 
     */
    private $__dataAttributes = array();

    /**
     * MySQLi instance
     *
     * @var object
     */
    public static $_mysqli;

    /**
     * Create connection configuration
     * <pre>
     * Database::$config = array(
     * 'development' => array(
     *   'host' => 'localhost',
     *   'user' => 'root',
     *   'password' => 'passwd',
     *   'database' => 'db',
     *   'encoding' => 'utf8',
     * ),

     * 'production' => array(
     *   'host' => 'localhost',
     *   'user' => 'root',
     *   'password' => 'passwd',
     *   'database' => 'db',
     *   'encoding' => 'utf8',
     * ),
     * );
     * </pre>
     * @access public
     */

    public static $config = array();

    public function __construct()
    {
        // Define database access
        if(empty(self::$_mysqli))
        {
            self::$_mysqli = new \MySQLi(
                    self::$config[ENV]['host'],
                    self::$config[ENV]['user'],
                    self::$config[ENV]['password'],
                    self::$config[ENV]['database']);

            self::$_mysqli->query("SET NAMES " . self::$config[ENV]['encoding']);
            self::$_mysqli->query("SET CHARACTER SET " . self::$config[ENV]['encoding']);

            if(mysqli_connect_errno())
            {
                $application = new Application();
                $application->internalError();
                exit(1);
            }
        }
        
        // Set the FROM clause to use the current model's table name
        $this->from($this->table);
        
        // Define helper classess
        $this->validation = new DataValidation($this);
    }
    
    /**
     * When this property is set the system will automaticaly cast it to the givet data type.
     * @param string $property Property name
     * @param string $type Data Type e.g. (bool, int, float, double, object, array, string)
     */
    protected function attributes(array $attributes) {
        if(empty($this->__dataAttributes)) {
            $this->__dataAttributes = array();
        }
        
        $this->__dataAttributes = array_merge($this->__dataAttributes, $attributes);
    }
    
    public function __set($attribute, $value) {
        if(!empty($this->__dataAttributes[$attribute])) {
            switch($this->__dataAttributes[$attribute]) {
                case 'bool':
                case 'boolean':
                    $value = (bool)$value;
                    break;
                case 'int':
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'float':
                    $value = (float)$value;
                    break;
                case 'double':
                    $value = (double)$value;
                    break;
                case 'obj':
                case 'object':
                    $value = (object)$value;
                    break;
                case 'arr':
                case 'array':
                    $value = (array)$value;
                    break;
                case 'str':
                case 'string':
                    $value = (string)$value;
                    break;
                default:
                    trigger_error('Unknown data type.', E_USER_WARNING);
                    break;
            }
        }
        $this->{$attribute} = $value;
    }

    /**
     * Loads the object's attributes from a given array
     * @param array $attributes Array of attributes to load into the object
     * @return Model the object of the current model
     */
    public function init(array $attributes) {
        foreach($attributes as $attribute => $value) {
            $this->{$attribute} = $value;
        }

        return $this;
    }

    /**
     * Function generate select condition statment
     * @access public
     * @example $Database->select('id,name');
     * @param string $values
     */
    public function select($values = '*')
    {        
        $this->_select = 'SELECT '.$values;
        $this->from($this->table);
        return $this;
    }
    
    /**
     * Generates FROM clause
     * @access public
     * @example $mode->from('categories');
     * @param string $value
     * @return Database 
     */
    public function from($value) {
        $this->_from = ' FROM '.$value;
        return $this;
    }

    /**
     * Function generate join condition statment
     * @access public
     * @example $Database->join('INNER JOIN table ON table.fk = this->table.pk');
     * @param string $join
     */
    public function join($join)
    {
        $this->_join = ' '.$join;
        return $this;
    }

    /**
     * Function generate WHERE condition statment
     * @access public
     * @example $Database->where('id = ? AND name = ?');
     * @param string $join
     */
    public function where($whereClause)
    {
        $this->_where = ' WHERE '.$whereClause;
        return $this;
    }

    /**
     * Function generate GROUP BY condition statment
     * @access public
     * @example $Database->group_by('id,name');
     * @param string $param
     */
    public function group_by($param)
    {
        $this->_group = ' GROUP BY '.$param;
        return $this;
    }

    /**
     * Function generate Having condition statment
     * @access public
     * @param string $param
     */
    public function having($param)
    {
        $this->_having = ' HAVING '.$param;
        return $this;
    }

    /**
     * Function generate Order By condition statment
     * @access public
     * @example $Database->order('id ASC,name DESC');
     * @param string $param
     */
    public function order($param)
    {
        $this->_order = ' ORDER BY '.$param;
        return $this;
    }

    /**
     * Function generate LIMIT condition statment
     * @access public
     * @example $Database->limit('0,30');
     * @param string $param
     */
    public function limit($param)
    {
        $this->_limit = ' LIMIT '.$param;
        return $this;
    }
    
    /**
     * Gives access to the validation property
     * @return DataValidation the validation object 
     */
    public function validation() {
        return $this->validation;
    }

    /**
     * Insert new record
     * @param array $tableData Data containing information for inserting into the DB.
     * @return mixed retuns false if the insert was not completed or the id of the inserted record on success.
     */
    public function create($tableData = NULL)
    {
        $this->_query = 'INSERT into `'.self::$config[ENV]['database'].'`.`'.$this->table.'`';

        $currentDate = date('Y-m-d H:i:s');

        $tableData['created_at'] = $currentDate;
        $tableData['updated_at'] = $currentDate;

        $keys = array_keys($tableData);
        $values = array_values($tableData);
        $num = count($keys);

        // wrap values in quotes
        $this->_paramTypeList = null;
        foreach ($values as $key => $val)
        {
            $this->_paramTypeList .= $this->_determineType($val);
        }

        $this->_query .= '('.implode($keys,',').')';

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
     * @return boolean Boolean indicating whether the insert query was completed succesfully.
     */
    public function update(array $tableData, array $whereParams)
    {
        $this->_query = 'UPDATE `'.self::$config[ENV]['database'].'`.`'.$this->table.'` SET ';

        $currentDate = date('Y-m-d H:i:s');

        $tableData['updated_at'] = $currentDate;

        $i = 1;
        
        $this->_paramTypeList = null;
        foreach ($tableData as $key => $value)
        {
            // determines what data type the item is, for binding purposes.
            $this->_paramTypeList .= $this->_determineType($value);

            // prepares the reset of the SQL query.
            ($i === count($tableData)) ?
                    $this->_query .= $key . ' = ?':
                    $this->_query .= $key . ' = ?, ';

            $i++;
        }

        $this->_query .= $this->_where.';';

        foreach ($whereParams as $key => $val)
        {
            $tableData[$key] = $val;
            $this->_paramTypeList .= $this->_determineType($val);
        }
        
        $stmt = $this->_buildQuery($tableData);

        ($stmt->affected_rows) ? $result = true : $result = false;
	return $result;
    }

    /**
     * Saves an object to the database. If the object has a set id attribute,
     * the system will update the existing record else it will create a new record.
     * @return Model the object of the current model
     */
    /**
     * Saves an object to the database. If the object has a set id attribute,
     * the system will update the existing record else it will create a new record.
     * @param bool $validate (Optional) Default value is true. Indicates whether to use data validations.
     * @return \Database 
     */
    public function save($validate = true) {
        //get only public properties
        $getProperties = create_function('$obj', 'return get_object_vars($obj);');

        //Before save hook
        if (method_exists($this, 'beforeSave')) {
            $this->beforeSave();
        }

        if(!empty($this->id)) {
            //Before update hook
            if(method_exists($this, 'beforeUpdate')) {
                $this->beforeUpdate();
            }
            
            //validate data
            if($validate) {
                if($this->validation->hasErrors()) {
                    return $this; // end method execution if the data is not valid
                }
            }
            
            //data to be saved
            $data = $getProperties($this);

            $id = $data['id'];

            if(isset($data['created_at'])) {
                unset ($data['created_at']);
            }
            unset ($data['id']);

            $this->where('id = ?')
                    ->update($data, array($id));
        } else {
            //before create hook
            if(method_exists($this, 'beforeCreate')) {
                $this->beforeCreate();
            }
            
            //validate data
            if($validate) {
                if($this->validation->hasErrors()) {
                    return $this; // end method if the data is not valid
                }
            }
            
            //data to be saved
            $data = $getProperties($this);

            $id = $this->create($data);
            $currentDate = date('Y-m-d H:i:s');
            $this->id = $id;
            $this->created_at = $currentDate;
            $this->updated_at = $currentDate;
        }
        
        unset ($getProperties);
        return $this;
    }

    /**
     * Delete query.
     *
     * @param string $tableData Data containing information for deleting into the DB.
     * @param string sign. Default value is =. You can use >, <, IS NULL, etc.
     * @return boolean Indicates success. 0 or 1.
     */
    public function delete(array $tableData, $sign = '=')
    {
        $this->_query = 'DELETE FROM `'.self::$config[ENV]['database'].'`.`'.$this->table.'` WHERE ';

        $keys = array_keys($tableData);
        $values = array_values($tableData);
        $num = count($keys);
        $count = 0;

        // wrap values in quotes
        $this->_paramTypeList = null;
        foreach ($values as $key => $val)
        {
            $this->_paramTypeList .= $this->_determineType($val);

            if($num === $count+1)
            {
                $this->_query .= $keys[$count].' ' . $sign . ' ?';
            }
            else
            {
                $this->_query .= $keys[$count].' ' . $sign . ' ? AND ';
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
     * @param array $tableData Data containing information for deleting into the DB.
     * @param array $signClauses. Example (!=, =, >, <)
     * @return boolean Indicates success. 0 or 1.
     */
    public function deleteWithDifferentClauses(array $tableData, array $signClauses) {
        $this->_query = 'DELETE FROM `' . self::$config[ENV]['database'] . '`.`' . $this->table . '` WHERE ';

        $keys = array_keys($tableData);
        $keySigns = array_values($signClauses);
        $values = array_values($tableData);
        $num = count($keys);
        $count = 0;

        // wrap values in quotes
        $this->_paramTypeList = null;
        foreach ($values as $key => $val) {
            $this->_paramTypeList .= $this->_determineType($val);

            if ($num === $count + 1) {
                $this->_query .= $keys[$count] . ' ' . $keySigns[$count] . ' ?';
            } else {
                $this->_query .= $keys[$count] . ' ' . $keySigns[$count] . ' ? AND ';
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
        $this->_query = 'DELETE FROM ' . self::$config[ENV]['database'] . '.`' . $this->table . '` WHERE ';
        $this->_query .= $query . ';';
        
        //        echo $this->_query;
        
        $stmt = $this->_prepareQuery();
        $stmt->execute();
        $this->reset();
        
        ($stmt->affected_rows) ? $result = true : $result = false;
        return $result;
    }

    /**
     * Checks if there is a record id set as an object attribute and if it's true
     * deletes the record.
     * @return Model object of the current model
     */
    public function destroy() {
        if(isset($this->id)) {

            //Before destroy hook
            if (method_exists($this, 'beforeDestroy')) {
                $this->beforeDestroy();
            }

            $this->delete(array(
                'id' => $this->id
            ));
        }

        return $this;
    }

    /**
     * Pass in a prepare and an array containing the parameters to bind to the prepaird statement.
     *
     * @param array $bindParams All variables to bind to the SQL statment.
     * @return array Contains the returned rows from the query.
     */
    public function prepare($bindParams = NULL)
    {
        $this->_query = $this->genereteSelect();
        
        //echo $this->_query;

        $results = $this->_prepareRoutine($bindParams);
        return $results;
    }
    
    /**
     * Get enum values from database
     * @link http://stackoverflow.com/questions/2350052/how-can-i-get-enum-possible-values-in-a-mysql-database
     * @param string $field enum field
     * @return array enum values from database
     */
    public function getEnumValues($field) 
    {
        $this->_query = 'SHOW COLUMNS FROM '.DATABASE.'.`'.$this->table.'` WHERE Field = "' . $field . '"';
        //echo $this->_query;
        $type = self::$_mysqli->query($this->_query)->fetch_object()->Type;
        $matches = null;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enums = array();
        if (isset($matches[1]))
        {
            $enumfields = explode(',', $matches[1]);
            foreach($enumfields as $enumfield)
            {
                $enums[] = trim($enumfield, "'");
            }    
        }
        
        return $enums;
    }
    
    /**
     * Checks if a database record exists
     * @param array $bindParams All variables to bind to the SQL statment.
     * @return boolean 
     */
    public function exists($bindParams = null) {
        $object = current($this->limit(1)
                ->prepare($bindParams));
        
        if($object) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Gets the record count
     * @param array $bindParams All variables to bind to the SQL statment.
     * @return int The number of matching records 
     */
    public function count($bindParams = null) {
        $this->_query = $this->genereteSelect();
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
        
        $stmt->store_result();
        
        $rowCount = $stmt->num_rows;
        
        $stmt->close();
        
        return $rowCount;
    }

    /**
     * Executes a custom query
     * @param string $query The query to execute
     * @param array $bindParams The parameters to bind to the query
     * @return array Returns a list of objects for the corresponding model
     */
    public function customPreparedQuery($query, $bindParams = null) {
        $this->_query = $query;

        $results = $this->_prepareRoutine($bindParams);
        return $results;
    }

    /**
     * Performs a query on the database
     * @return objects
     */
    function query()
    {
        $this->_query = $this->genereteSelect();

        $objects = $this->_queryRoutine();
        return $objects;
    }

    /**
     * Performs a custom query on the database
     * @return objects
     */
    function customQuery($query) {
        $this->_query = $query;

        $objects = $this->_queryRoutine();
        return $objects;
    }

    /**
     * <p>Same as find but separates the results in pages</p> <br />
     * Uses the Pagination and PaginationCollection classes located in libs/common
     * @return PaginationCollection
     */
    public function paginate($bindParams = null, array $config = array()) {
        $pagination = new Pagination($this);
        return $pagination->paginate($bindParams, $config);
    }

    /**
     * <p>Calls a previously defined method using has_many, has_one or belongs to methods and returns an array of objects related to the current object.</p>
     * @param String $method the name of the defined method
     * @return Mixed An array of objects or a single object that is related to the current one.
     */
    public function relation($method){
        $fn = $this->relations[$method];

        if(function_exists($fn)){
            return $fn($this);
        }else{
            trigger_error("Unknown relation method '$method'.", E_USER_ERROR);
        }
    }

    /**
     * set autocommit to off
     */
    function autocommitOff()
    {
        self::$_mysqli->autocommit(FALSE);
    }

    /**
     * Commit transaction
     */
    function commitTransaction()
    {
        self::$_mysqli->commit();
    }

    /**
     * rollback transaction
     */
    function commitRollback()
    {
        self::$_mysqli->rollback();
    }

    /*** Protected Methods ***/

    /**
     * <p>Creates a relation ship between the current model and a model tha has many related elements</p>
     * @param String $method the name of the method which will be defined
     * @param array $relationship Contains the elements needet to create a relationship
     * Elements: <br />
     * model - the name of the model thet the current one is related to<br />
     * key - the column name that the current model will connect through<br />
     * foreign_key - the column name in the related model to which the current model will connect to.<br />
     */
    protected function hasMany($method, array $relationship){
        // Cleanup before setting relations.
        if(isset($this->relations[$method])) {
            unset($this->relations[$method]);
        }
        
        $this->relations[$method] = create_function('$object',
                'if(empty($object->'.$method.'_collection)){'.
                    '$object->'.$method.'_collection = '.$relationship['model'].'::db()->select()->where("'.$relationship['foreign_key'].'=?")->prepare(array($object->'.$relationship['key'].'));'.
                '}'.
                'return $object->'.$method.'_collection;'
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
    protected function hasOne($method, array $relationship){
        // Cleanup before setting relations.
        if(isset($this->relations[$method])) {
            unset($this->relations[$method]);
        }
        
        $this->relations[$method] = create_function('$object',
                'if(empty($object->'.$method.'_collection)){'.
                    '$object->'.$method.'_collection = current('.$relationship['model'].'::db()->select()->where("'.$relationship['foreign_key'].'=?")->limit(1)->prepare(array($object->'.$relationship['key'].')));'.
                '}'.
                'return $object->'.$method.'_collection;'
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
    protected function belongsTo($method, array $relationship){
        // Cleanup before setting relations.
        if(isset($this->relations[$method])) {
            unset($this->relations[$method]);
        }
        
        $this->relations[$method] = create_function('$object',
                'if(empty($object->'.$method.'_collection)){'.
                    '$object->'.$method.'_collection = current('.$relationship['model'].'::db()->select()->where("'.$relationship['foreign_key'].'=?")->limit(1)->prepare(array($object->'.$relationship['key'].')));'.
                '}'.
                'return $object->'.$method.'_collection;'
                );
    }




    /*** Private Methods ***/

    /**
     * This method is needed for prepared statements. They require
     * the data type of the field to be bound with "i" s", etc.
     * This function takes the input, determines what type it is,
     * and then updates the param_type.
     *
     * @param mixed $item Input to determine the type.
     * @return string The joined parameter types.
     */
    private function _determineType($item)
    {
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
    private function _buildQuery(array $tableData)
    {
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
    private function _dynamicBindResults($stmt)
    {
            $parameters = array();
            $results = array();

            $meta = $stmt->result_metadata();

            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }

            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch())
            {
               $object = new $this->object_of();
               
               foreach ($row as $key => $value)
               {
                   // $object->{"$key"} = $val;
                   $object->setProperty($key, $value);
               }
               
               array_push($results, $object);
            }

            return $results;
    }

    /**
     * Function generate SQL code
     */
    private function genereteSelect()
    {
        return $this->_select.$this->_from.$this->_join.$this->_where.$this->_group.$this->_having.$this->_order.$this->_limit;
    }

    /**
     * Reset states after an execution
     *
     * @return object Returns the current instance.
     */
    private function reset()
    {
        $this->_group = '';
        $this->_having = '';
        $this->_join = '';
        $this->_limit = '';
        $this->_order = '';
        $this->_select = '';
        $this->_where = '';
        $this->from($this->table);
        $this->_bindParams = array('');		// Create the empty 0 index
        unset($this->_query);
        unset($this->_paramTypeList);
    }

    /**
     * Method attempts to prepare the SQL query
     * and throws an error if there was a problem.
     */
    private function _prepareQuery()
    {
        $stmt = self::$_mysqli->prepare($this->_query);
        
        if(!$stmt)
        {
            trigger_error("Cannot preparing query: ".self::$_mysqli->error, E_USER_WARNING);
        }
        
        return $stmt;
    }

    /**
     * The routine for creating a prepared query.
     *
     * @param array $bindData All variables to bind to the SQL statment.
     * @return array Contains the returned rows from the query.
     */
    private function _prepareRoutine($bindParams = null) {
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
     * Executes the normal query routine needed for fetching objects
     * @return array Array of objects for the coresponding model
     */
    private function _queryRoutine() {
        $result = self::$_mysqli->query($this->_query);

        $type = gettype($result);
        switch ($type) {
            case 'boolean' :
                return $result;
            case 'object':
                $objects = array();
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
                break;

            default:
                return true;
                break;
        }
    }
}
?>