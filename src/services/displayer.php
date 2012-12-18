<?php

require_once 'src/models/theater.php';
require_once 'src/models/movie.php';
require_once 'src/utilities/colorpicker.php';
require_once 'src/utilities/util.php';

abstract class DisplayerBase {
    protected $layout;

    /**
     * generate layout for display
     * @return bool succeed 
     */
    abstract public function generate();

    /**
     * show the display layout, usually just simply echo $layout
     */
    abstract public function show();
}

abstract class DOMDisplayerBase extends DisplayerBase {
    
    public function __construct() {
        $this->layout = new DOMDocument('1.0');
    }

    /**
     * create a new DOM elements with specific tag, and set attributes from passed in data
     * @param  String $tag     DOM tag
     * @param  Dictionary  $attributes DOM attributes
     * @return DOMElement   DOMElement with $tag and $attributes information
     */
    public function createElement($tag, $attributes = array()) {
        $elem = $this->layout->createElement($tag);

        foreach ($attributes as $key => $value) {
            $elem->setAttribute($key, $value);
        }

        return $elem;
    }

    /**
     * Create a DOMTextNode
     * @param  String $text   TextNode content value
     * @param  DOMNode $parent optional, parentNode that text node append to.
     *                      if $parent is String, then DOMElement will be created with that as tag  
     *                                          
     * @return DOMNode   a TextNode created by Dom with $text as content, or the parent contains that textnode
     */
    public function createTextNode($text, $parent = null) {
        $textNode = $this->layout->createTextNode($text);
        if (!isset($parent)) return $textNode;

        if (!($parent instanceof DOMNode)) {
            $parent = $this->createElement($parent);
        }

        $parent->appendChild($textNode);
        return $parent;
    }

    /**
     * Create a LinkNode
     * @param  String $text       LinkNode content value
     * @param  String $link       value for property 'href'
     * @param  Dictionary  $attributes      other attributes informations
     * @param  DOMNode $parent    optional, parentNode that link node append to,
     *                                      if $parent is String, then DOMElement will be created with that as tag 
     * @return DOMElement         
     */
    public function createLinkNode($text, $link, $attributes = array(), $parent = null) {
        $linkNode = $this->createTextNode($text, 'a');
        $linkNode->setAttribute('href', $link);

        foreach ($attributes as $key => $value) {
            $linkNode->setAttribute($key, $value);
        }

        if (!isset($parent)) return $linkNode;
        
        if (!($parent instanceof DOMNode)) {
            $parent = $this->createElement($parent);
        }
        
        $parent->appendChild($linkNode);
        return $parent;
    }

}

class TheaterListDisplayer extends DOMDisplayerBase{
    protected $theaterList = null;

    public function __construct(TheaterList $list) {
        parent::__construct();
        $this->theaterList = $list;
    }

    public function generate() {
        $mainContainer = $this->createElement('div'
                            , array('class' => 'main_container'));
        $this->layout->appendChild($mainContainer);

        foreach ($this->theaterList->theaters as $theater) {
            $theaterLayout = $this->layoutForTheater($theater);
            $mainContainer->appendChild($theaterLayout);
        }

        return true;
    }

    protected function layoutForTheater(Theater $theater) {
        $dom = $this->layout;
        $theaterContainer = $dom->createElement('div');

        $theaterContainer->setAttribute('class', 'theater_container');
        $theaterContainer->setAttribute('data-tid', $theater->tid);

        $theaterName = $this->createTextNode($theater->name, 'h3');
        $theaterContainer->appendChild($theaterName);

        $theaterAddress = $this->createTextNode($theater->address, 'div');
        $theaterContainer->appendChild($theaterAddress);

        $theaterPhone = $this->createTextNode($theater->phone, 'div');
        $theaterContainer->appendChild($theaterPhone);

        $linkText = $this->theaterList->source . ' Link';
        $theaterLink = $this->createLinkNode($linkText, $theater->link, array('target'=>'_blank'), 'div');
        $theaterContainer->appendChild($theaterLink);

        return $theaterContainer;
    }

    public function show() {
        echo $this->layout->saveHTML();
    }
}

/**
 * 
 */
class MovieListDisplayer extends DOMDisplayerBase{
    protected $movieList = null;
    protected $colorpicker;
    public function __construct(MovieList $list) {
        parent::__construct();
        $this->movieList = $list;
        $this->colorpicker = new ColorPicker();
    }

