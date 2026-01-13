<?php

namespace think\template\taglib;

use think\template\TagLib;

/**
 * eyoucms 标签库（前缀：eyou）
 *
 * think-template 会使用类名的小写作为标签前缀（Eyou -> eyou），
 * 因此把原 Base 的实现迁移到这里，模板即可直接使用 {eyou:xxx}。
 */
class Eyou extends TagLib
{
    // 标签定义
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'php'        => ['attr' => ''],
        'channel'    => ['attr' => 'modelid,channelid,typeid,notypeid,type,row,loop,currentstyle,currentclass,id,name,key,empty,mod,titlelen,offset,limit', 'alias' => 'models'],
        'channelartlist' => ['attr' => 'modelid,channelid,typeid,type,row,loop,id,key,empty,titlelen,mod,currentstyle,currentclass', 'alias' => 'modelsartlist'],
        'arclist'    => ['attr' => 'modelid,channelid,typeid,notypeid,keyword,row,loop,offset,titlelen,limit,orderby,ordermode,orderway,noflag,flag,bodylen,infolen,empty,mod,name,id,key,addfields,tagid,pagesize,thumb,joinaid,arcrank,release,idlist,idrange,aid,type,siteall', 'alias' => 'artlist'],
        'arcpagelist' => ['attr' => 'tagid,pagesize,id,tips,loading,callback', 'alias' => 'artpagelist'],
        'list'       => ['attr' => 'modelid,channelid,typeid,notypeid,pagesize,row,loop,keyword,titlelen,orderby,ordermode,orderway,noflag,flag,bodylen,infolen,empty,mod,id,key,addfields,thumb,arcrank,idlist,idrange,siteall'],
        'pagelist'   => ['attr' => 'listitem,listsize', 'close' => 0],
        'position'   => ['attr' => 'symbol,style', 'alias' => 'crumb', 'close' => 0],
        'type'       => ['attr' => 'typeid,type,empty,dirname,id,addfields,addtable'],
        'arcview'    => ['attr' => 'aid,empty,id,addfields,joinaid'],
        'arcclick'   => ['attr' => 'aid,value,type', 'close' => 0],
        'downcount'  => ['attr' => 'aid', 'close' => 0],
        'collectnum' => ['attr' => 'aid', 'close' => 0],
        'freebuynum' => ['attr' => 'aid,modelid,channelid', 'close' => 0],
        'load'       => ['attr' => 'file,href,type,value,basepath', 'close' => 0, 'alias' => ['import,css,js', 'type']],
        'guestbookform' => ['attr' => 'typeid,type,formid,empty,id,mod,key,before,beforeSubmit', 'alias' => 'form'],
        'formreply'   => ['attr' => 'aid,key,limit,row'],
        'assign'     => ['attr' => 'name,value', 'close' => 0],
        'empty'      => ['attr' => 'name'],
        'notempty'   => ['attr' => 'name'],
        'foreach'    => ['attr' => 'name,id,item,key,offset,length,mod', 'expression' => true],
        'volist'     => ['attr' => 'name,id,offset,length,key,mod,limit,row,loop', 'alias' => 'iterate'],
        'if'         => ['attr' => 'condition', 'expression' => true],
        'elseif'     => ['attr' => 'condition', 'close' => 0, 'expression' => true],
        'else'       => ['attr' => '', 'close' => 0],
        'switch'     => ['attr' => 'name', 'expression' => true],
        'case'       => ['attr' => 'value,break', 'expression' => true],
        'default'    => ['attr' => '', 'close' => 0],
        'compare'    => ['attr' => 'name,value,type', 'alias' => ['eq,equal,notequal,neq,gt,lt,egt,elt,heq,nheq', 'type']],
        'ad'         => ['attr' => 'aid,id', 'close' => 1],
        'adv'        => ['attr' => 'pid,row,loop,orderby,where,id,empty,key,mod,currentstyle,currentclass', 'close' => 1],
        'global'     => ['attr' => 'name', 'close' => 0],
        'static'     => ['attr' => 'file,lang,href,code,site,version', 'close' => 0],
        'prenext'    => ['attr' => 'get,titlelen,id,empty', 'alias' => 'beafter'],
        'field'      => ['attr' => 'name,addfields,aid', 'close' => 0],
        'searchurl'  => ['attr' => '', 'close' => 0],
        'searchform' => ['attr' => 'channel,modelid,channelid,typeid,notypeid,flag,noflag,type,empty,id,mod,key', 'close' => 1],
        'tag'        => ['attr' => 'aid,name,row,loop,id,key,mod,typeid,getall,sort,empty,style,type', 'alias' => 'tags'],
        'tagarclist' => ['attr' => 'keyword,row,loop,offset,titlelen,limit,orderby,ordermode,orderway,noflag,flag,bodylen,infolen,empty,mod,name,id,key,addfields,tagid,pagesize,thumb,arcrank', 'alias' => 'tagsartlist'],
        'flink'      => ['attr' => 'type,groupid,row,loop,id,key,mod,titlelen,empty,limit', 'alias' => 'links'],
        'language'   => ['attr' => 'type,row,loop,id,key,mod,titlelen,empty,limit,currentstyle,currentclass'],
        'lang'       => ['attr' => 'name,const', 'close' => 0],
        'ui'         => ['attr' => 'open', 'close' => 0],
        'uitext'     => ['attr' => 'e-id,e-page,id'],
        'uihtml'     => ['attr' => 'e-id,e-page,id'],
        'uiupload'   => ['attr' => 'e-id,e-page,id'],
        'uitype'     => ['attr' => 'e-id,e-page,id,typeid'],
        'uiarclist'  => ['attr' => 'e-id,e-page,id,typeid', 'alias' => 'uiartlist'],
        'uichannel'  => ['attr' => 'e-id,e-page,id,typeid', 'alias' => 'uimodels'],
        'uimap'      => ['attr' => 'e-id,e-page,id,width,height'],
        'uicode'     => ['attr' => 'e-id,e-page,id'],
        'uibackground' => ['attr' => 'e-id,e-page,id,e-img', 'close' => 0],
        'sql'        => ['attr' => 'sql,key,id,mod,cachetime,empty', 'close' => 1, 'level' => 3], // eyou sql 万能标签
        'weapp'      => ['attr' => 'type', 'close' => 0], // 网站应用插件
        'range'      => ['attr' => 'name,value,type', 'alias' => ['in,notin,between,notbetween', 'type']],
        'present'    => ['attr' => 'name'],
        'notpresent' => ['attr' => 'name'],
        'defined'    => ['attr' => 'name'],
        'notdefined' => ['attr' => 'name'],
        'define'     => ['attr' => 'name,value', 'close' => 0],
        'for'        => ['attr' => 'start,end,name,comparison,step'],
        'url'        => ['attr' => 'link,vars,suffix,domain,seo_pseudo,seo_pseudo_format,seo_inlet', 'close' => 0, 'expression' => true],
        'function'   => ['attr' => 'name,vars,use,call'],
        'diyfield'   => ['attr' => 'name,id,key,mod,type,empty,limit'],
        'attribute'  => ['attr' => 'aid,type,row,loop,limit,empty,id,mod,key'],
        'attr'       => ['attr' => 'aid,name', 'close' => 0],
        'user'       => ['attr' => 'type,id,key,mod,empty,currentstyle,currentclass,img,txt,txtid,afterhtml,viewfile'],
        'weapplist'  => ['attr' => 'type,id,key,mod,empty,currentstyle,currentclass'], // 网站应用插件列表
        'usermenu'   => ['attr' => 'row,loop,id,empty,key,mod,currentstyle,currentclass,limit'],
        // 购物行为标签
        'sppurchase' => ['attr' => 'row,loop,id,key,mod,empty,currentstyle,currentclass'],
        // 购物车大标签
        'spcart'     => ['attr' => 'row,loop,id,key,mod,empty,limit'],
        // 订单明细大标签
        'sporder'    => ['attr' => 'row,loop,id,key,mod,empty,limit'],
        // 订单提交大标签
        'spsubmitorder' => ['attr' => 'row,loop,id,key,mod,empty,limit'],
        // 订单管理页大标签
        'sporderlist' => ['attr' => 'row,loop,id,key,mod,empty,limit,pagesize'],
        // 地址标签
        'spaddress'  => ['attr' => 'type,row,loop,id,key,mod,empty,limit'],
        // 订单产品标签
        'spordergoods' => ['attr' => 'row,loop,id,key,mod,empty,limit,name,titlelen'],
        // 订单状态标签
        'spstatus'   => ['attr' => 'row,loop,id,key,mod,empty,limit'],
        // 订单管理页，分页标签
        'sppageorder'  => ['attr' => 'listitem,listsize,pre_text,next_text', 'close' => 0],
        // 订单管理页搜索标签
        'spsearch' => ['attr' => 'empty,id,mod,key'],
        // 商城支付API列表
        'sppayapilist'  => ['attr' => 'id,key,mod,empty'],

