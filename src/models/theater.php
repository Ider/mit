<?php
class Theater {
    public $tid = '';
    public $name = '';
    public $link = '';
    public $address = '';
    public $phone = '';
}

class TheaterList {
    public $zipcode = '';
    public $link = '';
    public $source = 'unknown';
    public $theaters = array();

    public function __get($name) {
        switch ($name) {
            case 'size':
                return count($this->theaters);
            
            default:
                return null;
        }
    }
}
