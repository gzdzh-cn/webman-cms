# Request 环境变量存在性有限验收（2026-11-09）

本批仅修改 `app/cms/compat/Request.php` 的 `has(..., 'env', ...)` 分支。原版 EyouCMS 1.7.7 只读，数据库、安装、UI及模板原文件均不修改。隔离工作与证据目录为项目外 `request-env-20261109-012708`。

## 修复行为

此前环境变量存在性检查走单值读取及类型转换：嵌套数组可能抛异常，小写键被自动转大写，布尔值经字符串转换后改变“非空”判断。现在保留原名逐段 `isset`，只将严格空字符串视为空。原版的惰性特征同样保留：存储为空时先调用全量 `env()` 过滤，初始化后直接检查原始存储。因此默认 `htmlspecialchars` 下的 `false` 在首次非空检查为false、后续为true；null首次可经过过滤成为空串、后续原始null仍视为不存在。过滤器不得在每次存在性检查时重复调用。

存储仍使用当前请求 `memo['request.env']`，无上下文时使用实例fallback。保留同一个Request对象跨请求也不会保留上一请求的setter；不写入 `$_ENV` 或 `getenv`。现有 `input('?env.NEST.x')` 和两套标签调用方式不变。

## 验收与边界

- `verification/original.json`、`before.json`、`after.json`、`report.json`：实际原类反射调用，PHP7.2.34与PHP8.4.23共102组序列。修前12组完全一致，修后102组的值、类型、异常、诊断和过滤调用序列一致。覆盖cold/warm、数组setter后读取、false/true/0/空串/null/数组、大小写及三种过滤设置。不是原完整框架或HTTP证据。
- `verification/regression-result.json`：既有101组Request方法回归全部保持。该组与本次102组分开记录，不累计为兼容率。
- `verification/integration-result.json`：69项实际CmsContext/Input/TemplateRenderer检查，包括两种模式冷/热编译、保留Request实例、48个连续独立请求上下文、空来源重载及不修改进程环境。没有数据库或原框架HTTP，不冒充真正并发worker验收。
- `verification/independent/`：无context fallback 102组差分、实际原input与候选Input的15组105次调用、13项跨context/实例隔离断言均通过；受控request/config/context桥且关闭city，不是全框架。独立复核未发现本次阻塞问题。生产T17七份实现保持原SHA；未尝试重启已拒绝的比较原型。

不直接复制 `checkedUrlParams`：原版入口/搜索模板豁免与请求生命周期不同，仍需分开验证。其他来源数组setter、session/file行为、SQL profile、原完整框架HTTP及T16总体保持开放。T17静态别名与GC可观察副作用没有通用安全修复；历史失败不覆盖。原40任务与22条生命周期要求、ID和顺序不修改。

## 发布、保护与回退

2026-11-09T01:34:14 已停止8787全部worker，发布1份PHP与6份说明文件，再启动双HTTP worker。11个匿名入口的状态与发布前一致、无原始PHP错误；管理员/角色关联/角色/权限节点/可信配置5表只读摘要保持（排除正常登录时间）。1638项主题/共享资源/上传/已有HTML、环境、Git索引及T17七份实现SHA保持，没有未登记文件变化。保留runtime及会话。本批未启动测试数据库或修改其他隔离站，8793未操作。无UI改动，未新增浏览器验收声明；源方法/模板证据不冒充生产HTTP的环境变量差分。

发布仅针对本批显式文件，先检查8787基线SHA并保存项目外前镜像，停止该项目全部worker，原子替换并按原环境启动。保留runtime会话，不清数据库、安装记录或上传。回退先停8787，再按前镜像和SHA恢复本批文件；后续有人工改动时拒绝覆盖。不会整库回退或覆盖新增内容。
