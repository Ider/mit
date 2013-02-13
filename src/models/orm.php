<?php
include_once 'src/services/connector.php';

class MysqlORM extends DBConnector{
    private $modelType;
    public function __construct($type) {
        $this->modelType = $type;
    }

    public function mapArray($query) {
        $models = array();

        if ($result = $this->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $model = new $this->modelType;
                foreach ($row as $key => $value) {
                    $model->$key = $value;
                }
                $models[] = $model;
            }
            $result->free();
        }

        return $models;
    }

    public function mapObject($query) {
        $model = null;

        if ($result = $this->query($query)) {
            if ($row = $result->fetch_assoc()) {
                $model = new $this->modelType;
                foreach ($row as $key => $value) {
                    $model->$key = $value;
                }
            }
            $result->free();
        }
        return $model;
    }
}