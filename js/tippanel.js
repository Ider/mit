

(function($, window) {
    if ($ === undefined || $ === null) {
        console.error('jQuery is required for BubbleMessage');
        return;
    }

    /* Configuration */
    var cf = {
        arrow: {
            position: {head:0, middle:1, rear:2},
            direction: {
                up: ['bottom', 'left'],
                down: ['top', 'left'],
                left: ['right', 'top'],
                right: ['left', 'top']
            }
        },
        formatArrowConfig: function(config) {
            var arrow = cf.arrow;
            if (!config.position || !arrow.position.hasOwnProperty(config.position)) {
                config.position = 'middle';
            }
            if (!config.direction || !arrow.direction.hasOwnProperty(config.direction)) {
                config.direction = 'down';
            }
            if (!config.size) {
                config.size = 10;
            }
        }
    };
    TipPanel.prototype = {
        genArrow: function(arrowConfig) {
            if (!arrowConfig) return;

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

            css['border-width'] = size;
            css['z-index'] = 7;
            css[seg] = '100%';
            css['border-'+seg+'-color'] = bordercolor;
            css[axi] = (50*pos)+'%';
            css['margin-'+axi] = -size*pos + (pos-1)*borderwidth;
            $('<div></div>').css(css).appendTo(this.panel);
        },

        genBottomArrow: function() {
            this.genArrow({position:'rear', direction:'up', size: 10});
            this.genArrow({position:'middle', direction:'down', size: 10});
        },

        showAt: function(offset) {
            this.panel.offset(offset);
        },

        showAbove: function(obj) {
            var offset = $(obj).offset();
            offset.top -= this.panel.outerHeight()+this.arrow.size;
            this.showAt(offset);

        },
        showUnder: function(obj) {
            obj = $(obj);
            var offset = obj.offset();
            offset.top += obj.outerHeight()+this.arrow.size;
            this.showAt(offset);

        }
    };

    function TipPanel(panel, config) {
        this.panel = panel;
        this.genBottomArrow();
        // this.genTopArrow();
        // this.genLeftArrow();
        this.arrow = {
            size:13
        };
    }

    


    $.tip = function(panel, config) {
        panel = $(panel);
        if (panel.length <= 0) {
            panel = $('<div></div>').appendTo($('body'));
        }

        return new TipPanel(panel, config);
    };
    
})(jQuery, window);