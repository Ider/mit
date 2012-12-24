<?php 

define('WIDTH_PER_MIN', 1);
define('HEIGHT_PER_ROW', 16);

class Util {
    //Join associated array as css style: combine key and value with colon,
    //concatenate pairs with semicolon.
    public static function join($pieces) {
        if (!is_array($pieces)) return $pieces;

        $result = ''; 
        foreach ($pieces as $key => $value)
            $result .= "$key: $value; ";
        
        return $result;
    }
}
class Displayer {
    protected $layout = '';
    protected $theater = null;
    protected $dom = null;
    public function __construct($theater)  {
        $this->theater = $theater;
    }
    public function generate() {
        $this->dom = new DOMDocument('1.0');
        $dom = $this->dom;
        $theater = $this->theater;

        $theaterContainer = $dom->createElement('div');
        $dom->appendChild($theaterContainer);
        $styleList = array(
                    // 'position' => 'relative',
                    'width' => '900px',
                    'margin' => 'auto',
                    // 'height' => '100%',
                );
        $style = Util::join($styleList);
        $theaterContainer->setAttribute('style', $style);


        $theaterName = $dom->createElement('h2');
        $theaterNameText = $dom->createTextNode($theater->name);
        $theaterName->appendChild($theaterNameText);
        $theaterContainer->appendChild($theaterName);

        // file_put_contents('/tmp/mit/html.txt',  $dom->saveHTML());
        // $this->append("<h2>{$theater->name}</h2>");
       

        $outerContainer = $dom->createElement('div');
        $theaterContainer->appendChild($outerContainer);
        $styleList = array(
                    // 'position' => 'relative',
                    'overflow' => 'hidden',
                    // 'margin' => 'auto',
                    'border' => "1px solid red",
                    // 'height' => '100%',
                );
        $style = Util::join($styleList);
        $outerContainer->setAttribute('style', $style);
        $outerContainer->setAttribute('class', 'movie_showtime_outer_container');
        $innerContainer = $dom->createElement('div');
        $styleList = array(
                    // 'position' => 'absolute',
                    'margin-left' => "-600px",
                    'width' => "1500px", //25 hours
                    // 'top' => "0",
                );
        $style = Util::join($styleList);
        $innerContainer->setAttribute('style', $style);
        $innerContainer->setAttribute('class', 'movie_showtime_inner_container');

        $outerContainer->appendChild($innerContainer);
        
        // echo '<div class="outer_container"><div class="inner_container">';


        foreach ($theater->movies as $movie) {
            $movieContainer = $this->layoutForMovie($movie);
            $innerContainer->appendChild($movieContainer);
        }

       // echo '</div></div>';
        echo $dom->saveHTML();
    }


//TODO htmlspecialchars(string); for movie names
    protected function layoutForMovie($movie) {
        $dom = $this->dom;
        $this->displayRows = array();

        $movieContainer = $dom->createElement('div');
        $movieContainer->setAttribute('class', 'movie_container');

        $movieName = $dom->createElement('h3');
        $movieNameText = $dom->createTextNode($movie->name);
        $movieName->appendChild($movieNameText);
        $movieContainer->appendChild($movieName);

        // $this->append("<h3>{$movie->name}</h3>");


        $showtimeConteainer = $dom->createElement('div');
        $movieContainer->appendChild($showtimeConteainer);

        // $width = WIDTH_PER_MIN * 24*60;
        // $height = 2*16;

        // $this->append("<div title='{$movie->name}' style='width:{$width}px; height:{$height}px; border:1px solid black; position:relative;'>");
        

        $count = count($movie->showtimes);
        $color = self::getColor();
        $width = WIDTH_PER_MIN * $movie->runtime;
        $height = HEIGHT_PER_ROW - 1;

        for ($i=0; $i < $count; $i++) {
            $showtime = $movie->showtimes[$i];
            $time = explode(':', $showtime);
            $minus = $time[0]*60+$time[1];
            $left = WIDTH_PER_MIN*$minus;
            $top = HEIGHT_PER_ROW * $this->getdisplayRowIndex($minus, $movie->runtime);
            $styleList = array(
                    'position' => 'absolute',
                    'top' => "{$top}px",
                    'left' => "{$left}px",
                    'width' => "{$width}px",
                    'height' => "{$height}px",
                    'background-color' => $color,
                );
            $style = Util::join($styleList);

            $showtimeBar = $dom->createElement('div');
            $showtimeConteainer->appendChild($showtimeBar);

            $showtimeBar->setAttribute('class', 'movie_showtime');
            $showtimeBar->setAttribute('title', $showtime);
            // $showtimeBar->setAttribute('data-movie_name', $movie->name);
            $showtimeBar->setAttribute('data-showtime', $showtime);
            $showtimeBar->setAttribute('style', $style);
        }
        
        // $this->append("</div>");

        $showtimeConteainer->setAttribute('title', $movie->name);
        $showtimeConteainer->setAttribute('class', "movie_showtime_container");
        $width = WIDTH_PER_MIN * 25*60;
        $height = HEIGHT_PER_ROW * count($this->displayRows);

        $styleList = array(
                'position' => 'relative',
                'width' => "{$width}px",
                'height' => "{$height}px",
                'border' => "1px dashed black",
            );
        $style = Util::join($styleList);
        // $style = "width:{$width}px; height:{$height}px; position:relative; border: 1px dashed black;";
        $showtimeConteainer->setAttribute('style', $style);

        return $movieContainer;
    }

