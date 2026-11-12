# webman-cms 1.0.0 版本展示发布

工作台原“后台组件版本”改为“webman-cms 版本”，数值单独显示`1.0.0`。版本数据沿用`app/cms/resources/backend-provenance.json`，项目名同步为`webman-cms`，由BackendVersion读取。原官方后台来源、许可证及应用兼容版本保持不变，Webman和Workerman框架版本仍从Composer读取。

两份PHP语法检查、元数据读取、差异空白检查通过，2,132个公开资源由构建校验通过；无公开资源字节变化。Linux x86_64成品使用缓存的PHP 8.4.27运行时，构建约6.2秒。成品SHA256：`5eac53a7b58608e4064a6ca7e2902ea534bfddd61823bf4a495daeaa448b20e7`。

发布前核对线上二进制前镜像及准确进程，备份位于`/www/backup/cms-version-100-20261112-mgT6RG`，本机三份改动前镜像位于`/private/tmp/cms-version-100-20261112/before`。原生服务器runtime/deployment预检通过后，替换程序并经宝塔3144原启动脚本完整重启；面板PID与程序PID均为2150777。公网登录页、官网首页和真实验证码只读请求通过，配置SHA一致。未提交真实登录，工作台实际浏览器截图未验；版本文字依据实际代码及元数据读取检查。

保留主题、缓存模式、私有配置、业务数据、会话、外置资源及Git暂存；未操作8793或用户Redis管理接口。Git暂存SHA256仍为`ffbc2eb7bdbc99604e34c8b593c90f8a2df0730b90690a4a013e4d940dc34b6d`。回退使用本批原二进制并经相同宝塔脚本重新启动，不恢复业务目录。
