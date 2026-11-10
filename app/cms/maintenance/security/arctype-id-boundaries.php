<?php
declare(strict_types=1);

// Run as a standalone process: php app/cms/maintenance/security/arctype-id-boundaries.php
namespace app\cms\compat {
    class Model {}
    class Db {
        public static array $results = [];
        public static function name($name) { return new class {
            public function field($v) { return $this; }
            public function where($v) { return $this; }
            public function select() { return array_shift(Db::$results); }
        }; }
    }
    function request() { return new class { public function module() { return 'home'; } }; }
}
namespace {
    $root = dirname(__DIR__, 4);
    require $root . '/vendor/autoload.php';
    require $root . '/app/cms/compat/model/LanguageAttr.php';
    function is_language() { return $GLOBALS['language_enabled']; }
    function get_main_lang() { return 'cn'; }
    function get_home_lang() { return 'en'; }
    function get_arr_column($rows, $key) { return array_column($rows, $key); }
    function check($ok, $message) { if (!$ok) throw new \RuntimeException($message); }

    $model = new \app\cms\compat\model\LanguageAttr();
    $GLOBALS['language_enabled'] = true;
    $source = [['attr_name'=>'attr_1']];
    foreach ([
        ['1', [['attr_name'=>'attr_1','attr_value'=>'7']], '7'],
        ['1,2', [['attr_name'=>'attr_1','attr_value'=>'7'],['attr_name'=>'attr_2','attr_value'=>'8']], '7,8'],
        ['1', [['attr_name'=>'attr_1','attr_value'=>"7) OR 1=1 -- "]], ''],
        ['1,2', [['attr_name'=>'attr_1','attr_value'=>'7'],['attr_name'=>'attr_2','attr_value'=>'8,9) OR 1=1']], ''],
    ] as [$input, $stored, $expected]) {
        \app\cms\compat\Db::$results = [$source, $stored];
        check($model->getBindValue($input, 'arctype') === $expected, 'association ID validation failed');
    }
    $GLOBALS['language_enabled'] = false;
    check($model->getBindValue('1,2', 'arctype') === '1,2', 'single-language IDs changed');
    check($model->getBindValue('1 OR 1=1', 'arctype') === '', 'invalid input survived');
    check($model->getBindValue(['id'=>1], 'arctype') === ['id'=>1], 'array contract changed');

    $policy = \app\cms\template\CompiledTemplatePolicy::class;
    $policy::validate('<?php echo gettoptype($id, "typename");');
    foreach (['getAllArctype', 'getAllArctypeCount', 'getparenttype'] as $function) {
        $rejected = false;
        try { $policy::validate('<?php echo ' . $function . '($id);'); }
        catch (\InvalidArgumentException $e) { $rejected = true; }
        check($rejected, $function . ' is available to compiled templates');
    }
    echo "category association IDs and template function boundaries OK\n";
}
