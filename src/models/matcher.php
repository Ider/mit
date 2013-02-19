<?php

/**
 * RegexMatcher 
 *     base class of StringMatcher and PatternMatcher
 *     this class manipulate the php regular expression modifier,
 *     call preg_match/preg_match_all to search results,
 *     append delimter around pattern
 */
abstract class RegexMatcher {
    protected static $delimiter = '#';
    protected $modifier = '';
    private $modifierFlag = 0;
    protected $matches = array();

    protected function regexMatch($pattern, $str, $all = false) {
        if ($all) {
            $count = preg_match_all($pattern, $str, $this->matches, PREG_SET_ORDER);
        } else {
            $count = preg_match($pattern, $str, $this->matches);
        }

        if ($count === false) {
            error_log('match error: '.preg_last_error());
            return false;
        }

        return $count;
    }

    /**
     * Escape delimiter with setted one, and append delimiter and modifier with pattern
     * @param String $pattern original pattern content
     * @return String pattren with delimiter and modifier around it
     */
    protected function delimit($pattern) {
        $d = self::$delimiter;
        $m = $this->modifier; //modifier

        $p = $d.str_replace($d, '\\'.$d, $pattern).$d.$m;
        return $p;
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
        $this->modifier = '';
        $this->modifierFlag = 0;
    }

    protected function updateModifer() {

    }
}

/**
 * StringMatcher
 *     save string, then apply multiple regular expressions to 
 *     look up different matches
 */
class StringMatcher extends RegexMatcher {
    private $str;

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
     * Seach saved string with regular expression patten, and return matchin result;
     * if pattern is not specified, last successful matched result will be returned;
     * if error occurs, empty array will be returned but mathced result would not be 
     * overrided.
     * @param String $pattern, regular expression that going to apply on string,
     *                         if autoDelimit is set to true, delimiter will be append
     *                         around pattern, otherwise it suppose the pattern contains
     *                         delimiter
     * @param Bool $all, match all results or just first one
     * @return Array 
     */
    public function match($pattern = null, $all = false) {
        if (!isset($pattern)) return $this->matches;

        if ($this->autoDelimit) $pattern = $this->delimit((string)$pattern);

        $result = $this->regexMatch($pattern, $this->str, $all);
        if (!$result) return array();

        return $this->matches;
    }

    /**
     * Execute each pattern in $patternList, and assign result to $obj property.
     * patternList is a list of arrays, each array contains 2-3 elements
     *                 where the first element is the property of $obj that will assign result to,
     *                 the second element it should be the pattern used to look up, 
     *                 the third element is optional, if set, it should be a callback function to manipulate the matching result, 
     *     if third element is not set, then **first** back-reference matched result will be assigned to property,
     *         otherwise callback function result will be assigned to property
     *                                  
     * If no result find for that key, $obj property will be stay the same.
     * @param  Array   $patternList    regular expression patterns container
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
}


/**
 * PatternMatcher 
 *     save pattern, apply this pattern on different strings to get matches
 *     on that string
 */
class PatternMatcher extends RegexMatcher {
    private $pattern;

    /**
     * class Constructor
     * @param String $regex Regex pattern
     */
    public function __construct($pattern) {
        $this->pattern = $this->delimit((string)$pattern);
    }

    /**
     * Seach string with saved regular expression patten, and return matchin result;
     * if string is not specified, last successful matched result will be returned;
     * if error occurs, empty array will be returned but mathced result would not be 
     * overrided.
     * @param String $str, string that looking for pattern match
     * @param Bool $all, indicate match all results or just first one
     * @return Array 
     */
    public function match($str = null, $all = false) {
        if (!isset($str)) return $this->matches;

        $result = $this->regexMatch(($this->pattern.$this->modifier), (string)$str, $all);
        
        if (!$result) return array();   
        return $this->matches;
    }
}


