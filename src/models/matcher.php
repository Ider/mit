<?php

class StringMatcher {
    private $str;
    private $matches;
    private $dirty = true;

    private $modifier = '';

    public function __construct($str) {
        $this->str = (string)$str;
    }

    protected function delimit($pattern) {
        return '/'.str_replace('/', '\\/', $pattern).'/'.$this->modifier;
    }

    public function match($pattern = null, $all = false) {
        if (!isset($pattern)) return $this->matches;

        $pattern = $this->delimit($pattern);

        if ($all) {
            $count = preg_match_all($pattern, $this->str, &$this->matches, PREG_SET_ORDER);
        } else {
            $count = preg_match($pattern, $this->str, &$this->matches);
        }

        if ($count === false) {
            error_log('match error: '.preg_last_error());
        }

        return $this->matches;
    }

    public function setModifier($modifier) {


        $this->updateModifer();
    }

    public function unsetModifier($modifier) {

        $this->updateModifer();
    }

    protected function updateModifer() {

    }
}