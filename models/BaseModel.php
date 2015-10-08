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
        $dataArray = $this->generateArrayFromPropertyInformation();

        return $this->create($dataArray);
    }
    
    /**
     * We extract model information and after by filter param, we try to update this 
     * database information.
     * 
     * @param string $whereClause filter clause for updating.
     * @param array $params
     */
    public function updateRecordByFilterParams($whereClause, array $params) {
        $dataArray = $this->generateArrayFromPropertyInformation();
        
        return $this->where($whereClause)->update($dataArray, $params);
    }

    abstract protected function getProperty($key);

    abstract protected function setProperty($key, $value);
    
    private function generateArrayFromPropertyInformation() {
        $dataArray = [];

        foreach ($this->fillable as $modelProperty) {
            if (property_exists($this, $modelProperty)) {
                $dataArray[$modelProperty] = $this->getProperty($modelProperty);
            }
        }
        
        return $dataArray;
    }
}
