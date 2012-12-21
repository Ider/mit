

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
                up:     ['bottom',  'left', +1],
                down:   ['top',     'left', -1],
                left:   ['right',   'top',  +1],
                right:  ['left',    'top',  -1]
            },
            pointto: {head:0, middle:1, rear:2}
        },
        defaultArrow: {
            position: 'middle',
            direction: 'down',
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

        formatAminate: function(animate) {
            if (!animate ||!animate.show ||!animate.hide) {
                return {show:'show', hide:'hide'};
            }

            return animate;
        }
    };

    TipPanel.prototype = {
        genArrow: function(arrowConfig) {
            if (!arrowConfig) arrowConfig= {};

            cf.formatArrowConfig(arrowConfig);
            this.arrow = arrowConfig;

            var bgColor = this.panel.css('background-color'),
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

            $('<div></div>').css(css).appendTo(this.panel);

            // Generate arrow border if box has border
            var borderwidth = parseInt(this.panel.css('border-width'), 10),
                bordercolor = this.panel.css('border-color');
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
            $('<div></div>').css(css).appendTo(this.panel);
        },

        show: function(offset) {
            if (offset) {
                this.panel.css(offset);
            }
            this.panel.stop(true);
            this.panel.hide();
            this.panel[this.animate.show]();
        },
        showAt: function(obj) {
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
                p = this.panel[abs](); //panel
            offset[piv] += (c-p)/2 + mul*((c+p)/2+arw);

            c = obj[rel](), p = this.panel[rel]();
            offset[axi] += (pto*c-pos*p)/2;

            this.show(offset);
        },
        hide: function(delay) {
            delay = parseInt(delay, 10);
            delay = isNaN(delay)? 0: delay;
            this.panel.delay(delay)[this.animate.hide]();
        }

        // showAbove: function(obj) {
        //     var offset = $(obj).offset();
        //     offset.top -= this.panel.outerHeight()+this.arrow.size;
        //     this.show(offset);

        // },

        // showUnder: function(obj) {
        //     obj = $(obj);
        //     var offset = obj.offset();
        //     offset.top += obj.outerHeight()+this.arrow.size;
        //     this.show(offset);

        // },

        // showBefore: function(obj) {

        // },

        // showAfter: function(obj) {

        // }
    };

    function TipPanel(panel, config) {
        this.panel = panel;
        this.panel.css('position', 'absolute');
        this.genArrow(config.arrow);
        this.animate = cf.formatAminate(config.animate);
    }

    


    $.tip = function(panel, config) {
        panel = $(panel);
        if (panel.length <= 0) {
            panel = $('<div></div>').appendTo($('body'));
        }
        if (!config) {
            config = {};
        }

        return new TipPanel(panel, config);
    };
    
})(jQuery, window);