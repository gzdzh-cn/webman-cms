window.rootPath = (function (src) {
	src = document.currentScript
		? document.currentScript.src
		: document.scripts[document.scripts.length - 1].src;
	return src.substring(0, src.lastIndexOf("/") + 1);
})();

layui.config({
	base: rootPath + "module/",
	version: "3.10.0"
}).extend({
	pearAdmin: "pearAdmin", 	// 框架布局组件
	pearMenu: "pearMenu",		// 数据菜单组件
	pearFrame: "pearFrame", 	// 内容页面组件
	pearTab: "pearTab",			// 多选项卡组件
	pearEcharts: "pearEcharts", // 数据图表组件
	pearEchartsTheme: "pearEchartsTheme", // 数据图表主题
	pearEncrypt: "pearEncrypt",		// 数据加密组件
	pearSelect: "pearSelect",	// 下拉多选组件
	xmSelect: "xm-select",	// 下拉多选组件 //变更
	pearDrawer: "pearDrawer",	// 抽屉弹层组件
	pearNotice: "pearNotice",	// 消息提示组件
	pearStep:"pearStep",		// 分布表单组件
	pearTag:"pearTag",			// 多标签页组件
	pearPopup:"pearPopup",      // 弹层封装
	pearTreetable:"pearTreetable",   // 树状表格
	pearDtree:"pearDtree",			// 树结构
	tinymce:"tinymce/tinymce", // 编辑器
	pearArea:"pearArea",			// 省市级联  
	pearCount:"pearCount",			// 数字滚动
	pearTopBar: "pearTopBar",		// 置顶组件
	pearButton: "pearButton",		// 加载按钮
	pearDesign: "pearDesign",		// 表单设计
	pearCard: "pearCard",			// 数据卡片组件
	pearLoading: "pearLoading",		// 加载组件
	pearCropper:"pearCropper",		// 裁剪组件
	pearConvert:"pearConvert",		// 数据转换
	pearYaml:"pearYaml",			// yaml 解析组件
	pearContext: "pearContext",		// 上下文组件
	pearHttp: "pearHttp",			// ajax请求组件
	pearTheme: "pearTheme",			// 主题转换
	pearMessage: "pearMessage",     // 通知组件
	pearToast: "pearToast",         // 消息通知
	pearIconPicker: "pearIconPicker",// 图标选择
	pearNprogress: "pearNprogress",  // 进度过渡
	pearCommon: "pearCommon",		// 常用封装类
	watermark:"watermark/watermark", //水印
	pearFullscreen:"pearFullscreen",  //全屏组件
	popover:"popover/popover" //汽泡组件
}).use(['layer', 'pearTheme'], function () {
	layui.pearTheme.changeTheme(window, false);
});