        // 筛选搜索
        'screening' => ['attr' => 'empty,id,mod,key,currentstyle,currentclass,addfields,addfieldids,alltxt,typeid,anchor'],
        // 会员列表
        'memberlist' => ['attr' => 'row,loop,titlelen,limit,empty,mod,id,key,orderby,ordermode,orderway,js', 'alias' => 'userslist'],
        // 会员信息
        'memberinfos' => ['attr' => 'mid,users_id,empty,id,addfields', 'alias' => 'usersinfo'],
        //自定义url
        'diyurl'   => ['attr' => 'type,link,vars,suffix,domain,seo_pseudo,seo_pseudo_format,seo_inlet', 'close' => 0],
        // 相关文档
        'likearticle'    => ['attr' => 'modelid,channelid,limit,row,loop,titlelen,bodylen,infolen,mytypeid,typeid,byabs,empty,mod,name,id,key,thumb', 'alias' => 'relevarticle'],
        // 视频播放
        'videoplay'  => ['attr' => 'aid,empty,id,autoplay'],
        // 视频列表
        'videolist'  => ['attr' => 'aid,empty,id,mod,key,autoplay,player'],
        // 获取网站搜索的热门关键字
        'hotwords'        => ['attr' => 'subday,num,id,key,mod,maxlength,empty,orderby,ordermode,orderway,screen', 'alias' => 'hotkeywords'],
        // 插件标签通用
        'weapptaglib'     => ['attr' => 'name,id,offset,length,key,mod,limit,row,loop'],
        // 问答模型问题列表标签通用
        'asklist'     => ['attr' => 'id,key,mod,titlelen,limit,row,loop,orderby,ordermode,orderway'],
        // 专题节点列表标签
        'specnodelist' => ['attr' => 'id,key,mod,empty'],
        // 专题节点文档标签
        'specnode'    => ['attr' => 'aid,code,title,isauto,aidlist,keyword,typeid,row,loop,limit,bodylen,infolen,titlelen,name,empty,mod,id,key,thumb', 'alias' => 'specialnode'],
        'pagespecnode'   => ['attr' => 'listitem,listsize', 'alias' => 'pagespecialnode', 'close' => 0],
        // 收藏标签
        'collect'   => ['attr' => 'aid,collect,cancel,id,class'],
        // 站内通知标签
        'notice'   => ['attr' => 'id'],

