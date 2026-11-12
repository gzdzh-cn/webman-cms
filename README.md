# Webman CMS

基于 Webman 重构 EyouCMS 1.7.7 企业官网，兼容受支持的原模板标签、业务 ID 与资源 URL。支持文章、单页、展示产品、栏目、表单、多语言、模板管理及 SEO；会员和商城交易不在范围内。

2026-11-07：开发站 8787 已完成本轮停机迁移并重启，新架构已运行。旧状态已逐项核验入库，整个 runtime 已清除并自动重建；8793 保留副本未改动、未同步。迁移后 HTTP 复查与当前管理员登录后的主要页面读取、筛选和打开／返回验收已通过。本轮架构迁移验收完成；未扩大为全部角色、业务写入或 EyouCMS 严格兼容验收。

当前安全边界：业务插件市场与运行时加载、可视化编辑和通过 Web 生成 PHP 代码均已关闭；`plugin\admin` 只是保留的后台兼容身份。模板在安装和启用前扫描，企业官网模板中的不支持内容会阻止启用；旧会员／支付页面的问题仅提示，访问时仍受模板安全策略限制。历史数据保留。Composer 框架依赖及其配置继续使用。

## 启动与安装

构建二进制、源码启动、二进制启动及 Docker ARM64 命令见根目录 [构建与启动说明](BUILD.md)。

运行需要 PHP ≥ 8.2、Composer 锁定依赖和 MySQL。本机显式使用以下 PHP，避免 shell 默认旧版本：

```sh
cd /Volumes/disk/site/php/dzhcms/dzhcms002/webman-cms
/Users/lizheng/Library/FlyEnv/env/php/bin/php start.php start
```

开发站为 `http://127.0.0.1:8787/`，后台为 `/web-admin`。新站通过 `/install` 安装，默认空站，由安装者设置管理员账号和密码；已有站使用迁移流程，禁止重新导入基线覆盖。部署、停机迁移和缓存清理见[部署说明](docs/deployment.md)与[数据库说明](docs/database.md)。

## 目录约定

- `app/cms/` 集中业务、标签兼容、安装、维护和私有资源；框架接入层及后台视图集中维护；业务插件不再加载。
- `template/<主题>/pc|mobile/` 保存原版主题，官方默认主题为 `default`。
- `public/uploads/` 保存上传实体，兼容旧 URL；`public/html/` 专放生成 HTML。
- `install/` 保存安装页面、资源及初始 SQL，安装完成可删除；后续升级代码在 `app/cms/database/`。
- `runtime/` 在状态迁移完成后可停机整删，启动自动重建；会话失效，业务与发布状态在数据库中保留。
- `docs/` 不参与运行；`.git` 留在项目内，运行版本不受旧 10MB 源码包限制。

根目录不再保留 `data`、`resources`、`scripts`、`sql`。完整职责与路径映射见[架构说明](docs/architecture.md)。

## 开发与验收

- [协作规范](AGENTS.md)、[维护与外部归档](docs/maintenance-archive.md)。
- [离线标签手册](docs/eyoucms/labels.md)、[六阶段路线](docs/eyoucms/roadmap.md)。
- [当前交接](docs/eyoucms/current-handoff.md)、[剩余验收项](docs/eyoucms/acceptance-gaps.md)。
- [历史源码包与资源恢复](docs/source-package.md)。

已有企业官网及具名模板的有限验收不代表全部官方主题完全兼容。T17、部分权限与富文本边界、未提供主题和最终综合回归继续开放。8793 保留副本独立管理，开发修改不自动发布。

## 许可

MIT，保留原版权及第三方组件许可；详见 `LICENSE` 和对应源码声明。
