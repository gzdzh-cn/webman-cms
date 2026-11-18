<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace app\cms\compat\template\driver;
use function app\cms\compat\config;
use function app\cms\compat\input;
use function app\cms\compat\request;
use function app\cms\compat\cache;
use function app\cms\compat\cookie;
use function app\cms\compat\session;
use function app\cms\compat\model;
use function app\cms\compat\M;
use function app\cms\compat\lang;
use app\cms\compat\Runtime;


use app\cms\compat\Exception;

class File
{
    protected $cacheFile;
    private \app\cms\security\TemplateCache $secureCache;

    public function __construct(string $mode = 'eyou', int $lifetime = 0)
    {
        $scope = json_encode([$mode, \app\cms\template\TemplateDialect::VERSION, \app\cms\template\TemplateCapabilities::COMPILE_VERSION,
            \app\cms\template\TemplateMode::siteSignature()], JSON_THROW_ON_ERROR);
        $this->secureCache = new \app\cms\security\TemplateCache('template.front', $scope, $lifetime);
    }

    /** Validate metadata and the compiled AST before dependency parsing. */
    public function verifiedContent(string $cacheFile): ?string { return $this->secureCache->read($cacheFile); }


    /*
     * fwrite 方式写入编译文件 by 许
     */
    public function fwrite($cacheFile, $content)
    {
        $this->write($cacheFile, $content);
    }

    /**
     * 写入编译缓存
     * @param string $cacheFile 缓存的文件名
     * @param string $content 缓存的内容
     * @return void|array
     */
    public function write($cacheFile, $content)
    {
        $this->secureCache->write((string)$cacheFile, Runtime::rewriteCompiled((string)$content));
    }

    /**
     * 读取编译编译
     * @param string  $cacheFile 缓存的文件名
     * @param array   $vars 变量数组
     * @return void
     */
    public function read($cacheFile, $vars = [])
    {
        $bytes = $this->verifiedContent((string)$cacheFile);
        if ($bytes === null) throw new Exception('模板缓存完整性校验失败');
        \app\cms\security\CompiledSnapshot::render($bytes, (array)$vars, function (string $snapshotUri, array $snapshotVars): void {
        $this->cacheFile = $snapshotUri;
        $cacheFile = $snapshotUri;
        // PHP 7 returned empty for absent/numeric/boolean scalar offsets and
        // missing string positions. PHP 8 raises warnings that Webman throws.
        // Native indirect writes to by-value ArrayAccess results likewise only
        // produced a hidden notice upstream; keep that compiled-file boundary.
        // Only compiled-template reads receive the legacy empty-value behavior;
        // helpers/models and all other warnings retain the caller's handler.
        // Includes render synchronously: this scope must not yield a coroutine.
        $previous = null;
        $previous = set_error_handler(static function ($severity, $message, $file, $line) use ($cacheFile, &$previous) {
            if (($severity === E_WARNING || $severity === E_NOTICE) && $file === $cacheFile
                && preg_match('/^(?:Undefined variable \\$|Undefined array key |Trying to access array offset on (?:value of type )?(?:null|bool|int|float|true|false)$|Uninitialized string offset:? -?[0-9]+$|Indirect modification of overloaded element of .+ has no effect$|Only variables should be passed by reference$)/', $message)) {
                return true;
            }
            return is_callable($previous) ? $previous($severity, $message, $file, $line) : false;
        });
        try {
            $this->includeTemplate($snapshotVars);
        } finally {
            restore_error_handler();
        }
        });
    }

    /** Keep template extraction from replacing the handler/restoration locals. */
    private function includeTemplate($vars)
    {
        if (!empty($vars) && is_array($vars)) {
            // 模板阵列变量分解成为独立变量
            extract($vars, EXTR_SKIP);
        }
        //载入模版缓存文件
        include $this->cacheFile;
    }

    /**
     * 检查编译缓存是否有效
     * @param string  $cacheFile 缓存的文件名
     * @param int     $cacheTime 缓存时间
     * @return boolean
     */
    public function check($cacheFile, $cacheTime)
    {
        // 缓存文件不存在, 直接返回false
        if ($this->verifiedContent((string)$cacheFile) === null) {
            return false;
        }
        return true;
    }
}
