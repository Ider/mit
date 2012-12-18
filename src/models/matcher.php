<?php

class StringMatcher {
    private $str;
    private $matches = array();
    private $dirty = true;

    private $modifier = '';
    private $modifierFlag = 0;

    private $autoDelimit = true; 

    ///////Properties///////
    
    /**
     * Determin if need to append delimiter arround pattern or user add them 
     * @param boolean $bool if specified, set autoDelimit with this new value
     * @return boolean autoDelimit value
     */
    public function autoDelimit($bool = null) {
        if (isset($bool)) $this->autoDelimit = (bool)$bool;
        return $this->autoDelimit;
    }

    ///////Methods///////

    /**
     * class Constructor
     * @param String $str String content for looking up with matching pattern
     */
    public function __construct($str) {
        $this->str = (string)$str;
    }

    /**
     * Escape delimiter with setted one, and append delimiter and modifier with pattern
     * @param String $pattern original pattern content
     * @return String pattren with delimiter and modifier around it
     */
    protected function delimit($pattern) {
        if (!$this->autoDelimit) return $pattern;

        static $d = '#'; //delimiter;
        $m = $this->modifier; //modifier

        $p = $d.str_replace($d, '\\'.$d, $pattern).$d.$m;
        return $p;
    }

    /**
     * Seach saved string with regular expression patten, and return matchin result;
     * if pattern is not specified, last successful matched result will be returned;
     * @param String $pattern 
     * @return Array 
     */
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
            return array();
        }

        return $this->matches;
    }

    /**
     * Execute each patern in $patternList, and assign result to $obj property.
     * patternList is a list of arrays, each array contains 2-3 elements
     *                 where the first element is the property of $obj that will assign result to,
     *                 the second element it should be the pattern used to look up, 
     *                 the third element is optional, if set, it should be a callback function to manipulate the matching result, 
     *     if third element is not set, then **first** back-reference matched result will be assigned to property,
     *         otherwise callback function result will be assigned to property
     *                                  
     * If no result find for that key, $obj property will be stay the same.
     * @param  Dictionary   $patternList    regular expression patterns container
     * @param  Object       $obj  the object that need to assign result to, if null. stdClass will be created
     * @return $obj           [description]
     */
    public function execute($patternList, $obj = null){
        if (!is_array($patternList)) return $obj;
        if (!isset($obj)) $obj = new stdClass();

        $defaultCallBack = function($matches){ return $matches[1]; };
        
        foreach ($patternList as $combo) {
            $prop = $combo[0];
            $pattern = $combo[1];
            $callback = $defaultCallBack;
            if (isset($combo[2])) $callback = $combo[2];

            $matches = $this->match($pattern);
            if (empty($matches)) continue;

            $obj->$prop = $callback($matches);
        }
        return $obj;
    }

    public function setModifier($modifierFlag) {
        $this->modifierFlag |= $modifierFlag;
        $this->updateModifer();
    }

    public function unsetModifier($modifierFlag) {
        $this->modifierFlag &= ~$modifierFlag;
        $this->updateModifer();
    }
    
    public function clearModifier() {
        $this->updateModifer();
    }

    protected function updateModifer() {

    }
}