<?php

namespace MVC\Model;

abstract class BaseModel extends \Lib\Database\Database {
 
 /**
  * Fill model object with array information.
  * Fill only fillable properties
  */
  public function fillModelByArray(Array $data) {
    $className = get_class($this);

    if (property_exists($className, 'fillable')) {
      $modelProperties = $this->fillable;
      foreach ($data as $key => $value) {
        foreach ($modelProperties as $modelProperty) {
          if ($key === $modelProperty) {
            $this->setProperty($key, $value);
            break;
          }
        }
      }
    }
  }
  
  public function insertRecord() {
        $dataArray = [];
        
        foreach ($this->fillable as $modelProperty) {
            if (property_exists($this, $modelProperty)) {
                $dataArray[$modelProperty] = $this->getProperty($modelProperty);
            }
        }
        
        return $this->create($dataArray);
    }
  
  abstract protected function getProperty($key);
  abstract protected function setProperty($key, $value);
  
}

 

