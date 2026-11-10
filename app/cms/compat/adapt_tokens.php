<?php
// Maintenance tool: normalize dynamic constant references in ported source tokens.
require dirname(__DIR__, 3) . '/vendor/autoload.php';
$dynamic=['ROOT_DIR','TPL_THEME','THEME_STYLE','THEME_STYLE_PATH','MODULE_NAME','CONTROLLER_NAME','ACTION_NAME'];
$manifest=json_decode(file_get_contents(__DIR__.'/source-manifest.json'),true);
foreach($manifest as $entry) {
    $path=dirname(__DIR__,3).'/'.$entry['target']; $source=file_get_contents($path);$out='';$previous=null;
    foreach(token_get_all($source) as $token) {
        if(is_array($token)) {
            [$type,$text]=$token;
            if($type===T_STRING&&in_array($text,$dynamic,true)&&!in_array($previous,[T_OBJECT_OPERATOR,T_DOUBLE_COLON,T_FUNCTION,T_CONST],true)) $text='\\app\\cms\\compat\\Runtime::constant('.var_export($text,true).')';
            $out.=$text; if(!in_array($type,[T_WHITESPACE,T_COMMENT,T_DOC_COMMENT],true))$previous=$type;
        } else {$out.=$token;if(trim($token)!=='')$previous=$token;}
    }
    file_put_contents($path,$out);
}
