# webman-cms

一个基于 **Webman(Workerman)** 的高性能 CMS 项目，目标是 **复用并兼容 EyouCMS(易优CMS)** 的核心能力与模板生态：

- 运行在常驻内存的 Workerman 事件循环之上，具备 **高并发、低延迟、低资源占用** 的优势
- 尽可能复用 EyouCMS 的数据结构/后台思路/前台模板标签（如 `{eyou:channel}`、`{eyou:arclist}`）
- 通过 ThinkTemplate + 自定义 TagLib，在 Webman 里实现对 EyouCMS 模板的“直接兼容”

<h1>webman官网</h1>
<ul>

  <li>
    <a href="https://webman.workerman.net" target="__blank">文档 / Document</a>
  </li>
 
</ul>


## 特性亮点

- **高性能**：Webman 基于 Workerman，常驻内存、无需反复引导框架，适合高并发场景
- **模板生态复用**：直接使用 `template/pc/*.htm` 等 EyouCMS 风格模板
- **标签兼容**：通过 think-template 的 TagLib 机制支持 `{eyou:xxx}` 模板标签

---



## 演示截图

> 下图为本项目部分功能演示（以仓库图片为准）：

|   | 预览 |
|---|---|
|   | ![](https://www.gzdzh.cn/public/dzh/t0.png) |
|   | ![](https://www.gzdzh.cn/public/dzh/t1.png) |
|   | ![](https://www.gzdzh.cn/public/dzh/t2.png) |
|   | ![](https://www.gzdzh.cn/public/dzh/t3.png) |
|   | ![](https://www.gzdzh.cn/public/dzh/t4.png) |
|   | ![](https://www.gzdzh.cn/public/dzh/t5.png) |

---

## 演示地址

>
- 前台：http://webmancms.dzha.gzdazhihui.cn/
- 后台：http://webmancms.dzha.gzdazhihui.cn/app/admin
  * 账号：test
  * 密码：123456

## 交流 / 反馈

- 扫码添加微信，沟通需求、问题排查、拉群交流：

<img src="https://www.gzdzh.cn/public/dzh/weixin.jpg" width="360" alt="微信沟通/拉群" />

---

## 请作者喝咖啡

如果这个项目对你有帮助，欢迎赞助支持我持续维护与迭代：

<img src="https://www.gzdzh.cn/public/dzh/qcode.png" width="360" alt="赞助/捐赠二维码" />

> 你的支持会用于：功能完善、标签兼容扩展、性能优化、文档与示例补齐。

---

## License

MIT