<?php

namespace app\cms\admin\controller;

use Doctrine\Inflector\InflectorFactory;
use Illuminate\Database\Schema\Blueprint;
use plugin\admin\app\common\Layui;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use plugin\admin\app\model\Option;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;
use support\Log;

class TableController extends \plugin\admin\app\controller\Base
{
    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['types'];

    protected ?array $generationFiles = null;
    protected string $generatedPrimaryKey = 'id';

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return \app\cms\admin\support\AdminView::raw('table/index');
    }

    /**
     * 查看表
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function view(Request $request): Response
    {
        $table = $request->get('table');
        $table = Util::filterAlphaNum($table);
        $form = Layui::buildForm($table, 'search');
        $table_info = Util::getSchema($table, 'table');
        $primary_key = $table_info['primary_key'][0] ?? null;
        return \app\cms\admin\support\AdminView::raw('table/view', [
            'form' => $form,
            'table' => $table,
            'primary_key' => $primary_key,
        ]);
    }

    /**
     * 查询表
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function show(Request $request): Response
    {
        $tableName = $request->get('table_name', '');
        if (!is_string($tableName)) throw new BusinessException('表名筛选条件无效');
        $limit = self::boundedPageNumber($request->get('limit', 10), 10, 100);
        $page = self::boundedPageNumber($request->get('page', 1), 1, 1000000);
        $offset = ($page - 1) * $limit;
        $database = config('database.connections')['plugin.admin.mysql']['database'];
        $field = $request->get('field', 'TABLE_NAME');
        $allowedColumns = ['TABLE_NAME', 'TABLE_COMMENT', 'ENGINE', 'TABLE_ROWS', 'CREATE_TIME', 'UPDATE_TIME', 'TABLE_COLLATION'];
        if (!is_string($field) || !in_array($field, $allowedColumns, true)) $field = 'TABLE_NAME';
        $order = $request->get('order', 'asc') === 'desc' ? 'desc' : 'asc';
        $db = Util::db();
        $bindings = [$database, '%' . $tableName . '%'];
        $total = $db->select('SELECT count(*) total FROM information_schema.`TABLES` WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE ?', $bindings)[0]->total ?? 0;
        $tables = $db->select("SELECT TABLE_NAME,TABLE_COMMENT,ENGINE,TABLE_ROWS,CREATE_TIME,UPDATE_TIME,TABLE_COLLATION FROM information_schema.`TABLES` WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE ? ORDER BY `$field` $order LIMIT $offset,$limit", $bindings);

        foreach ($tables as $table) {
            // Metadata names are identifiers, not SQL values. MySQL represents
            // a literal backtick in an identifier as two backticks.
            $identifier = str_replace('`', '``', $table->TABLE_NAME);
            $result = $db->select("SELECT COUNT(*) as count FROM `$identifier`");
            $table->TABLE_ROWS = $result[0]->count ?? $table->TABLE_ROWS;
        }

        return json(['code' => 0, 'msg' => 'ok', 'count' => $total, 'data' => $tables]);
    }

    private static function boundedPageNumber($value, int $default, int $maximum): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]{1,9}$/D', $value))) return $default;
        return max(1, min($maximum, (int)$value));
    }

    /**
     * 创建表
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return \app\cms\admin\support\AdminView::raw('table/create', []);
        }
        $data = $request->post();
        $table_name = Util::filterAlphaNum($data['table']);
        $table_comment = Util::pdoQuote($data['table_comment']);
        $columns = $data['columns'];
        $forms = $data['forms'];
        $keys = $data['keys'];

        $primary_key_count = 0;
        foreach ($columns as $index => $item) {
            $columns[$index]['field'] = trim($item['field']);
            if (!$item['field']) {
                unset($columns[$index]);
                continue;
            }
            $columns[$index]['primary_key'] = !empty($item['primary_key']);
            if ($columns[$index]['primary_key']) {
                $primary_key_count++;
            }
            $columns[$index]['auto_increment'] = !empty($item['auto_increment']);
            $columns[$index]['nullable'] = !empty($item['nullable']);
            if ($item['default'] === '') {
                $columns[$index]['default'] = null;
            } else if ($item['default'] === "''") {
                $columns[$index]['default'] = '';
            }
        }

        if ($primary_key_count > 1) {
            throw new BusinessException('不支持复合主键');
        }

        foreach ($forms as $index => $item) {
            if (!$item['field']) {
                unset($forms[$index]);
                continue;
            }
            $forms[$index]['form_show'] = !empty($item['form_show']);
            $forms[$index]['list_show'] = !empty($item['list_show']);
            $forms[$index]['enable_sort'] = !empty($item['enable_sort']);
            $forms[$index]['searchable'] = !empty($item['searchable']);
        }

        foreach ($keys as $index => $item) {
            if (!$item['name'] || !$item['columns']) {
                unset($keys[$index]);
            }
        }

        Util::schemaCreate($table_name, function (Blueprint $table) use ($columns) {
            $type_method_map = Util::methodControlMap();
            foreach ($columns as $column) {
                if (!isset($column['type'])) {
                    throw new BusinessException("请为{$column['field']}选择类型");
                }
                if (!isset($type_method_map[$column['type']])) {
                    throw new BusinessException("不支持的类型{$column['type']}");
                }
                $this->createColumn($column, $table);
            }
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->engine = 'InnoDB';
        });

        Util::dbStatement("ALTER TABLE `$table_name` COMMENT $table_comment");

        // 索引
        Util::schemaTable($table_name, function (Blueprint $table) use ($keys) {
            foreach ($keys as $key) {
                $name = $key['name'];
                $columns = is_array($key['columns']) ? $key['columns'] : explode(',', $key['columns']);
                $type = $key['type'];
                if ($type == 'unique') {
                    $table->unique($columns, $name);
                    continue;
                }
                $table->index($columns, $name);
            }
        });
        $form_schema_map = [];
        foreach ($forms as $item) {
            $form_schema_map[$item['field']] = $item;
        }
        $form_schema_map = json_encode($form_schema_map, JSON_UNESCAPED_UNICODE);
        $this->updateSchemaOption($table_name, $form_schema_map);
        return $this->json(0, 'ok');
    }

    /**
     * 修改表
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function modify(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return \app\cms\admin\support\AdminView::raw('table/modify', ['table' => $request->get('table')]);
        }
        $data = $request->post();
        $old_table_name = Util::filterAlphaNum($data['old_table']);
        $table_name = Util::filterAlphaNum($data['table']);
        $table_comment = $data['table_comment'];
        $columns = $data['columns'];
        $forms = $data['forms'];
        $keys = $data['keys'];
        $primary_key = null;
        $auto_increment_column = null;
        $schema = Util::getSchema($old_table_name);
        $old_columns = $schema['columns'];
        $old_primary_key = $schema['table']['primary_key'][0] ?? null;

        $primary_key_count = $auto_increment_count = 0;
        foreach ($columns as $index => $item) {
            $columns[$index]['field'] = trim($item['field']);
            if (!$item['field']) {
                unset($columns[$index]);
                continue;
            }
            $field = $item['field'];
            $columns[$index]['auto_increment'] = !empty($item['auto_increment']);
            $columns[$index]['nullable'] = !empty($item['nullable']);
            $columns[$index]['primary_key'] = !empty($item['primary_key']);
            if ($columns[$index]['primary_key']) {
                $primary_key = $item['field'];
                $columns[$index]['nullable'] = false;
                $primary_key_count++;
            }
            if ($item['default'] === '') {
                $columns[$index]['default'] = null;
            } else if ($item['default'] === "''") {
                $columns[$index]['default'] = '';
            }
            if ($columns[$index]['auto_increment']) {
                $auto_increment_count++;
                if (!isset($old_columns[$field]) || !$old_columns[$field]['auto_increment']) {
                    $auto_increment_column = $columns[$index];
                    unset($auto_increment_column['old_field']);
                    $columns[$index]['auto_increment'] = false;
                }
            }
        }

        if ($primary_key_count > 1) {
            throw new BusinessException('不支持复合主键');
        }

        if ($auto_increment_count > 1) {
            throw new BusinessException('一个表只能有一个自增字段，并且必须为key');
        }

        foreach ($forms as $index => $item) {
            if (!$item['field']) {
                unset($forms[$index]);
                continue;
            }
            $forms[$index]['form_show'] = !empty($item['form_show']);
            $forms[$index]['list_show'] = !empty($item['list_show']);
            $forms[$index]['enable_sort'] = !empty($item['enable_sort']);
            $forms[$index]['searchable'] = !empty($item['searchable']);
        }

        foreach ($keys as $index => $item) {
            if (!$item['name'] || !$item['columns']) {
                unset($keys[$index]);
            }
        }

        // 改表名
        if ($table_name != $old_table_name) {
            Util::schemaRename($old_table_name, $table_name);
        }

        $type_method_map = Util::methodControlMap();

        foreach ($columns as $column) {
            if (!isset($type_method_map[$column['type']])) {
                throw new BusinessException("不支持的类型{$column['type']}");
            }
            $field = $column['old_field'] ?? $column['field'];
            $old_column = $old_columns[$field] ?? [];
            // 类型更改
            foreach ($old_column as $key => $value) {
                if (key_exists($key, $column) && ($column[$key] != $value || ($key === 'default' && $column[$key] !== $value))) {
                    $this->modifyColumn($column, $table_name);
                    break;
                }
            }
        }

        $table = Util::getSchema($table_name, 'table');
        if ($table_comment !== $table['comment']) {
            $table_comment = Util::pdoQuote($table_comment);
            Util::dbStatement("ALTER TABLE `$table_name` COMMENT $table_comment");
        }

        $old_columns = Util::getSchema($table_name, 'columns');
        Util::schemaTable($table_name, function (Blueprint $table) use ($columns, $old_columns, $keys) {
            foreach ($columns as $column) {
                $field = $column['field'];
                // 新字段
                if (!isset($old_columns[$field])) {
                    $this->createColumn($column, $table);
                }
            }
            // 更新索引名字
            foreach ($keys as $key) {
                if (!empty($key['old_name']) && $key['old_name'] !== $key['name']) {
                    $table->renameIndex($key['old_name'], $key['name']);
                }
            }
        });

        // 找到删除的字段
        $old_columns = Util::getSchema($table_name, 'columns');
        $exists_column_names = array_column($columns, 'field', 'field');
        $old_columns_names = array_column($old_columns, 'field');
        $drop_column_names = array_diff($old_columns_names, $exists_column_names);
        $drop_column_names = Util::filterAlphaNum($drop_column_names);
        foreach ($drop_column_names as $drop_column_name) {
            Util::dbStatement("ALTER TABLE `$table_name` DROP COLUMN `$drop_column_name`");
        }

        $old_keys = Util::getSchema($table_name, 'keys');
        Util::schemaTable($table_name, function (Blueprint $table) use ($keys, $old_keys) {
            foreach ($keys as $key) {
                $key_name = $key['name'];
                $old_key = $old_keys[$key_name] ?? [];
                // 如果索引有变动，则删除索引，重新建立索引
                if ($old_key && ($key['type'] != $old_key['type'] || $key['columns'] != implode(',', $old_key['columns']))) {
                    $old_key = [];
                    unset($old_keys[$key_name]);
                    echo "Drop Index $key_name\n";
                    $table->dropIndex($key_name);
                }
                // 重新建立索引
                if (!$old_key) {
                    $name = $key['name'];
                    $columns = is_array($key['columns']) ? $key['columns'] : explode(',', $key['columns']);
                    $type = $key['type'];
                    if ($type == 'unique') {
                        $table->unique($columns, $name);
                        continue;
                    }
                    echo "Create Index $key_name\n";
                    $table->index($columns, $name);
                }
            }

            // 找到删除的索引
            $exists_key_names = array_column($keys, 'name', 'name');
            $old_keys_names = array_column($old_keys, 'name');
            $drop_keys_names = array_diff($old_keys_names, $exists_key_names);
            foreach ($drop_keys_names as $name) {
                echo "Drop Index $name\n";
                $table->dropIndex($name);
            }
        });

        // 变更主键
        if ($old_primary_key != $primary_key) {
            if ($old_primary_key) {
                Util::dbStatement("ALTER TABLE `$table_name` DROP PRIMARY KEY");
            }
            if ($primary_key) {
                $primary_key = Util::filterAlphaNum($primary_key);
                Util::dbStatement("ALTER TABLE `$table_name` ADD PRIMARY KEY(`$primary_key`)");
            }
        }

        // 一个表只能有一个 auto_increment 字段，并且是key，所以需要在最后设置
        if ($auto_increment_column) {
            $this->modifyColumn($auto_increment_column, $table_name);
        }

        $form_schema_map = [];
        foreach ($forms as $item) {
            $form_schema_map[$item['field']] = $item;
        }
        $form_schema_map = json_encode($form_schema_map, JSON_UNESCAPED_UNICODE);
        $option_name = $this->updateSchemaOption($table_name, $form_schema_map);

        return $this->json(0, $option_name);
    }


    /**
     * 一键菜单
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function crud(Request $request): Response
    {
        return response('代码生成功能已关闭', 403);
    }

    /** Physical storage and logical URLs are independent of application discovery. */
    protected function generationTarget(string $controller, string $model): array
    {
        foreach ([$controller, $model] as $path) {
            if (!preg_match('#^/?(?:[A-Za-z_][A-Za-z0-9_-]*/)+[A-Za-z_][A-Za-z0-9_]*\.php$#D', $path)) throw new \InvalidArgumentException('生成文件路径无效');
            if (preg_match('#^(?:plugin|plugins|weapp|app/plugins|app/weapp)/#i', ltrim($path, '/'))) throw new \InvalidArgumentException('业务插件目录已停用');
        }
        $controller = '/' . ltrim($controller, '/');
        $model = '/' . ltrim($model, '/');
        $controllerPath = dirname($controller);
        $modelPath = dirname($model);
        $controllerClass = pathinfo($controller, PATHINFO_FILENAME);
        $modelClass = pathinfo($model, PATHINFO_FILENAME);
        $cms = str_starts_with($controller, '/app/cms/admin/');
        $app = '';
        if ($cms) {
            if ($controllerPath !== '/app/cms/admin/controller' || $modelPath !== '/app/cms/admin/model' || !str_ends_with($controllerClass, 'Controller')) {
                throw new \InvalidArgumentException('CMS生成目录仅支持 app/cms/admin/controller 和 model');
            }
            $templateBase = '';
            $suffix = 'Controller';
        } else {
            $parts = explode('/', trim($controllerPath, '/'));
            if ($parts[0] === 'app' && count($parts) >= 2 && $parts[1] !== 'cms') {
                if (!str_starts_with($modelPath . '/', '/app/') || str_starts_with($modelPath . '/', '/app/cms/')) throw new \InvalidArgumentException('控制器和模型必须属于主应用');
                $parts = array_slice($parts, 1);
            } else {
                throw new \InvalidArgumentException('控制器路径必须属于CMS或有效应用');
            }
            if (($parts[0] ?? '') !== 'controller') $app = (string)array_shift($parts);
            if (($parts[0] ?? '') !== 'controller' || !str_contains($modelPath . '/', '/model/')) throw new \InvalidArgumentException('控制器或模型目录无效');
            array_shift($parts);
            $templateBase = implode('/', $parts);
            $suffix = (string)config('app.controller_suffix', 'Controller');
        }
        $name = $suffix !== '' && str_ends_with($controllerClass, $suffix) ? substr($controllerClass, 0, -strlen($suffix)) : $controllerClass;
        if ($name === '') throw new \InvalidArgumentException('控制器名称不能为空');
        $name = str_replace('_', '-', InflectorFactory::create()->build()->tableize($name));
        $template = ($templateBase !== '' ? $templateBase . '/' : '') . $name;
        $controllerNamespace = str_replace('/', '\\', trim($controllerPath, '/'));
        $modelNamespace = str_replace('/', '\\', trim($modelPath, '/'));
        return [
            'cms' => $cms,
            'controller_class' => $controllerClass, 'model_class' => $modelClass,
            'controller_namespace' => $controllerNamespace, 'model_namespace' => $modelNamespace,
            'model_reference_namespace' => $cms ? 'plugin\\admin\\app\\model' : $modelNamespace,
            'controller_file' => base_path(ltrim($controller, '/')), 'model_file' => base_path(ltrim($model, '/')),
            'template_path' => $template,
            'template_directory' => $cms ? base_path('app/cms/admin/view/' . $template) : base_path('app/' . ($app ? "$app/" : '') . 'view/' . $template),
            'url' => $cms ? '/app/admin/' . $template : '/' . ($app ? "$app/" : '') . $template,
            'permission_key' => $cms ? 'plugin\\admin\\app\\controller\\' . $controllerClass : $controllerNamespace . '\\' . $controllerClass,
            'legacy_controller_file' => base_path('app/cms/admin/legacy/app/controller/' . $controllerClass . '.php'),
            'legacy_model_file' => base_path('app/cms/admin/legacy/app/model/' . $modelClass . '.php'),
        ];
    }

    protected function legacyClass(string $kind, string $class, string $implementation): string
    {
        return "<?php\nnamespace plugin\\admin\\app\\$kind;\n\n/** Compatibility identity for generated CMS code. */\nclass $class extends \\$implementation\\$class\n{\n}\n";
    }

    protected function writeGeneratedFile(string $file, string $bytes): void
    {
        if ($this->generationFiles === null) throw new \LogicException('生成文件必须经过预演及统一发布');
        if (isset($this->generationFiles[$file]) && $this->generationFiles[$file] !== $bytes) throw new \RuntimeException('生成路径冲突');
        $this->generationFiles[$file] = $bytes;
    }

    /** Called under a process lock. Existing differing source is never overwritten. */
    protected function publishGeneration(array $newRoutes): array
    {
        $files = $this->generationFiles ?? [];
        $registry = base_path('app/cms/admin/config/generated-routes.php');
        $previous = null;
        if ($newRoutes) {
            $this->assertGenerationPath($registry);
            $previous = is_file($registry) ? (string)file_get_contents($registry) : null;
            $routes = $previous === null ? [] : require $registry;
            if (!is_array($routes)) throw new \RuntimeException('生成路由登记格式错误');
            foreach ($newRoutes as $key => $route) {
                if (isset($routes[$key]) && $routes[$key] !== $route) throw new \RuntimeException('已存在不同的生成路由，不覆盖');
                $routes[$key] = $route;
            }
            $this->assertGeneratedRoutes($routes);
            ksort($routes, SORT_STRING);
            $files[$registry] = "<?php\n// Generated CMS routes; controller identities remain stable.\nreturn " . var_export($routes, true) . ";\n";
        }
        foreach ($files as $file => $bytes) {
            $this->assertGenerationPath($file);
            if ($file !== $registry && file_exists($file) && (!is_file($file) || file_get_contents($file) !== $bytes)) throw new \RuntimeException('文件已存在且内容不同，拒绝覆盖：' . substr($file, strlen(base_path()) + 1));
        }
        $published = [];
        try {
            foreach ($files as $file => $bytes) {
                if (is_file($file) && file_get_contents($file) === $bytes) continue;
                \app\cms\Paths::ensureDirectory(dirname($file));
                if ($file === $registry && $previous !== null) {
                    if (file_get_contents($file) !== $previous) throw new \RuntimeException('路由登记在生成期间被修改');
                    $temporary = tempnam(dirname($file), '.cms-routes-');
                    try {
                        if ($temporary === false || file_put_contents($temporary, $bytes, LOCK_EX) !== strlen($bytes) || !rename($temporary, $file)) throw new \RuntimeException('无法写入生成路由');
                    } finally { if ($temporary && is_file($temporary)) unlink($temporary); }
                } else {
                    $handle = @fopen($file, 'x+b');
                    if (!$handle) throw new \RuntimeException('生成目标在写入前已存在');
                    try { if (fwrite($handle, $bytes) !== strlen($bytes)) throw new \RuntimeException('生成文件写入不完整'); }
                    catch (Throwable $error) { fclose($handle); unlink($file); throw $error; }
                    fclose($handle);
                }
                $published[$file] = ['sha256' => hash('sha256', $bytes), 'previous' => $file === $registry ? $previous : null];
            }
            return $published;
        } catch (Throwable $error) {
            $this->rollbackGeneration($published);
            throw $error;
        }
    }

    /** Match the router's case/dash aliases before publishing source or registry files. */
    protected function assertGeneratedRoutes(array $generated): void
    {
        $builtinFile = base_path('app/cms/admin/config/routes.php');
        $this->assertGenerationPath($builtinFile);
        if (!is_file($builtinFile)) throw new \RuntimeException('内置后台路由登记缺失，拒绝生成');
        $builtin = require $builtinFile;
        if (!is_array($builtin)) throw new \RuntimeException('内置后台路由登记格式错误');
        $identities = [];
        $paths = [];
        foreach ([$builtin, $generated] as $registry) {
            foreach ($registry as $identity => $definition) {
                if (!is_string($identity) || !str_starts_with($identity, 'plugin\\admin\\app\\controller\\') || !preg_match('/^[A-Za-z][A-Za-z0-9_]*Controller$/D', substr($identity, strlen('plugin\\admin\\app\\controller\\')))) {
                    throw new \RuntimeException('后台路由权限标识无效');
                }
                $path = $definition['path'] ?? null;
                if (!is_string($path) || !preg_match('#^/app/admin/[a-z][a-z0-9-]*$#D', $path)) throw new \RuntimeException('后台路由地址无效');
                $canonicalIdentity = strtolower($identity);
                $canonicalPath = strtolower(str_replace('-', '', $path));
                if (isset($identities[$canonicalIdentity])) throw new \RuntimeException('生成控制器与已有后台权限标识冲突');
                if (isset($paths[$canonicalPath])) throw new \RuntimeException('生成地址与已有后台路由或别名冲突');
                $identities[$canonicalIdentity] = true;
                $paths[$canonicalPath] = true;
            }
        }
    }

    protected function rollbackGeneration(array $published): void
    {
        foreach (array_reverse($published, true) as $file => $state) {
            if (is_link($file) || !is_file($file) || hash_file('sha256', $file) !== $state['sha256']) continue;
            if ($state['previous'] === null) { unlink($file); continue; }
            $temporary = tempnam(dirname($file), '.cms-routes-');
            try {
                if ($temporary === false || file_put_contents($temporary, $state['previous'], LOCK_EX) !== strlen($state['previous']) || !rename($temporary, $file)) throw new \RuntimeException('生成路由回退失败，请保留现场');
            } finally { if ($temporary && is_file($temporary)) unlink($temporary); }
        }
    }

    protected function assertGenerationPath(string $file): void
    {
        $root = rtrim(base_path(), '/') . '/';
        if (!str_starts_with($file, $root) || str_contains($file, '\\') || str_contains($file, "\0")) throw new \InvalidArgumentException('生成路径超出项目');
        $relative = substr($file, strlen($root));
        if (preg_match('#^(?:plugin|plugins|weapp|app/plugins|app/weapp)/#i', $relative) || preg_match('#(^|/)\.[^/]*(/|$)#', $relative)) throw new \InvalidArgumentException('生成路径包含停用目录或相对跳转');
        $path = rtrim($root, '/');
        foreach (explode('/', $relative) as $part) {
            $path .= '/' . $part;
            if (is_link($path)) throw new \RuntimeException('生成路径不能包含符号链接');
        }
    }


    /**
     * 创建model
     * @param $class
     * @param $namespace
     * @param $file
     * @param $table
     * @return void
     */
    protected function createModel($class, $namespace, $file, $table)
    {
        $this->mkdir($file);
        // 去掉表前缀，因为 Base 模型已经配置了表前缀
        $prefix = Util::getTablePrefix();
        $table_without_prefix = strpos($table, $prefix) === 0 ? substr($table, strlen($prefix)) : $table;
        $table_val = "'$table_without_prefix'";
        $pk = 'id';
        $properties = '';
        $timestamps = '';
        $incrementing = '';
        $columns = [];
        try {
            $database = config('database.connections')['plugin.admin.mysql']['database'];
            //plugin.admin.mysql
            foreach (Util::db()->select('select COLUMN_NAME,DATA_TYPE,COLUMN_KEY,COLUMN_COMMENT from INFORMATION_SCHEMA.COLUMNS where table_name = ? and table_schema = ? order by ORDINAL_POSITION', [$table, $database]) as $item) {
                if ($item->COLUMN_KEY === 'PRI') {
                    $pk = $item->COLUMN_NAME;
                    $item->COLUMN_COMMENT .= '(主键)';
                    if (strpos(strtolower($item->DATA_TYPE), 'int') === false) {
                        $incrementing = <<<EOF
/**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public \$incrementing = false;

EOF;
                    }
                }
                $type = $this->getType($item->DATA_TYPE);
                $properties .= " * @property $type \${$item->COLUMN_NAME} {$item->COLUMN_COMMENT}\n";
                $columns[$item->COLUMN_NAME] = $item->COLUMN_NAME;
            }
        } catch (Throwable $e) {
            throw new \RuntimeException('无法读取数据表结构，未生成代码', 0, $e);
        }
        if (!isset($columns['created_at']) || !isset($columns['updated_at'])) {
            $timestamps = <<<EOF
/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public \$timestamps = false;

EOF;

        }
        $properties = rtrim($properties) ?: ' *';
        $model_content = <<<EOF
<?php

namespace $namespace;

use plugin\admin\app\model\Base;

/**
$properties
 */
class $class extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = $table_val;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected \$primaryKey = '$pk';
    $timestamps
    $incrementing
    
}

EOF;
        $this->generatedPrimaryKey = $pk;
        $this->writeGeneratedFile($file, $model_content);
    }

    /**
     * 创建控制器
     * @param $controller_class
     * @param $namespace
     * @param $file
     * @param $model_class
     * @param $model_namespace
     * @param $name
     * @param $template_path
     * @return void
     */
    protected function createController($controller_class, $namespace, $file, $model_class, $model_namespace, $name, $template_path)
    {
        $model_class_alias = $model_class;
        if (strtolower($model_class) === strtolower($controller_class)) {
            $model_class_alias = "$model_class as {$model_class}Model";
            $model_class = "{$model_class}Model";
        }
        $this->mkdir($file);
        $viewCall = str_starts_with($namespace, 'app\\cms\\admin\\') ? '\app\cms\admin\support\AdminView::render' : 'view';
        $controller_content = <<<EOF
<?php

namespace $namespace;

use support\Request;
use support\Response;
use $model_namespace\\$model_class_alias;
use plugin\admin\app\controller\Crud;
use support\\exception\BusinessException;

/**
 * $name 
 */
class $controller_class extends Crud
{
    
    /**
     * @var $model_class
     */
    protected \$model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        \$this->model = new $model_class;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return $viewCall('$template_path/index');
    }

    /**
     * 插入
     * @param Request \$request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request \$request): Response
    {
        if (\$request->method() === 'POST') {
            return parent::insert(\$request);
        }
        return $viewCall('$template_path/insert');
    }

    /**
     * 更新
     * @param Request \$request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request \$request): Response
    {
        if (\$request->method() === 'POST') {
            return parent::update(\$request);
        }
        return $viewCall('$template_path/update');
    }

}

EOF;
        $this->writeGeneratedFile($file, $controller_content);
    }

    /**
     * 创建控制器
     * @param $template_file_path
     * @param $table
     * @param $template_path
     * @param $url_path_base
     * @param $primary_key
     * @param $controller_class_with_namespace
     * @return void
     */
    protected function createTemplate($template_file_path, $table, $url_path_base, $primary_key, $controller_class_with_namespace)
    {

        $this->mkdir($template_file_path . '/index.html');
        $code_base = Util::controllerToUrlPath($controller_class_with_namespace);
        $code_base = str_replace('/', '.', trim($code_base, '/'));
        $form = Layui::buildForm($table, 'search');
        $html = $form->html(3);
        $html = $html ? <<<EOF
<div class="layui-card">
    <div class="layui-card-body">
        <form class="layui-form top-search-from">
            $html
            <div class="layui-form-item layui-inline">
                <label class="layui-form-label"></label>
                <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="table-query">
                    <i class="layui-icon layui-icon-search"></i>查询
                </button>
                <button type="reset" class="pear-btn pear-btn-md" lay-submit lay-filter="table-reset">
                    <i class="layui-icon layui-icon-refresh"></i>重置
                </button>
            </div>
            <div class="toggle-btn">
                <a class="layui-hide">展开<i class="layui-icon layui-icon-down"></i></a>
                <a class="layui-hide">收起<i class="layui-icon layui-icon-up"></i></a>
            </div>
        </form>
    </div>
</div>
EOF
            : '';
        $html = str_replace("\n", "\n" . str_repeat('    ', 2), $html);
        $js = $form->js(3);

        $table_js = Layui::buildTable($table, 4);
        $template_content = <<<EOF

<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8">
        <title>浏览页面</title>
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/pear/css/pear.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/admin/css/reset.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
    </head>
    <body class="pear-container">
    
        <!-- 顶部查询表单 -->
        $html
        
        <!-- 数据表格 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <table id="data-table" lay-filter="data-table"></table>
            </div>
        </div>

        <!-- 表格顶部工具栏 -->
        <script type="text/html" id="table-toolbar">
            <button class="pear-btn pear-btn-primary pear-btn-xs" lay-event="add" permission="$code_base.insert">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            <button class="pear-btn pear-btn-danger pear-btn-xs" lay-event="batchRemove" permission="$code_base.delete">
                <i class="layui-icon layui-icon-delete"></i>删除
            </button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            <button class="pear-btn pear-btn-xs tool-btn" lay-event="edit" permission="$code_base.update">编辑</button>
            <button class="pear-btn pear-btn-xs tool-btn" lay-event="remove" permission="$code_base.delete">删除</button>
        </script>

        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/layui/layui.js?v=2.13.12'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/pear/pear.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/admin/js/permission.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/admin/js/common.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        
        <script>

            // 相关常量
            const PRIMARY_KEY = "$primary_key";
            const SELECT_API = "$url_path_base/select";
            const UPDATE_API = "$url_path_base/update";
            const DELETE_API = "$url_path_base/delete";
            const INSERT_URL = "$url_path_base/insert";
            const UPDATE_URL = "$url_path_base/update";
            $js
            // 表格渲染
            layui.use(["table", "form", "pearCommon", "pearPopup", "util"], function() {
                let table = layui.table;
                let form = layui.form;
                let $ = layui.$;
                let common = layui.pearCommon;
                let util = layui.util;
                $table_js
                // 编辑或删除行事件
                table.on("tool(data-table)", function(obj) {
                    if (obj.event === "remove") {
                        remove(obj);
                    } else if (obj.event === "edit") {
                        edit(obj);
                    }
                });

                // 表格顶部工具栏事件
                table.on("toolbar(data-table)", function(obj) {
                    if (obj.event === "add") {
                        add();
                    } else if (obj.event === "refresh") {
                        refreshTable();
                    } else if (obj.event === "batchRemove") {
                        batchRemove(obj);
                    }
                });

                // 表格顶部搜索事件
                form.on("submit(table-query)", function(data) {
                    table.reload("data-table", {
                        page: {
                            curr: 1
                        },
                        where: data.field
                    })
                    return false;
                });
                
                // 表格顶部搜索重置事件
                form.on("submit(table-reset)", function(data) {
                    table.reload("data-table", {
                        where: []
                    })
                });
                
                // 字段允许为空
                form.verify({
                    phone: [/(^$)|^1\d{10}$/, "请输入正确的手机号"],
                    email: [/(^$)|^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/, "邮箱格式不正确"],
                    url: [/(^$)|(^#)|(^http(s*):\/\/[^\s]+\.[^\s]+)/, "链接格式不正确"],
                    number: [/(^$)|^\d+$/,'只能填写数字'],
                    date: [/(^$)|^(\d{4})[-\/](\d{1}|0\d{1}|1[0-2])([-\/](\d{1}|0\d{1}|[1-2][0-9]|3[0-1]))*$/, "日期格式不正确"],
                    identity: [/(^$)|(^\d{15}$)|(^\d{17}(x|X|\d)$)/, "请输入正确的身份证号"]
                });

                // 表格排序事件
                table.on("sort(data-table)", function(obj){
                    table.reload("data-table", {
                        initSort: obj,
                        scrollPos: "fixed",
                        where: {
                            field: obj.field,
                            order: obj.type
                        }
                    });
                });

                // 表格新增数据
                let add = function() {
                    layer.open({
                        type: 2,
                        title: "新增",
                        shade: 0.1,
                        maxmin: true,
                        area: [common.isModile()?"100%":"500px", common.isModile()?"100%":"450px"],
                        content: INSERT_URL
                    });
                }

                // 表格编辑数据
                let edit = function(obj) {
                    let value = obj.data[PRIMARY_KEY];
                    layer.open({
                        type: 2,
                        title: "修改",
                        shade: 0.1,
                        maxmin: true,
                        area: [common.isModile()?"100%":"500px", common.isModile()?"100%":"450px"],
                        content: UPDATE_URL + "?" + PRIMARY_KEY + "=" + value
                    });
                }

                // 删除一行
                let remove = function(obj) {
                    return doRemove(obj.data[PRIMARY_KEY]);
                }

                // 删除多行
                let batchRemove = function(obj) {
                    let checkIds = common.checkField(obj, PRIMARY_KEY);
                    if (checkIds === "") {
                        layui.pearPopup.warning("未选中数据");
                        return false;
                    }
                    doRemove(checkIds.split(","));
                }

                // 执行删除
                let doRemove = function (ids) {
                    let data = {};
                    data[PRIMARY_KEY] = ids;
                    layer.confirm("确定删除?", {
                        icon: 3,
                        title: "提示"
                    }, function(index) {
                        layer.close(index);
                        let loading = layer.load();
                        $.ajax({
                            url: DELETE_API,
                            data: data,
                            dataType: "json",
                            type: "post",
                            success: function(res) {
                                layer.close(loading);
                                if (res.code) {
                                    return layui.pearPopup.failure(res.msg);
                                }
                                return layui.pearPopup.success("操作成功", refreshTable);
                            }
                        })
                    });
                }

                // 刷新表格数据
                window.refreshTable = function() {
                    table.reloadData("data-table", {
                        scrollPos: "fixed",
                        done: function (res, curr) {
                            if (curr > 1 && res.data && !res.data.length) {
                                curr = curr - 1;
                                table.reloadData("data-table", {
                                    page: {
                                        curr: curr
                                    },
                                })
                            }
                        }
                    });
                }
            })

        </script>
    </body>
</html>

EOF;
        $this->writeGeneratedFile("$template_file_path/index.html", $template_content);

        $form = Layui::buildForm($table);

        $html = $form->html(5);
        $js = $form->js(3);
        $template_content = <<<EOF
<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>新增页面</title>
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/pear/css/pear.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/jsoneditor/css/jsoneditor.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/admin/css/reset.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
    </head>
    <body>

        <form class="layui-form" action="">

            <div class="mainBox">
                <div class="main-container mr-5">
                    $html
                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit=""
                        lay-filter="save">
                        提交
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        重置
                    </button>
                </div>
            </div>
            
        </form>

        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/layui/layui.js?v=2.13.12'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/pear/pear.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/jsoneditor/jsoneditor.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/admin/js/permission.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        
        <script>
            
            // 相关接口
            const INSERT_API = "$url_path_base/insert";
            $js
            //提交事件
            layui.use(["form", "pearPopup"], function () {
                // 字段验证允许为空
                layui.form.verify({
                    phone: [/(^$)|^1\d{10}$/, "请输入正确的手机号"],
                    email: [/(^$)|^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/, "邮箱格式不正确"],
                    url: [/(^$)|(^#)|(^http(s*):\/\/[^\s]+\.[^\s]+)/, "链接格式不正确"],
                    number: [/(^$)|^\d+$/,'只能填写数字'],
                    date: [/(^$)|^(\d{4})[-\/](\d{1}|0\d{1}|1[0-2])([-\/](\d{1}|0\d{1}|[1-2][0-9]|3[0-1]))*$/, "日期格式不正确"],
                    identity: [/(^$)|(^\d{15}$)|(^\d{17}(x|X|\d)$)/, "请输入正确的身份证号"]
                });
                layui.form.on("submit(save)", function (data) {
                    layui.$.ajax({
                        url: INSERT_API,
                        type: "POST",
                        dateType: "json",
                        data: data.field,
                        success: function (res) {
                            if (res.code) {
                                return layui.pearPopup.failure(res.msg);
                            }
                            return layui.pearPopup.success("操作成功", function () {
                                parent.refreshTable();
                                parent.layer.close(parent.layer.getFrameIndex(window.name));
                            });
                        }
                    });
                    return false;
                });
            });

        </script>

    </body>
</html>

EOF;

        $this->writeGeneratedFile("$template_file_path/insert.html", $template_content);

        $form = Layui::buildForm($table, 'update');


        $html = $form->html(5);
        $js = $form->js(6);
        $template_content = <<<EOF
<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>更新页面</title>
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/pear/css/pear.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/jsoneditor/css/jsoneditor.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        <link rel="stylesheet" href="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/admin/css/reset.css'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" />
        
    </head>
    <body>

        <form class="layui-form">

            <div class="mainBox">
                <div class="main-container mr-5">
                    $html
                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">
                        提交
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        重置
                    </button>
                </div>
            </div>
            
        </form>

        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/layui/layui.js?v=2.13.12'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/pear/pear.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/component/jsoneditor/jsoneditor.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        <script src="<?= htmlspecialchars(\app\cms\AssetManifest::url('/app/admin/admin/js/permission.js'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
        
        <script>

            // 相关接口
            const PRIMARY_KEY = "$primary_key";
            const SELECT_API = "$url_path_base/select" + location.search;
            const UPDATE_API = "$url_path_base/update";
            // 获取数据库记录
            layui.use(["form", "util", "pearPopup"], function () {
                let $ = layui.$;
                $.ajax({
                    url: SELECT_API,
                    dataType: "json",
                    success: function (res) {
                        
                        // 给表单初始化数据
                        layui.each(res.data[0], function (key, value) {
                            let obj = $('*[name="'+key+'"]');
                            if (key === "password") {
                                obj.attr("placeholder", "不更新密码请留空");
                                return;
                            }
                            if (typeof obj[0] === "undefined" || !obj[0].nodeName) return;
                            if (obj[0].nodeName.toLowerCase() === "textarea") {
                                obj.val(value);
                            } else {
                                obj.attr("value", value);
                                obj[0].value = value;
                            }
                            
                            // 多图渲染
                            if (obj[0].classList.contains('uploader-list')) {
                                let multiple_images = value.split(",");
                                $.each(multiple_images, function(index, value) {
                                    $('#uploader-list-'+ key).append(
                                        '<div class="file-iteme">' +
                                        '<div class="handle"><i class="layui-icon layui-icon-delete"></i></div>' +
                                        '<img src='+value +' alt="'+ value +'" >' +
                                        '</div>'
                                    );
                                });
                            }
                        });
                        $js
                        
                        // ajax返回失败
                        if (res.code) {
                            layui.pearPopup.failure(res.msg);
                        }
                        
                    }
                });
            });

            //提交事件
            layui.use(["form", "pearPopup"], function () {
                // 字段验证允许为空
                layui.form.verify({
                    phone: [/(^$)|^1\d{10}$/, "请输入正确的手机号"],
                    email: [/(^$)|^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/, "邮箱格式不正确"],
                    url: [/(^$)|(^#)|(^http(s*):\/\/[^\s]+\.[^\s]+)/, "链接格式不正确"],
                    number: [/(^$)|^\d+$/,'只能填写数字'],
                    date: [/(^$)|^(\d{4})[-\/](\d{1}|0\d{1}|1[0-2])([-\/](\d{1}|0\d{1}|[1-2][0-9]|3[0-1]))*$/, "日期格式不正确"],
                    identity: [/(^$)|(^\d{15}$)|(^\d{17}(x|X|\d)$)/, "请输入正确的身份证号"]
                });
                layui.form.on("submit(save)", function (data) {
                    data.field[PRIMARY_KEY] = layui.url().search[PRIMARY_KEY];
                    layui.$.ajax({
                        url: UPDATE_API,
                        type: "POST",
                        dateType: "json",
                        data: data.field,
                        success: function (res) {
                            if (res.code) {
                                return layui.pearPopup.failure(res.msg);
                            }
                            return layui.pearPopup.success("操作成功", function () {
                                parent.refreshTable();
                                parent.layer.close(parent.layer.getFrameIndex(window.name));
                            });
                        }
                    });
                    return false;
                });
            });

        </script>

    </body>

</html>

EOF;

        $this->writeGeneratedFile("$template_file_path/update.html", $template_content);

    }

    /**
     * 创建目录
     * @param $file
     * @return void
     */
    protected function mkdir($file)
    {
        $this->assertGenerationPath($file);
        if ($this->generationFiles !== null) return;
        \app\cms\Paths::ensureDirectory(dirname($file));
    }


    /**
     * 查询记录
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        $page = $request->get('page', 1);
        $field = $request->get('field');
        $order = $request->get('order', 'asc');
        $table = Util::checkTableName($request->get('table', ''));
        $format = $request->get('format', 'normal');
        $limit = $request->get('limit', $format === 'tree' ? 5000 : 10);
        $allow_column = Util::db()->select("desc `$table`");
  
        $allow_column = array_column($allow_column, 'Field', 'Field');
        if (!in_array($field, $allow_column)) {
            $field = current($allow_column);
        }
        $order = $order === 'asc' ? 'asc' : 'desc';
       
        // 使用智能表操作，自动处理前缀
        $requestParams = $request->get();
        $paginator = Util::smartTableOperation(function($db) use ($table, $requestParams, $allow_column, $field, $order, $limit, $page) {
            $query = $db->table($table);
            foreach ($requestParams as $column => $value) {
                if ($value === '') {
                    continue;
                }
                if (isset($allow_column[$column])) {
                    if (is_array($value)) {
                        if ($value[0] === 'like') {
                            $query = $query->where($column, 'like', "%$value[1]%");
                        } elseif (in_array($value[0], ['>', '=', '<', '<>', 'not like'])) {
                            $query = $query->where($column, $value[0], $value[1]);
                        } else {
                            if ($value[0] !== '' || $value[1] !== '') {
                                $query = $query->whereBetween($column, $value);
                            }
                        }
                    } else {
                        $query = $query->where($column, $value);
                    }
                }
            }
            return $query->orderBy($field, $order)->paginate($limit, '*', 'page', $page);
        });
        $items = $paginator->items();
        if ($format == 'tree') {
            $items_map = [];
            foreach ($items as $item) {
                $items_map[$item->id] = (array)$item;
            }
            $formatted_items = [];
            foreach ($items_map as $index => $item) {
                if ($item['pid'] && isset($items_map[$item['pid']])) {
                    $items_map[$item['pid']]['children'][] = &$items_map[$index];
                }
            }
            foreach ($items_map as $item) {
                if (!$item['pid']) {
                    $formatted_items[] = $item;
                }
            }
            $items = $formatted_items;
        }

        return json(['code' => 0, 'msg' => 'ok', 'count' => $paginator->total(), 'data' => $items]);

    }

    /**
     * 插入记录
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'GET') {
            $table = $request->get('table');
            $form = Layui::buildForm($table);
            return \app\cms\admin\support\AdminView::raw('table/insert', [
                'form' => $form,
                'table' => $table
            ]);
        }
        $table = Util::checkTableName($request->input('table', ''));
        $data = $request->post();
        $allow_column = Util::db()->select("desc `$table`");
        if (!$allow_column) {
            throw new BusinessException('表不存在', 2);
        }
        $columns = array_column($allow_column, 'Type', 'Field');
        foreach ($data as $col => $item) {
            if (!isset($columns[$col])) {
                unset($data[$col]);
                continue;
            }
            // 非字符串类型传空则为null
            if ($item === '' && strpos(strtolower($columns[$col]), 'varchar') === false && strpos(strtolower($columns[$col]), 'text') === false) {
                $data[$col] = null;
            }
            if (is_array($item)) {
                $data[$col] = implode(',', $item);
                continue;
            }
            if ($col === 'password') {
                $data[$col] = Util::passwordHash($item);
            }
        }
        $datetime = date('Y-m-d H:i:s');
        if (isset($columns['created_at']) && empty($data['created_at'])) {
            $data['created_at'] = $datetime;
        }
        if (isset($columns['updated_at']) && empty($data['updated_at'])) {
            $data['updated_at'] = $datetime;
        }
        $id = Util::table($table)->insertGetId($data);
        return $this->json(0, $id);
    }

    /**
     * 更新记录
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'GET') {
            $table = $request->get('table');
            $table_info = Util::getSchema($table, 'table');
            $primary_key = $table_info['primary_key'][0] ?? null;
            $value = htmlspecialchars($request->get($primary_key, ''));
            $form = Layui::buildForm($table, 'update');
            return \app\cms\admin\support\AdminView::raw('table/update', [
                'primary_key' => $primary_key,
                'value' => $value,
                'form' => $form,
                'table' => $table
            ]);
        }
        $table = Util::checkTableName($request->post('table'));
        $table_info = Util::getSchema($table, 'table');
        $primary_keys = $table_info['primary_key'];
        if (empty($primary_keys)) {
            return $this->json(1, '该表没有主键，无法执行更新操作');
        }
        if (count($primary_keys) > 1) {
            return $this->json(1, '不支持复合主键更新');
        }
        $primary_key = $primary_keys[0];
        $value = $request->post($primary_key);
        $data = $request->post();
        $allow_column = Util::db()->select("desc `$table`");
        if (!$allow_column) {
            throw new BusinessException('表不存在', 2);
        }
        $columns = array_column($allow_column, 'Type', 'Field');
        foreach ($data as $col => $item) {
            if (!isset($columns[$col])) {
                unset($data[$col]);
                continue;
            }
            // 非字符串类型传空则为null
            if ($item === '' && strpos(strtolower($columns[$col]), 'varchar') === false && strpos(strtolower($columns[$col]), 'text') === false) {
                $data[$col] = null;
            }
            if (is_array($item)) {
                $data[$col] = implode(',', $item);
            }
            if ($col === 'password') {
                // 密码为空，则不更新密码
                if ($item == '') {
                    unset($data[$col]);
                    continue;
                }
                $data[$col] = Util::passwordHash($item);
            }
        }
        $datetime = date('Y-m-d H:i:s');
        if (isset($columns['updated_at']) && empty($data['updated_at'])) {
            $data['updated_at'] = $datetime;
        }
        Util::table($table)->where($primary_key, $value)->update($data);
        return $this->json(0);
    }

    /**
     * 删除记录
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $table = $request->post('table');
        $table_info = Util::getSchema($table, 'table');
        $primary_keys = $table_info['primary_key'];
        if (empty($primary_keys)) {
            return $this->json(1, '该表没有主键，无法执行删除操作');
        }
        if (count($primary_keys) > 1) {
            return $this->json(1, '不支持复合主键删除');
        }
        $primary_key = $primary_keys[0];
        $value = (array)$request->post($primary_key);
        Util::table($table)->whereIn($primary_key, $value)->delete();
        return $this->json(0);
    }


    /**
     * 删除表
     * @param Request $request
     * @return Response
     */
    public function drop(Request $request): Response
    {
        $tables = $request->post('tables');
        if (!$tables) {
            return $this->json(0, 'not found');
        }
        $prefix = Util::getTablePrefix();
        $table_not_allow_drop = ["{$prefix}admins", "{$prefix}users", "{$prefix}options", "{$prefix}roles", "{$prefix}rules", "{$prefix}admin_roles", "{$prefix}uploads"];
        if ($found = array_intersect($tables, $table_not_allow_drop)) {
            return $this->json(400, implode(',', $found) . '不允许删除');
        }
        foreach ($tables as $table) {
            Util::schemaDrop($table);
            // 删除schema
            Util::table('options')->where('name', "table_form_schema_$table")->delete();
        }
        return $this->json(0, 'ok');
    }

    /**
     * 表摘要
     * @param Request $request
     * @return Response
     */
    public function schema(Request $request): Response
    {
        $table = $request->get('table');
        $data = Util::getSchema($table);

        return $this->json(0, 'ok', [
            'table' => $data['table'],
            'columns' => array_values($data['columns']),
            'forms' => array_values($data['forms']),
            'keys' => array_values($data['keys']),
        ]);
    }

    /**
     * 创建字段
     * @param $column
     * @param Blueprint $table
     * @return mixed
     */
    protected function createColumn($column, Blueprint $table)
    {
        $method = $column['type'];
        $args = [$column['field']];
        if (stripos($method, 'int') !== false) {
            // auto_increment 会自动成为主键
            if ($column['auto_increment']) {
                $column['nullable'] = false;
                $column['default'] = null;
                $args[] = true;
            }
        } elseif (in_array($method, ['string', 'char']) || stripos($method, 'time') !== false) {
            if ($column['length']) {
                $args[] = $column['length'];
            }
        } elseif ($method === 'enum') {
            $args[] = array_map('trim', explode(',', $column['length']));
        } elseif (in_array($method, ['float', 'decimal', 'double'])) {
            if ($column['length']) {
                $args = array_merge($args, array_map('trim', explode(',', $column['length'])));
            }
        } else {
            $column['auto_increment'] = false;
        }

        $column_def = call_user_func_array([$table, $method], $args);
        if (!empty($column['comment'])) {
            $column_def = $column_def->comment($column['comment']);
        }

        if (!$column['auto_increment'] && $column['primary_key']) {
            $column_def = $column_def->primary(true);
        }

        if ($column['auto_increment'] && !$column['primary_key']) {
            $column_def = $column_def->primary(false);
        }
        $column_def = $column_def->nullable($column['nullable']);

        if ($column['primary_key']) {
            $column_def = $column_def->nullable(false);
        }

        if ($method != 'text' && $column['default'] !== null) {
            $column_def->default($column['default']);
        }
        return $column_def;
    }

    /**
     * 更改字段
     * @param $column
     * @param $table
     * @return mixed
     * @throws BusinessException
     */
    protected function modifyColumn($column, $table)
    {
        $table = Util::filterAlphaNum($table);
        $method = Util::filterAlphaNum($column['type']);
        $field = Util::filterAlphaNum($column['field']);
        $old_field = Util::filterAlphaNum($column['old_field'] ?? null);
        $nullable = $column['nullable'];
        $default = $column['default'] !== null ? Util::pdoQuote($column['default']) : null;
        $comment = Util::pdoQuote($column['comment']);
        $auto_increment = $column['auto_increment'];
        $length = (int)$column['length'];

        if ($column['primary_key']) {
            $default = null;
        }

        if ($old_field && $old_field !== $field) {
            $sql = "ALTER TABLE `$table` CHANGE COLUMN `$old_field` `$field` ";
        } else {
            $sql = "ALTER TABLE `$table` MODIFY `$field` ";
        }

        if (stripos($method, 'integer') !== false) {
            $type = str_ireplace('integer', 'int', $method);
            if (stripos($method, 'unsigned') !== false) {
                $type = str_ireplace('unsigned', '', $type);
                $sql .= "$type ";
                $sql .= 'unsigned ';
            } else {
                $sql .= "$type ";
            }
            if ($auto_increment) {
                $column['nullable'] = false;
                $column['default'] = null;
                $sql .= 'AUTO_INCREMENT ';
            }
        } else {
            switch ($method) {
                case 'string':
                    $length = $length ?: 255;
                    $sql .= "varchar($length) ";
                    break;
                case 'char':
                case 'time':
                    $sql .= $length ? "$method($length) " : "$method ";
                    break;
                case 'enum':
                    $args = array_map('trim', explode(',', (string)$column['length']));
                    foreach ($args as $key => $value) {
                        $args[$key] = Util::pdoQuote($value);
                    }
                    $sql .= 'enum(' . implode(',', $args) . ') ';
                    break;
                case 'double':
                case 'float':
                case 'decimal':
                    if (trim($column['length'])) {
                        $args = array_map('intval', explode(',', $column['length']));
                        $args[1] = $args[1] ?? $args[0];
                        $sql .= "$method($args[0], $args[1]) ";
                        break;
                    }
                    $sql .= "$method ";
                    break;
                default :
                    $sql .= "$method ";

            }
        }

        if (!$nullable) {
            $sql .= 'NOT NULL ';
        }

        if ($method != 'text' && $default !== null) {
            $sql .= "DEFAULT $default ";
        }

        if ($comment !== null) {
            $sql .= "COMMENT $comment ";
        }

        echo "$sql\n";
        Util::dbStatement($sql);
    }

    /**
     * 字段类型列表
     * @param Request $request
     * @return Response
     */
    public function types(Request $request): Response
    {
        $types = Util::methodControlMap();
        return $this->json(0, 'ok', $types);
    }


    /**
     * 更新表的form schema信息
     * @param $table_name
     * @param $data
     * @return string
     */
    protected function updateSchemaOption($table_name, $data): string
    {
        $option_name = "table_form_schema_$table_name";
        $option = Option::where('name', $option_name)->first();
        if ($option) {
            Option::where('name', $option_name)->update(['value' => $data]);
        } else {
            Option::insert(['name' => $option_name, 'value' => $data]);
        }
        return $option_name;
    }

    /**
     * 字段类型到php类型映射
     * @param string $type
     * @return string
     */
    protected function getType(string $type): string
    {
        if (strpos($type, 'int') !== false) {
            return 'integer';
        }
        switch ($type) {
            case 'varchar':
            case 'string':
            case 'text':
            case 'date':
            case 'time':
            case 'guid':
            case 'datetimetz':
            case 'datetime':
            case 'decimal':
            case 'enum':
                return 'string';
            case 'boolean':
                return 'integer';
            case 'float':
                return 'float';
            default:
                return 'mixed';
        }
    }

}
