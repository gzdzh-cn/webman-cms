(function ($, window, undefined) {
    window.yunu = {
        init : function (){
            var json = arguments[0]?arguments[0]:{};
            if (json.imgAuto) {
                $('img').each(function () {
                    var t = $(this);
                    if(t.attr('img-Auto')){
                        t.attr('img-Auto')=='cover'?t.imgAuto('cover'):t.imgAuto();
                    }
                });
            }
            $('body').fadeTo('fast', 1);
            return this;
        },
        rem : function (size) {
            size = size || 640;
            $(window).width()>1024?$('html').css('font-size', 320):$('html').css('font-size', $(window).width()/size*100);
            return this;
        },
        nav : function () {
            var nav = $('#nav');
            var btn = $('#nav>ul>li>a');
            $('#menu').click(function () {
                nav.addClass('in');
            });
            nav.click(function () {
                nav.removeClass('in');
            });
            $('#nav>ul').click(function (event) {
                event.stopPropagation();
            });
            btn.each(function () {
                var t = $(this);
                if (t.next('ul').children().length) {
                    t.prop('href', 'javascript:;').append('<i class="fa fa-angle-down"></i>');
                }
            })
            .click(function (event) {
                var t = $(this);

                var abtn = btn.filter(function (argument) {
                    return $(this).hasClass('in');
                });

                var slide = function (obj, ud) {
                    if ( ud == 'down' ) {
                        obj.addClass('in').next('ul').slideDown('fast');
                    }else{
                        obj.removeClass('in').next('ul').slideUp('fast');
                    }
                };

                if (abtn === t) {
                    slide(t, 'up');
                }else{
                    slide(t, 'down');
                    slide(abtn, 'up');
                }
            });
            return this;
        },
        /*使用方法
        var throttled = yunu.letter(function () {})
        $(window).scroll(throttled);
        */
        letter : function (fn) {
            if (typeof arguments[0] != 'function') return;
            fn();
            var times = typeof arguments[1] != 'number'?300:arguments[1];
            var timer = null;
            var st = new Date().getTime();
            return function () {
                var et = new Date().getTime();
                if (et - st < times) {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        //return fn.apply(this,arguments);
                        fn();
                    }, times);
                } else{
                    clearTimeout(timer);
                    st = et;
                    fn();
                }
            };
        },
        css3 : (function () {
            var div = document.createElement('div'),
            vendors = 'Ms O Moz Webkit'.split(' ');
            return function(prop) {
                var len = vendors.length;
                if (typeof prop != 'string') return false;
                if ( prop in div.style ) return true;
                prop = prop.replace(/^[a-z]/, function(val) {
                    return val.toUpperCase();
                });
                while(len--) {
                    if ( vendors[len] + prop in div.style ) {
                        return true;
                    } 
                }
                return false;
            };
        })(),
        css3_3d : function () {
            var docElement = document.documentElement;
            var support = yunu.css3('perspective');
            var body = document.body;
            if(support && 'webkitPerspective' in docElement.style){
                var style = document.createElement('style');
                style.type = 'text/css';
                style.innerHTML = '@media (transform-3d),(-webkit-transform-3d){#css3_3d_test{left:9px;position:absolute;height:3px;}}';
                body.appendChild(style);
                var div = document.createElement('div');
                div.id = 'css3_3d_test';
                body.appendChild(div);
                support = div.offsetLeft === 9 && div.offsetHeight === 3;
            }
            yunu.css3_3d = function () {
                return support;
            };
            return support;
        },
        //延迟加载
        lazyLoad : function (obj, move){
            obj.each(function(i){
                var t = $(this);
                if (typeof t.attr('data-src')=='undefined' && typeof move=='undefined') {
                    yunu.lazyLoad(obj.not(t));
                    return false;
                }
                t.show = function () {
                    var top = $(window).height() + $(window).scrollTop();
                    if ( top > (t.offset().top - (parseInt(t.css('top'))||0) ) ) {
                        //回调函数型
                        if (typeof move == 'function') {
                            move.call(t);
                        //文字运动显示型
                        }else if (move) {
                            t.animate({
                                top : 0,
                                left : 0
                            }, parseInt(t.attr('timer'))||2000, function(){

                            });
                        //图片延迟加载
                        }else{
                            t.attr('src', t.attr('data-src')).removeAttr('data-src');
                        }
                        $(window).unbind('scroll',t.show);
                    }else{
                        return false;
                    }
                };
                if (t.show() === false) {
                    $(window).bind('scroll', t.show);
                }else{
                    setTimeout(function () {
                        yunu.lazyLoad(obj.not(t));
                    });
                    return false;
                }
            });
            return this;
        },
        loadMore: function loadMore() {
            var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

            config = $.extend(true, {
                key: true, // load开关
                url: '', // api url
                type: 'POST',
                btn: $('#load_more_btn'), // 加载更多按钮
                box: $('#list>ul'), // 加载内容的容器
                data: { // api接口配置参数
                    pages: 1,
                    limit: 5
                },
                beforeSend: function beforeSend() {}, // 发送请求前
                done: function done() {}, // 请求成功
                fail: function fail() {}, // 请求失败
                always: function always() {}, // 请求完成
                load_icon: true, // 是否开启请求等待图标样式
                load_icon_class: '', // 图标尺寸样式 可选: la-sm / la-2x / la-3x
                loading: $('<div class="la-ball-fall"><div></div><div></div><div></div><div></div></div>'), // 按钮load icon元素
                load_more_none: $('<p class="load_more_none tac c_red mt20">没有了</p>') // 没有内容时显示的元素
            }, config);
            var load = function load() {
                $.ajax({
                    url: config.url,
                    type: config.type,
                    data: config.data,
                    dataType: "jsonp",
                    beforeSend: function beforeSend(XMLHttpRequest) {
                        config.beforeSend(XMLHttpRequest);
                        config.btn.attr('disabled', true);
                    }
                }).done(function (res, textStatus, XMLHttpRequest) {
                    config.done(res, textStatus, XMLHttpRequest);
                    if (res.data.length < config.data.limit) {
                        config.btn.replaceWith(config.load_more_none);
                    }
                }).fail(function (XMLHttpRequest, textStatus, errorThrown) {
                    config.fail(XMLHttpRequest, textStatus, errorThrown);
                }).always(function (XMLHttpRequest, textStatus, errorThrown) {
                    config.always(XMLHttpRequest, textStatus, errorThrown);
                    config.key = true;
                    config.btn.attr('disabled', false);
                });
            };
            if (config.load_icon) {
                $(config.loading).addClass(config.load_icon_class); // 设置图标大小
                config.btn.after(config.loading); // 装载图标动画
            }
            config.btn.click(function () {
                if (!config.key) return;
                config.key = false;
                config.data.pages++;
                load();
            });
        },
        tab : function(tab, list, fn){
            var tab = $(tab),
                list = $(list),
                show = function (t) {
                    var index = $(t).data('i');
                    tab.removeClass('active').eq(index).addClass('active');
                    list.hide().eq(index).stop(true, true).fadeTo(0,.5).fadeTo('fast',1);
                    if (fn) fn(t);
                };
            tab.each(function (i) {
                tab.eq(i).data('i', i);
            }).click(function (){
                show(this);
            });
            show(tab.first());
            return this;
        },
        scrollTop : function () {
            var oA,px,hb,win,box,throttled,box_win_top,timer = null;

            typeof arguments[1] != 'number'?px=150:px=arguments[1];

            if (typeof arguments[0] == 'object') {
                if (arguments[2] == 'box') {
                    hb = win = arguments[0];
                    box = oA = $("a[href='#top']");
                    throttled = yunu.letter(function () {
                        win.scrollTop() > px?oA.fadeIn():oA.fadeOut('fast');
                    });
                }else{
                    hb = $('html,body');
                    win = $(window);
                    box = arguments[0];
                    oA = box.find("a[href='#top']");
                    throttled = yunu.letter(function () {
                        box_win_top = (win.height()-box.height())/2;
                        box.animate({
                            'top': box_win_top + win.scrollTop()
                        });
                        if (win.scrollTop() + box_win_top > px && box.is(':hidden')) {
                            box.stop(true, true).fadeIn();
                        }
                        if (win.scrollTop() + box_win_top < px && !box.is(':hidden')) {
                           box.stop(true).fadeOut('fast');
                        }
                    });
                }
            }else{
                hb = $('html,body');
                win = $(window);
                box = oA = $("a[href='#top']");
                throttled = yunu.letter(function () {
                    win.scrollTop() > px?oA.fadeIn():oA.fadeOut('fast');
                });
            }

            oA.click(function () {
                event.stopPropagation();
                event.preventDefault();
                hb.animate({  
                    scrollTop: 0
                });
                box.fadeOut();
            });
            win.scroll(throttled);
            return this;
        },
    };

    $.fn.roll = function () {
        var t = this,
            json = arguments[0]?arguments[0]:{};
        if (typeof json.num != 'number') json.num = 1;
        if (typeof json.timer != 'number') json.timer = 6000;
        if (typeof json.speed != 'number') json.speed = 400;
        if (typeof json.easing != 'string') json.easing = 'swing';
        if (typeof json.timing != 'string') json.timing = 'ease-out';
        if (typeof json.adaptive === 'undefined') json.adaptive = true;
        if (typeof json.center === 'undefined') json.center = true;

        var oUl = t.find('ul'),
            oLi = oUl.find('li'),
            oLL = oLi.length,
            oW = oLi.outerWidth(true),
            banner_l = json.lbtn || t.find('.btn_l'),
            banner_r = json.rbtn || t.find('.btn_r'),
            btn = t.find('.banner_btn').find('a'),
            oIndex = -oLL,
            timer = null,
            moveIn = false,
            outer = false,
            sx = 0,
            ex = 0,
            downTime = 0,
            _l = 0,
            css3d = yunu.css3_3d(),
            move,banner_btn,banner_btn_span,suspend,_w,cw,dcenter,bresize,
            translate3d = function () {
                return 'translate3d('+ oW * oIndex +'px, 0px, 0px)';
            },
            toleft = function () {
                return oW * oIndex;
            };

        t.roll_stop = function () {
            clearInterval(timer);
        }
        t.roll_start = function (bl) {
            if (bl) {
                moveIn = false;
            };
            clearInterval(timer);
            timer = setInterval(function(){
                move('-');
            }, json.timer);
        }

        oUl.html( oUl.html() + oUl.html() + oUl.html() );
        oLi = oUl.find('li');
        oLL = oLi.length;

        t.css({
            position : 'relative',
            overflow : 'hidden',
            margin   : '0 auto',
            width    : json.width?json.width:(json.banner?'100%':json.num * oW)
        });

        oLi.css({
            width    : json.banner?oW:oLi.width(),
            height   : 'auto',
            float    : 'left',
            position : 'relative',
            overflow : 'hidden'
        });

        oUl.css({
            width  : oLL * oW,
            height : 'auto'
        });

        if (json.height) t.height(json.height);
        if (json.btn) {
            banner_btn = $('<div class="banner_btn"></div>');
            banner_btn_span = $('<span></span>');
            for (var i = 0; i < oLL/3; i++) {
                banner_btn_span.append('<a href="javascript:;"></a>');
            }
            btn = banner_btn_span.find('a');
            btn.first().addClass('in');
            banner_btn.append(banner_btn_span);
            t.append(banner_btn);
            if (json.btn == 'all') {
                t.append('<div class="banner_l"></div><div class="banner_r"></div>');
                banner_l = t.find('.banner_l');
                banner_r = t.find('.banner_r');
            }
        }
        suspend = btn.add(banner_l).add(banner_r);
        if (json.banner) {
            dcenter = $('div.center:first');
            bresize = function(){
                _w = t.width();
                cw = dcenter.width();
                if (json.adaptive) {
                    oW = _w;
                }else{
                    oW = _w>cw?_w:cw;
                }
                oLi.width(oW);
                oUl.width(oLL * oW);
                if (css3d) {
                    oUl.css({'transform':translate3d(), 'transition':'transform 0ms'});
                } else{
                    oUl.css({left : toleft()});
                }
            };
            bresize();
            $(window).resize(function () {
                bresize();
            });
        }else{
            suspend.add(oLi);
        }

        if (css3d) {
            oUl.css({
                'transform' : translate3d()
            }).on('webkitTransitionEnd', function () {
                if (moveIn) {
                    if (oIndex >= 0 ) {
                        oIndex = - oLL/3;
                        oUl.css({'transform':translate3d(), 'transition':'transform 0ms'});
                    }
                    if (oIndex <= - (oLL - json.num)) {
                        oIndex = - (2*oLL/3 - json.num);
                        oUl.css({'transform':translate3d(), 'transition':'transform 0ms'});
                    }
                    moveIn = false;
                }
            });
        } else{
            oUl.css({
                position : 'absolute',
                top : 0,
                left : toleft()
            });
        }

        // if (json.center) oUl.find('img').imgAuto(true, {
        //     imgAutoStart: function(img){
        //         if (json.banner && !json.height) {
        //             t.height(_w / img.width * img.height);
        //         }
        //     }
        // });

        move = function (dir){
            if (moveIn) return;
            moveIn = true;
            if (typeof dir != 'undefined') {
                dir == '+'?oIndex++:oIndex--;
            }
            btn.removeClass('in').eq((-oIndex)%(oLL/3)).addClass('in');
            if (css3d) {
                oUl.css({'transform':translate3d(),'transition':'transform '+json.speed+'ms '+json.timing});
            } else{
                oUl.stop(true,true).animate({
                    left : toleft()
                }, json.speed, json.easing, function(){
                    if (oIndex >= 0 ) {
                        oIndex = - oLL/3;
                        oUl.css('left', toleft());
                    }
                    if (oIndex <= - (oLL - json.num)) {
                        oIndex = - (2*oLL/3 - json.num);
                        oUl.css('left', toleft());
                    }
                    moveIn = false;
                });
            }
        }

        banner_l.click(function(){
            move ('+');
        });

        banner_r.click(function(){
            move ('-');
        });

        btn.click(function(e){
            e.stopPropagation();
            moveIn = false;
            oIndex = -oLL/3 - $(this).index();
            move();
        });

        t.on("touchstart",function(e){
            moveIn = false;
            oUl.css('transition','transform 0ms');
            t.roll_stop();
            sx = e.originalEvent.changedTouches[0].clientX;
            downTime = Date.now();
            _l = oUl.position().left;
            _w = t.width()/json.num;
        });
        t.on("touchmove",function(e){
            e.preventDefault();
            ex = e.originalEvent.changedTouches[0].clientX - sx;
            if (ex > _w/3) {
                oUl.css('transform','translate3d('+ ( _l + _w/3 + 2*_w/3 * (1 - (_w/3) / ex) ) +'px, 0px, 0px)');
            }else if(ex < -_w/3) {
                oUl.css('transform','translate3d('+  ( _l - _w/3 - 2*_w/3 * (1 - (-_w/3) / ex) ) +'px, 0px, 0px)');
            }else{
                oUl.css('transform','translate3d('+ (_l + ex) +'px, 0px, 0px)');
            }
        });
        t.on("touchend",function(e){
            outer = false;
            if (oIndex >= 0 && ex > 0) {
                oIndex = -1;
                outer = 1;
            }
            if (oIndex <= - (oLL - json.num) && ex < 0) {
                oIndex = - (oLL - json.num) + 1;
                outer = 2;
            }
            ex = e.originalEvent.changedTouches[0].clientX - sx;
            if ( ex >= _w/3 || (Date.now() - downTime < 300 && ex > 30 || outer == 1) ) {
                move('+');
            }else if( ex < -_w/3 || (Date.now() - downTime < 300 && ex < -30) || outer == 2){
                move('-');
            }else{
                move();
            }
            t.roll_start();
        });

        timer = setInterval(function(){
            move('-');
        }, json.timer);

        suspend.hover(function(){
            t.roll_stop();
        },function(){
            t.roll_start();
        });
        
        t.fadeTo("slow", 1);
        return this;
    };

    $.fn.imgAuto = function(co, fn){
        fn = arguments[1]?arguments[1]:{};
        var fncall = function(name, arg){
            if (typeof fn[name] === 'function') fn[name](arg);
        };
        $(this).each(function(){
            var t = $(this);
            t.css('opacity', 0);
            var img = new Image();
            img.src = t.attr('src');
            var cover = t.attr('img-Auto') == 'cover'||co?true:false,
                _w = t.attr('width'),
                _h = t.attr('height'),
                box,i_w,i_h,b_w,b_h,t_w,t_h;
            if (_w && _h) {
                box = $('<div class="imgAuto_box"></div>');
                box.css({
                    width:_w,
                    height:_h,
                    "text-align":'left',
                    overflow:'hidden'
                });
                t.wrapAll(box);
            } else{
                box = t.parent();
            }
            function move (){
                if (img.width>0 || img.height>0) {
                    fncall('imgAutoStart', img);
                    t.css({'display':'block','margin':0}).parent().css('overflow', 'hidden');
                    i_w = img.width;//原图宽
                    i_h = img.height;//原图高
                    b_w = box.width();//父元素宽
                    b_h = box.height();//父元素高
                    t_w = (b_h/i_h) * i_w;//实际显示的图片宽
                    t_h = (b_w/i_w) * i_h;//实际显示的图片高
                    if ( i_w/i_h < b_w/b_h ) {
                        if (cover) {
                            t.css({'width':'100%','height':'auto'}).css('margin-top', -(t_h-b_h)/2);
                        }else{
                            t.css({'width':'auto','height':'100%'}).css('margin-left', (b_w-t_w)/2);
                        }
                    }else{
                        if (cover) {
                            t.css({'width':'auto','height':'100%'}).css('margin-left', -(t_w-b_w)/2);
                        }else{
                            t.css({'width':'100%','height':'auto'}).css('margin-top', (b_h-t_h)/2);
                        }
                    }
                }else{
                    setTimeout(move);
                }
                fncall('callback', img);
            }
            move();
            t.fadeTo(2000, 1);
            var throttled = yunu.letter(move);
            $(window).resize(throttled);
        });
        return this;
    };
})(jQuery, window, document, undefined);

yunu.rem(320);
$(window).resize(function () {
    yunu.rem(320);
});