    private $displayRows = array();

    protected function getdisplayRowIndex($showtime, $runtime) {
        $count = count($this->displayRows);
        for ($i=0; $i < $count; $i++) { 
            if ($this->displayRows[$i] < $showtime) {
                break;
            }
        }

        $this->displayRows[$i] = ($showtime + $runtime);
        return $i;
    }

    protected function append($content) {
        echo $content;
    }

    protected static $colorIndex = 0;

    public static function getColor() {
        return self::$COLORLIST[self::$colorIndex++];
    }



    public static $COLORLIST = array(
        // "#F0F8FF", //AliceBlue
        // "#FAEBD7", //AntiqueWhite
        // "#00FFFF", //Aqua
        "#7FFFD4", //Aquamarine
        // "#F0FFFF", //Azure
        // "#F5F5DC", //Beige
        // "#FFE4C4", //Bisque
        "#000000", //Black
        // "#FFEBCD", //BlanchedAlmond
        // "#0000FF", //Blue
        "#8A2BE2", //BlueViolet
        "#A52A2A", //Brown
        "#DEB887", //BurlyWood
        // "#5F9EA0", //CadetBlue
        // "#7FFF00", //Chartreuse
        // "#D2691E", //Chocolate
        "#FF7F50", //Coral
        "#6495ED", //CornflowerBlue
        // "#FFF8DC", //Cornsilk
        "#DC143C", //Crimson
        // "#00FFFF", //Cyan
        "#00008B", //DarkBlue
        "#008B8B", //DarkCyan
        "#B8860B", //DarkGoldenRod
        // "#A9A9A9", //DarkGray
        // "#006400", //DarkGreen
        "#BDB76B", //DarkKhaki
        "#8B008B", //DarkMagenta
        "#556B2F", //DarkOliveGreen
        "#FF8C00", //Darkorange
        "#9932CC", //DarkOrchid
        // "#8B0000", //DarkRed
        "#E9967A", //DarkSalmon
        "#8FBC8F", //DarkSeaGreen
        "#483D8B", //DarkSlateBlue
        "#2F4F4F", //DarkSlateGray
        "#2F4F4F", //DarkSlateGrey
        "#00CED1", //DarkTurquoise
        "#9400D3", //DarkViolet
        "#FF1493", //DeepPink
        "#00BFFF", //DeepSkyBlue
        "#696969", //DimGray
        "#696969", //DimGrey
        "#1E90FF", //DodgerBlue
        "#B22222", //FireBrick
        "#FFFAF0", //FloralWhite
        "#228B22", //ForestGreen
        "#FF00FF", //Fuchsia
        "#DCDCDC", //Gainsboro
        "#F8F8FF", //GhostWhite
        "#FFD700", //Gold
        "#DAA520", //GoldenRod
        "#808080", //Gray
        "#808080", //Grey
        "#008000", //Green
        "#ADFF2F", //GreenYellow
        "#F0FFF0", //HoneyDew
        "#FF69B4", //HotPink
        "#CD5C5C", //IndianRed
        "#4B0082", //Indigo
        "#FFFFF0", //Ivory
        "#F0E68C", //Khaki
        "#E6E6FA", //Lavender
        "#FFF0F5", //LavenderBlush
        "#7CFC00", //LawnGreen
        "#FFFACD", //LemonChiffon
        "#ADD8E6", //LightBlue
        "#F08080", //LightCoral
        "#E0FFFF", //LightCyan
        "#FAFAD2", //LightGoldenRodYellow
        "#D3D3D3", //LightGray
        "#D3D3D3", //LightGrey
        "#90EE90", //LightGreen
        "#FFB6C1", //LightPink
        "#FFA07A", //LightSalmon
        "#20B2AA", //LightSeaGreen
        "#87CEFA", //LightSkyBlue
        "#778899", //LightSlateGray
        "#778899", //LightSlateGrey
        "#B0C4DE", //LightSteelBlue
        "#FFFFE0", //LightYellow
        "#00FF00", //Lime
        "#32CD32", //LimeGreen
        "#FAF0E6", //Linen
        "#FF00FF", //Magenta
        "#800000", //Maroon
        "#66CDAA", //MediumAquaMarine
        "#0000CD", //MediumBlue
        "#BA55D3", //MediumOrchid
        "#9370DB", //MediumPurple
        "#3CB371", //MediumSeaGreen
        "#7B68EE", //MediumSlateBlue
        "#00FA9A", //MediumSpringGreen
        "#48D1CC", //MediumTurquoise
        "#C71585", //MediumVioletRed
        "#191970", //MidnightBlue
        "#F5FFFA", //MintCream
        "#FFE4E1", //MistyRose
        "#FFE4B5", //Moccasin
        "#FFDEAD", //NavajoWhite
        "#000080", //Navy
        "#FDF5E6", //OldLace
        "#808000", //Olive
        "#6B8E23", //OliveDrab
        "#FFA500", //Orange
        "#FF4500", //OrangeRed
        "#DA70D6", //Orchid
        "#EEE8AA", //PaleGoldenRod
        "#98FB98", //PaleGreen
        "#AFEEEE", //PaleTurquoise
        "#DB7093", //PaleVioletRed
        "#FFEFD5", //PapayaWhip
        "#FFDAB9", //PeachPuff
        "#CD853F", //Peru
        "#FFC0CB", //Pink
        "#DDA0DD", //Plum
        "#B0E0E6", //PowderBlue
        "#800080", //Purple
        "#FF0000", //Red
        "#BC8F8F", //RosyBrown
        "#4169E1", //RoyalBlue
        "#8B4513", //SaddleBrown
        "#FA8072", //Salmon
        "#F4A460", //SandyBrown
        "#2E8B57", //SeaGreen
        "#FFF5EE", //SeaShell
        "#A0522D", //Sienna
        "#C0C0C0", //Silver
        "#87CEEB", //SkyBlue
        "#6A5ACD", //SlateBlue
        "#708090", //SlateGray
        "#708090", //SlateGrey
        "#FFFAFA", //Snow
        "#00FF7F", //SpringGreen
        "#4682B4", //SteelBlue
        "#D2B48C", //Tan
        "#008080", //Teal
        "#D8BFD8", //Thistle
        "#FF6347", //Tomato
        "#40E0D0", //Turquoise
        "#EE82EE", //Violet
        "#F5DEB3", //Wheat
        "#FFFFFF", //White
        "#F5F5F5", //WhiteSmoke
        "#FFFF00", //Yellow
        "#9ACD32", //YellowGreen
    );
}