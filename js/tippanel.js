

(function($, window) {
    if ($ === undefined || $ === null) {
        console.error('jQuery is required for BubbleMessage');
        return;
    }

    /* Configuration Default Set*/
    var cf = {
        arrow: {
            position: {head:0, middle:1, rear:2},
            direction: {
        /* direction:   [arrow-margin-segment, offset-relative-axi, showat-offset-multiplier] */
                top:     ['bottom',  'left', +1],
                bottom:   ['top',     'left', -1],
                left:   ['right',   'top',  +1],
                right:  ['left',    'top',  -1]
            },
            pointto: {head:0, middle:1, rear:2}
        },
        defaultArrow: {
            position: 'middle',
            direction: 'bottom',
            pointto: 'middle'
        },
        formatConfig: function(config, sets, defaults){
            for (var prop in defaults) {
                if (!config[prop] || !sets[prop].hasOwnProperty(config[prop])) {
                    config[prop] = defaults[prop];
                }
            }

            return config;
        },

        formatArrowConfig: function(config) {
            var arrow = cf.arrow;
            config = cf.formatConfig(config, cf.arrow, cf.defaultArrow);

            config.size = config.size? config.size : 10;
            config.border = 0;
            return config;
        },

        extension: {
            pointTo: function(obj) {
                obj = $(obj);
                var offset = obj.offset();
                if (!offset) return;

                var pto = cf.arrow.pointto[this.arrow.pointto],
                    pos = cf.arrow.position[this.arrow.position],
                    dir = cf.arrow.direction[this.arrow.direction],
                    axi = dir[1], mul = dir[2],
                    arw = this.arrow.size+this.arrow.border;
                
                var piv, //pivot: for arrow offset
                    abs, //aboslute offset: offset on content
                    rel; //relative offset: offset on arrow and pointer
                if (axi == 'top') {
                    piv = 'left'; abs = 'outerWidth'; rel = 'outerHeight';
                } else {
                    piv = 'top';  abs = 'outerHeight'; rel = 'outerWidth';
                }

                var c = obj[abs](), //content
                    p = this[abs](); //panel
                offset[piv] += (c-p)/2 + mul*((c+p)/2+arw);

                c = obj[rel](), p = this[rel]();
                offset[axi] += (pto*c-pos*p)/2;

                this.css(offset);
            }
        }
    };

    TipPanel = {
        init: function(panel, config) {
            panel.css('position', 'absolute');
            TipPanel.genArrow.call(panel, config.arrow);
            $.extend(panel, cf.extension);
        },
        genArrow: function(arrowConfig) {
            if (!arrowConfig) arrowConfig= {};

            cf.formatArrowConfig(arrowConfig);
            this.arrow = arrowConfig;

            var bgColor = this.css('background-color'),
                size = arrowConfig.size,
                pos = cf.arrow.position[arrowConfig.position],
                dir = cf.arrow.direction[arrowConfig.direction],
                seg = dir[0], axi = dir[1],

                css = {
                    heigt: 0,
                    width: 0,
                    border: 'solid transparent',
                    'border-width': size,
                    position: 'absolute',
                    'z-index': 21
                };

            css[seg] = '100%';
            css['border-'+seg+'-color'] = bgColor;
            css[axi] = (50*pos)+'%';
            css['margin-'+axi] = -size*pos;
            this.arrow.inner = $('<div></div>').css(css).appendTo(this);

            // Generate arrow border if box has border
            var brd = 'border-'+arrowConfig.direction,
                borderwidth = parseInt(this.css(brd+'-width'), 10),
                bordercolor = this.css(brd+'-color');
            if (isNaN(borderwidth)) borderwidth = 0;
            if (arrowConfig.noborder || borderwidth <=0) return;
            ++borderwidth;//arrow border is tilted, make 1px giger to make nicer
            size += borderwidth;
            this.arrow.border = borderwidth;

            css['border-width'] = size;
            css['z-index'] = 7;
            css[seg] = '100%';
            css['border-'+seg+'-color'] = bordercolor;
            css[axi] = (50*pos)+'%';
            css['margin-'+axi] = -size*pos + (pos-1)*borderwidth;
            this.arrow.outer = $('<div></div>').css(css).appendTo(this);
        }
    };

    function TipPanel(panel, config) {
        
    }

    $.tip = function(panel, config) {
        panel = $(panel);
        if (panel.length <= 0) panel = $('<div></div>').appendTo($('body'));
        if (!config) config = {};
        
        TipPanel.init(panel, config);
        
        return panel;
    };
    
})(jQuery, window);