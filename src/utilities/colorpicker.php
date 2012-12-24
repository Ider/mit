<?php

/**
 * Circularly return color values in hex format
 */
class ColorPicker {
    protected $index = 0;

    /**
     * return next color value
     * @return function [description]
     */
    public function next() {
        $color = self::$colors[$this->index ++];
        $this->index %= count(self::$colors);

        return $color;
    }

    /**
     * [reset description]
     * @return [type] [description]
     */
    public function reset() {
        $index = 0;
    }

    public static function allColors() {
        return self::$colors;
    }

    protected static $colors = array(
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