    public function generate() {
        $mainContainer = $this->createElement('div'
                            , array('class' => 'main_container'));
        $this->layout->appendChild($mainContainer);

        //create outer container
        $styleList = array(
                    'overflow' => 'hidden',
                    'border' => "1px solid red",
                );
        $style = Util::cssjoin($styleList);

        $attrs = array( 'class' => 'movie_showtime_outer_container', 
                        'style' => $style,
                    );
        $outerContainer = $this->createElement('div', $attrs);
        $mainContainer->appendChild($outerContainer);

        //creat inner container
        $styleList = array(
                    'margin-left' => "-600px",
                    'width' => (25*60*self::WIDTH_PER_MIN)."px",
                );
        $style = Util::cssjoin($styleList);
        
        $attrs = array( 'class' => 'movie_showtime_inner_container', 
                        'style' => $style,
                    );
        $innerContainer = $this->createElement('div', $attrs);
        $outerContainer->appendChild($innerContainer);

        //create movie layout
        foreach ($this->movieList->movies as $movie) {
            $movieLayout = $this->layoutForMovie($movie);
            $innerContainer->appendChild($movieLayout);
        }
        
        return true;
    }

    protected function layoutForMovie(Movie $movie) {
        $this->showtimeRows = array(); //clear showtime row
        
        $attrs = array( 'class' => 'movie_container',
                        'data-mid' => $movie->mid,
                        );
        $movieContainer = $this->createElement('div', $attrs);

        //basic information
        $theaterName = $this->createTextNode($movie->name, 'h3');
        $movieContainer->appendChild($theaterName);

        $theaterLink = $this->createLinkNode('Movie Link', $movie->link, array('target'=>'_blank'), 'div');
        $movieContainer->appendChild($theaterLink);

        //showtime container
        $attrs = array( 'class' => 'movie_container',
                        'title' => $movie->name,
                        'data-mid' => $movie->mid,
                        );
        $showtimeConteainer = $this->createElement('div', $attrs);
        $movieContainer->appendChild($showtimeConteainer);

        //showtimes layout
        $this->generateShowtimeLayout($movie, $showtimeConteainer);

        //showtime container style
        $width = self::WIDTH_PER_MIN * 25*60;
        $height = self::SHOWTIME_HIGHT * count($this->showtimeRows);

        $styleList = array(
                'position' => 'relative',
                'width' => "{$width}px",
                'height' => "{$height}px",
                'border' => "1px dashed black",
            );
        $style = Util::cssjoin($styleList);
        $showtimeConteainer->setAttribute('style', $style);

        return $movieContainer;
    }

    const WIDTH_PER_MIN = 1;
    const SHOWTIME_HIGHT = 16;

    protected function generateShowtimeLayout(Movie $movie, DOMElement $showtimeConteainer) {
        $color = $this->colorpicker->next();
        $width = self::WIDTH_PER_MIN * $movie->runtime;
        $height = self::SHOWTIME_HIGHT - 1;

        foreach ($movie->showtimes as $showtime) {
            $time = explode(':', $showtime);
            $minus = $time[0]*60+$time[1];
            $left = self::WIDTH_PER_MIN*$minus;
            $top = self::SHOWTIME_HIGHT * $this->showtimeRowIndex($minus, $movie->runtime);
            $styleList = array(
                    'position' => 'absolute',
                    'top' => "{$top}px",
                    'left' => "{$left}px",
                    'width' => "{$width}px",
                    'height' => "{$height}px",
                    'background-color' => $color,
                );
            $style = Util::cssjoin($styleList);
            $attrs = array( 'class' => 'movie_showtime',
                            'title' => $showtime,
                            'data-showtime' => $showtime,
                            'style' => $style,
                    );
            $showtimeBar = $this->createElement('div', $attrs);
            $showtimeConteainer->appendChild($showtimeBar);
            $showtimeBar->setAttribute('style', $style);
        }
    }

    /////// Help Methods ///////
    private $showtimeRows = array();
    private function showtimeRowIndex($showtime, $runtime) {
        $count = count($this->showtimeRows);
        for ($i=0; $i < $count; $i++) { 
            if ($this->showtimeRows[$i] < $showtime) {
                break;
            }
        }

        $this->showtimeRows[$i] = ($showtime + $runtime);
        return $i;
    }

    public function show() {
        echo $this->layout->saveHTML();
    }
}