        // 商品评价 -- 调用商品整体内容
        'comment' => ['attr' => 'id, aid'],
        // 商品评价 -- 仅循环评价内容
        'commentlist' => ['attr' => 'name, id, offset, length, key, mod, limit, row,loop'],
        // 区域列表
        'citysite'    => ['attr' => 'siteid,nositeid,pid,type,row,loop,currentstyle,currentclass,id,name,key,empty,mod,titlelen,offset,limit'],
        //文章付费阅读标签
        'articlepay'   => ['attr' => 'id'],
        // 导航标签
        'navigation' => ['attr' => 'position_id,row,loop,id,name,key,empty,mod,titlelen,orderby,ordermode,orderway,alltxt,currentstyle,currentclass'],
        //付费下载标签
        'downloadpay'   => ['attr' => 'id'],
        // 常见问题标题
        'faq' => ['attr' => 'group_id,row,loop,orderby,where,id,empty,key,mod,currentstyle,currentclass', 'close' => 1],
    ];

    /**
     * 自动识别构建变量，传值可以使变量也可以是值
     * @access private
     * @param string $value 值或变量
     * @return string
     */
    public function autoBuildVar(string &$name): string
    {
        // 简单实现变量转换，将:var转换为$var
        if (substr($name, 0, 1) == ':') {
            $name = str_replace(':', '$', $name);
        }
        return $name;
    }
    
    private function varOrvalue($value)
    {
        $flag  = substr($value, 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $valueCopy = $value;
            $this->autoBuildVar($valueCopy);
            $value = $valueCopy;
        } else {
            $value = str_replace('"', '\"', $value);
            $value = '"' . $value . '"';
        }

        return $value;
    }

    /**
     * 万能的SQL标签
     */
    public function tagSql($tag, $content)
    {
        $sql = $tag['sql']; // sql 语句
        $sql  = $this->varOrvalue($sql);

        $key    = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod    = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2';
        $id  =  !empty($tag['id']) ? $tag['id'] : 'field'; // 返回的变量
        $cachetime  =  !empty($tag['cachetime']) ? $tag['cachetime'] : ''; // 缓存时间
        $empty  = isset($tag['empty']) ? $tag['empty'] : '';
        $empty  = htmlspecialchars($empty);

        $parseStr = '<?php ';
        $parseStr .= ' $tagSql = new \think\template\taglib\eyou\TagSql;';
        $parseStr .= ' $_result = $tagSql->getSql(' . $sql . ', "' . $cachetime . '");';

        $parseStr .= 'if(is_array($_result) || $_result instanceof \think\Collection || $_result instanceof \think\Paginator): $' . $key . ' = 0; $e = 1;';
        $parseStr .= ' $__LIST__ = $_result;';

        $parseStr .= 'if( count($__LIST__)==0 ) : echo htmlspecialchars_decode("' . $empty . '");';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$' . $id . '): ';
        $parseStr .= '$' . $key . '= intval($key) + 1;?>';
        $parseStr .= '<?php $mod = ($' . $key . ' % ' . $mod . ' ); ?>';
        $parseStr .= $content;
        $parseStr .= '<?php ++$e; ?>';
        $parseStr .= '<?php endforeach; endif; else: echo htmlspecialchars_decode("' . $empty . '");endif; ?>';

        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }

    /**
     * 重置美化标签的变量，以免相互干扰
     */
    private function resetUiVal()
    {
        return '<?php ?>';
    }


    /**
     * 全局变量标签
     * 用法: {eyou:global name='web_name' /}
     */
    public function tagGlobal($tag, $content)
    {
        $name = $tag['name'] ?? '';
        if ($name === '') {
            return '';
        }

        // 采用 "new + 调用" 的方式，避免依赖 Facade
        $parseStr  = '<?php ';
        $parseStr .= '$tagGlobal = new \\think\\template\\taglib\\engine\\TagGlobal;';
        $parseStr .= 'echo $tagGlobal->getGlobal(' . var_export($name, true) . ');';
        $parseStr .= ' ?>';
        return $parseStr;
    }

    /**

     * 导航标签

     * 用法: {eyou:channel typeid='0' row='10' currentstyle='active'}...{/eyou:channel}

     */

    public function tagChannel($tag, $content)
    {
        $modelid   = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : '');
        $modelid  = $this->varOrvalue($modelid);

        $typeid  = !empty($tag['typeid']) ? $tag['typeid'] : '';
        $typeid  = $this->varOrvalue($typeid);

        $notypeid  = !empty($tag['notypeid']) ? $tag['notypeid'] : '';
        $notypeid  = $this->varOrvalue($notypeid);

        $name   = !empty($tag['name']) ? $tag['name'] : '';
        $type   = !empty($tag['type']) ? $tag['type'] : 'son';
        $currentclass   = !empty($tag['currentclass']) ? $tag['currentclass'] : '';
        if (!isset($tag['currentclass'])) { // 加强兼容性
            $currentclass   = !empty($tag['currentstyle']) ? $tag['currentstyle'] : '';
        }
        $id     = isset($tag['id']) ? $tag['id'] : 'field';
        $key    = !empty($tag['key']) ? $tag['key'] : 'i';
        $empty  = isset($tag['empty']) ? $tag['empty'] : '';
        $empty  = htmlspecialchars($empty);
        $mod    = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2';
        $titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100;
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        if (isset($tag['loop'])) $tag['row'] = $tag['loop'];
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 100;
        if (!empty($tag['limit'])) {
            $limitArr = explode(',', $tag['limit']);
            $offset = !empty($limitArr[0]) ? intval($limitArr[0]) : 0;
            $row = !empty($limitArr[1]) ? intval($limitArr[1]) : 0;
        }
 
        $parseStr = '<?php ';
        // 声明变量
        /*typeid的优先级别从高到低：装修数据 -> 标签属性值 -> 外层标签channelartlist属性值*/
        $parseStr .= ' if(isset($ui_typeid) && !empty($ui_typeid)) : $typeid = $ui_typeid; else: $typeid = '.$typeid.'; endif;';
        $parseStr .= ' if(isset($ui_modelid) && !empty($ui_modelid)) : $modelid = $ui_modelid; else: $modelid = '.$modelid.'; endif;';
        $parseStr .= ' $notypeid = '.$notypeid.';'; // 初始化notypeid变量
        $parseStr .= ' if(empty($typeid) && isset($channelartlist["id"]) && !empty($channelartlist["id"])) : $typeid = intval($channelartlist["id"]); endif; ';
        /*--end*/
        $parseStr .= ' if(isset($ui_row) && !empty($ui_row)) : $row = $ui_row; else: $row = '.$row.'; endif;';

        if ($name) { // 从模板中传入数据集
            $symbol     = substr($name, 0, 1);
            if (':' == $symbol) {
                $name = str_replace(':', '$', $name);
                $parseStr .= '$_result=' . $name . ';';
                $name = '$_result';
            } else {
                $name = '$' . $name;
            }

            $parseStr .= 'if(is_array(' . $name . ') || ' . $name . ' instanceof \think\Collection || ' . $name . ' instanceof \think\Paginator): $' . $key . ' = 0; $e = 1;';
            // 设置了输出数组长度
            if (0 != $offset || 'null' != $row) {
                $parseStr .= '$__LIST__ = is_array(' . $name . ') ? array_slice(' . $name . ',' . $offset . ',' . $row . ', true) : ' . $name . '->slice(' . $offset . ',' . $row . ', true); ';
            } else {
                $parseStr .= ' $__LIST__ = ' . $name . ';';
            }

        } else { // 查询数据库获取的数据集
            $parseStr .= ' $tagChannel = new \think\template\taglib\engine\TagChannel;';
            $parseStr .= ' $_result = $tagChannel->getChannel($typeid, "'.$type.'", "'.$currentclass.'", $notypeid, $modelid);';
            $parseStr .= ' if(is_array($_result) || $_result instanceof \think\Collection || $_result instanceof \think\Paginator): $' . $key . ' = 0; $e = 1;';
            // 设置了输出数组长度
            if (0 != $offset || 'null' != $row) {
                $parseStr .= '$__LIST__ = is_array($_result) ? array_slice($_result,' . $offset . ', $row, true) : $_result->slice(' . $offset . ', $row, true); ';
            } else {
                $parseStr .= ' if(intval($row) > 0) :';
                $parseStr .= ' $__LIST__ = is_array($_result) ? array_slice($_result,' . $offset . ', $row, true) : $_result->slice(' . $offset . ', $row, true); ';
                $parseStr .= ' else:';
                $parseStr .= ' $__LIST__ = $_result;';
                $parseStr .= ' endif;';
            }
        }

        $parseStr .= 'if( count($__LIST__)==0 ) : echo htmlspecialchars_decode("' . $empty . '");';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$' . $id . '): ';
        $parseStr .= '$' . $id . '["typename"] = mb_substr($' . $id . '["typename"], 0, '.$titlelen.', "UTF-8");';

        $parseStr .= ' $__LIST__[$key] = $_result[$key] = $' . $id . ';';
        $parseStr .= '$' . $key . '= intval($key) + 1;?>';
        $parseStr .= '<?php $mod = ($' . $key . ' % ' . $mod . ' ); ?>';
        $parseStr .= $content;
        $parseStr .= '<?php ++$e; ?>';
        $parseStr .= '<?php endforeach; endif; else: echo htmlspecialchars_decode("' . $empty . '");endif; ?>';
        $parseStr .= '<?php $'.$id.' = []; ?>'; // 清除变量值，只限于在标签内部使用

        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }

    /**
     * 文章列表标签
     * 用法: {eyou:arclist typeid='1' row='10' titlelen='30' orderby='a.add_time desc'}...{/eyou:arclist}
     */
    public function tagArclist($tag, $content)
    {
        $typeid     = !empty($tag['typeid']) ? $tag['typeid'] : '';
        $typeid  = $this->varOrvalue($typeid);

        $notypeid     = !empty($tag['notypeid']) ? $tag['notypeid'] : '';
        $notypeid  = $this->varOrvalue($notypeid);

        $modelid   = isset($tag['modelid']) ? $tag['modelid'] : (isset($tag['channelid']) ? $tag['channelid'] : '');
        $modelid  = $this->varOrvalue($modelid);

        $addfields     = isset($tag['addfields']) ? $tag['addfields'] : '';
        $addfields  = $this->varOrvalue($addfields);

        $joinaid   = isset($tag['joinaid']) ? $tag['joinaid'] : '';
        $joinaid  = $this->varOrvalue($joinaid);

        $keyword   = isset($tag['keyword']) ? $tag['keyword'] : '';
        $keyword  = $this->varOrvalue($keyword);

        $release   = isset($tag['release']) ? $tag['release'] : 'off';
        $release  = $this->varOrvalue($release);

        $idlist   = isset($tag['idlist']) ? $tag['idlist'] : '';
        $idlist  = $this->varOrvalue($idlist);

        $idrange   = isset($tag['idrange']) ? $tag['idrange'] : '';
        $idrange  = $this->varOrvalue($idrange);
        
        $aid   = isset($tag['aid']) ? $tag['aid'] : '';
        $aid  = $this->varOrvalue($aid);

        $name   = !empty($tag['name']) ? $tag['name'] : '';
        $id     = isset($tag['id']) ? $tag['id'] : 'field';
        $key    = !empty($tag['key']) ? $tag['key'] : 'i';
        $empty  = isset($tag['empty']) ? $tag['empty'] : '';
        $empty  = htmlspecialchars($empty);
        $mod    = !empty($tag['mod']) && is_numeric($tag['mod']) ? $tag['mod'] : '2';
        $orderby    = isset($tag['orderby']) ? $tag['orderby'] : '';
        $ordermode = 'desc';
        if (!empty($tag['ordermode'])) {
            $ordermode = $tag['ordermode'];
        } else {
            if (!empty($tag['orderWay'])) {
                $ordermode = $tag['orderWay'];
            } else {
                $ordermode = !empty($tag['orderway']) ? $tag['orderway'] : $ordermode;
            }
        }
        $flag    = isset($tag['flag']) ? $tag['flag'] : '';
        $noflag    = isset($tag['noflag']) ? $tag['noflag'] : '';
        $tagid    = isset($tag['tagid']) ? $tag['tagid'] : ''; // 标签ID
        $pagesize = !empty($tag['pagesize']) && is_numeric($tag['pagesize']) ? intval($tag['pagesize']) : 0;
        $thumb   = !empty($tag['thumb']) ? $tag['thumb'] : 'on';
        $titlelen = !empty($tag['titlelen']) && is_numeric($tag['titlelen']) ? intval($tag['titlelen']) : 100;
        $bodylen = !empty($tag['bodylen']) && is_numeric($tag['bodylen']) ? intval($tag['bodylen']) : 160;
        if (isset($tag['infolen'])) {
            $bodylen = !empty($tag['infolen']) && is_numeric($tag['infolen']) ? intval($tag['infolen']) : 160;
        }
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        if (isset($tag['loop'])) $tag['row'] = $tag['loop'];
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 10;
        if (!empty($tag['limit'])) {
            $limitArr = explode(',', $tag['limit']);
            $offset = !empty($limitArr[0]) ? intval($limitArr[0]) : 0;
            $row = !empty($limitArr[1]) ? intval($limitArr[1]) : 0;
        }
        $arcrank    = !empty($tag['arcrank']) ? $tag['arcrank'] : 'off';
        $type   = !empty($tag['type']) ? $tag['type'] : '';
        $siteall  = isset($tag['siteall']) ? $tag['siteall'] : null;
        $parseStr = '<?php ';
        // 声明变量
        /*typeid的优先级别从高到低：装修数据 -> 标签属性值 -> 外层标签channelartlist属性值*/
        $parseStr .= ' if(isset($ui_typeid) && !empty($ui_typeid)) : $typeid = $ui_typeid; else: $typeid = '.$typeid.'; endif;';
        $parseStr .= ' if(isset($ui_modelid) && !empty($ui_modelid)) : $modelid = $ui_modelid; else: $modelid = '.$modelid.'; endif;';
        $parseStr .= ' if(empty($typeid) && isset($channelartlist["id"]) && !empty($channelartlist["id"])) : $typeid = intval($channelartlist["id"]); endif; ';
        /*--end*/
        $parseStr .= ' if(isset($ui_row) && !empty($ui_row)) : $row = $ui_row; else: $row = '.$row.'; endif;';

        if ($name) { // 从模板中传入数据集
            $symbol     = substr($name, 0, 1);
            if (':' == $symbol) {
                $name = str_replace(':', '$', $name);
                $parseStr .= '$_result=' . $name . ';';
                $name = '$_result';
            } else {
                $name = '$' . $name;
            }

            $parseStr .= 'if(is_array(' . $name . ') || ' . $name . ' instanceof \think\Collection || ' . $name . ' instanceof \think\Paginator): $' . $key . ' = 0; $e = 1;';
            // 设置了输出数组长度
            if (0 != $offset || 'null' != $row) {
                $parseStr .= '$__LIST__ = is_array(' . $name . ') ? array_slice(' . $name . ',' . $offset . ',' . $row . ', true) : ' . $name . '->slice(' . $offset . ',' . $row . ', true); ';
            } else {
                $parseStr .= ' $__LIST__ = ' . $name . ';';
            }

        } else { // 查询数据库获取的数据集
            $parseStr .= ' $param = array(';
            $parseStr .= '      "typeid"=> $typeid,';
            $parseStr .= '      "notypeid"=> '.$notypeid.',';
            $parseStr .= '      "flag"=> "'.$flag.'",';
            $parseStr .= '      "noflag"=> "'.$noflag.'",';
            $parseStr .= '      "channel"=> $modelid,';
            $parseStr .= '      "joinaid"=> '.$joinaid.',';
            $parseStr .= '      "keyword"=> '.$keyword.',';
            $parseStr .= '      "release"=> '.$release.',';
            $parseStr .= '      "idlist"=> '.$idlist.',';
            $parseStr .= '      "idrange"=> '.$idrange.',';
            $parseStr .= '      "aid"=> '.$aid.',';
            $parseStr .= ' );';
            $parseStr .= ' $tag = '.var_export($tag,true).';';
            $parseStr .= ' $tagArclist = new \think\template\taglib\engine\TagArclist;';
            $parseStr .= ' $_result = $tagArclist->getArclist($param, $row, "'.$orderby.'", '.$addfields.',"'.$ordermode.'","'.$tagid.'",$tag,"'.$pagesize.'","'.$thumb.'","'.$arcrank.'","'.$type.'","'.$siteall.'");';

            $parseStr .= 'if(!empty($_result["list"]) && (is_array($_result["list"]) || $_result["list"] instanceof \think\Collection || $_result["list"] instanceof \think\Paginator)): $' . $key . ' = 0; $e = 1;';
            // 设置了输出数组长度
            if (0 != $offset || 'null' != $row) {
                $parseStr .= ' $__LIST__ = is_array($_result["list"]) ? array_slice($_result["list"],' . $offset . ', $row, true) : $_result["list"]->slice(' . $offset . ', $row, true); ';
            } else {
                $parseStr .= ' $__LIST__ = $_result["list"];';
            }
            $parseStr .= ' $__TAG__ = $_result["tag"];';
        }
        $parseStr .= 'if( count($__LIST__)==0 ) : echo htmlspecialchars_decode("' . $empty . '");';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$' . $id . '): ';
        $parseStr .= '$aid = $'.$id.'["aid"] ?? 0;';
        $parseStr .= '$users_id = $'.$id.'["users_id"] ?? 0;';
        // 使用mb_substr替代text_msubstr，模拟EyouCMS的text_msubstr行为
        $parseStr .= '$' . $id . '["title"] = mb_substr($' . $id . '["title"] ?? "", 0, '.$titlelen.', "UTF-8");';
        $parseStr .= 'if(($' . $id . '["is_b"] ?? 0) == 1) : $' . $id . '["title"] = "<strong>".$' . $id . '["title"]."</strong>";endif;';
        // 对于seo_description，如果实际长度超过截取长度，添加省略号
        $parseStr .= '$desc = $' . $id . '["seo_description"] ?? "";';
        $parseStr .= '$' . $id . '["seo_description"] = mb_substr($desc, 0, '.$bodylen.', "UTF-8");';
        $parseStr .= 'if(mb_strlen($desc, "UTF-8") > '.$bodylen.') : $' . $id . '["seo_description"] .= "...";endif;';

        $parseStr .= '$' . $key . '= intval($key) + 1;?>';
        $parseStr .= '<?php $mod = ($' . $key . ' % ' . $mod . ' ); ?>';
        $parseStr .= $content;
        $parseStr .= '<?php ++$e; ?>';
        $parseStr .= '<?php $aid = 0; ?>';
        $parseStr .= '<?php $users_id = 0; ?>';
        $parseStr .= '<?php endforeach; endif; else: echo htmlspecialchars_decode("' . $empty . '");endif; ?>';
        $parseStr .= '<?php $'.$id.' = []; ?>'; // 清除变量值，只限于在标签内部使用

        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }
}
