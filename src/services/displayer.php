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
     * [createElement description]
     * @param  [type] $tag        [description]
     * @param  array  $attributes [description]
     * @return [type]
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
        $mainContainer = $this->layout->createElement('div');
        $this->layout->appendChild($mainContainer);

        $style = Util::cssjoin(array(
                    'width' => '900px',
                    'margin' => 'auto',
                ));

        $mainContainer->setAttribute('style', $style);

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