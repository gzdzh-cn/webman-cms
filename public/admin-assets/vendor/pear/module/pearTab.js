layui.define(['jquery', 'element'], function(exports) {
	"use strict";

	var MOD_NAME = 'pearTab',
		$ = layui.jquery,
		element = layui.element;

	var pearTab = function(opt) {
		this.option = opt;
	};

	var tabData = new Array();
	var tabDataCurrent = 0;
	var contextTabDOM;

	function escapeHtml(value) {
		return String(value).replace(/[&<>"']/g, function(character) {
			return {"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[character];
		});
	}

	function normalizeTab(item) {
		if (!item || typeof item !== "object" || Array.isArray(item)
			|| !["string", "number"].includes(typeof item.id) || String(item.id) === ""
			|| typeof item.url !== "string" || typeof item.title !== "string") return null;
		return Object.assign({}, item, {id: String(item.id)});
	}

	function normalizeTabs(items) {
		if (!Array.isArray(items)) return null;
		var result = [], ids = new Set();
		for (var i = 0; i < items.length; i++) {
			var item = normalizeTab(items[i]);
			if (!item) return null;
			if (!ids.has(item.id)) { result.push(item); ids.add(item.id); }
		}
		return result;
	}

	function storageGet(key) {
		try { return sessionStorage.getItem(key); } catch (error) { return null; }
	}

	function storageSet(key, value) {
		try { sessionStorage.setItem(key, value); } catch (error) { /* Keep the active page usable. */ }
	}

	function storedTabs(elem) {
		try { return normalizeTabs(JSON.parse(storageGet(elem + "-pear-tab-data"))); }
		catch (error) { return null; }
	}

	pearTab.prototype.render = function(opt) {

		var option = {
			elem: opt.elem,
			data: opt.data,
			tool: opt.tool,
			roll: opt.roll,
			index: opt.index,
			width: opt.width,
			height: opt.height,
			tabMax: opt.tabMax,
			session: opt.session ? opt.session : false,
			preload: opt.preload ? opt.preload : false,
			closeEvent: opt.closeEvent,
			success: opt.success ? opt.success : function(id) {}
		}

		tabData = normalizeTabs(opt.data) || [];
		if (option.session) {
			var restored = storedTabs(option.elem);
			if (restored && restored.length) tabData = restored;
		}
		option.data = tabData;
		var savedId = option.session ? storageGet(option.elem + "-pear-tab-data-current") : null;
		var savedIndex = tabData.findIndex(function(item) { return item.id === savedId; });
		option.index = savedIndex >= 0 ? savedIndex : Math.max(0, Math.min(Math.floor(Number(option.index) || 0), tabData.length - 1));
		tabDataCurrent = tabData[option.index] ? tabData[option.index].id : "";
		storageSet(option.elem + "-pear-tab-data", JSON.stringify(tabData));
		storageSet(option.elem + "-pear-tab-data-current", tabDataCurrent);

		var lastIndex;
		var tab = createTab(option);
		$("#" + option.elem).html(tab);
		$(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-prev").click(function() {
			rollPage("left", option);
		})
		$(".layui-tab[lay-filter='" + option.elem + "'] .layui-tab-next").click(function() {
			rollPage("right", option);
		})
		element.init();
		toolEvent(option);
		$("#" + option.elem).width(opt.width);
		$("#" + option.elem).height(opt.height);
		$("#" + option.elem).css({
			position: "relative"
		});
		closeEvent(option);

		option.success(tabDataCurrent);

		$("body .layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title").on("contextmenu", "li",
			function(e) {
				// 获取当前元素位置
				var top = e.clientY;
				var left = e.clientX;
				var menuWidth = 100;
				var currentId = $(this).attr("lay-id");
				var menu = "<ul><li class='item' id='" + option.elem +
					"closeThis'>关闭当前</li><li class='item' id='" + option.elem +
					"closeOther'>关闭其他</li><li class='item' id='" + option.elem +
					"closeAll'>关闭所有</li></ul>";

				contextTabDOM = $(this);
				var isOutsideBounds = (left + menuWidth) > $(window).width();
				if (isOutsideBounds) {
					left = $(window).width() - menuWidth;
				}
				// 初始化
				layer.open({
					type: 1,
					title: false,
					shade: false,
					skin: 'pear-tab-menu',
					closeBtn: false,
					area: [menuWidth + 'px', '108px'],
					fixed: true,
					anim: false,
					isOutAnim: false,
					offset: [top, left],
					content: menu, //iframe的url,
					success: function(layero, index) {
						layer.close(lastIndex);
						lastIndex = index;
						menuEvent(option, index);
						var timer;
						$(layero).on('mouseout', function() {
							timer = setTimeout(function() {
								layer.close(index);
							}, 30)
						});

						$(layero).on('mouseover', function() {
							clearTimeout(timer);
						});

						// 清除 item 右击
						$(layero).on('contextmenu', function() {
							return false;
						})

					}
				});
				return false;
			})

		mousewheelAndTouchmoveHandler(option)
		return new pearTab(option);
	}

	pearTab.prototype.click = function(callback) {
		var elem = this.option.elem;
		var option = this.option;
		element.on('tab(' + this.option.elem + ')', function(data) {
			var id = String($("#" + elem + " .layui-tab-title .layui-this").attr("lay-id") || "");
			storageSet(option.elem + "-pear-tab-data-current", id);
			if (!option.preload) {
				var $iframe = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-content").find(
					"iframe[id='" + id + "']");
				var iframeUrl = $iframe.attr("src");
				if (!iframeUrl || iframeUrl === "about:blank") {
					// 获取 url 并重载
					tabData.forEach(function(item, index) {
						if (item.id === id) {
							iframeUrl = item.url;
						}
					})
					tabIframeLoading(elem);
					$iframe.attr("src", iframeUrl);
				}
			}
			callback(id);
		});
	}

	pearTab.prototype.positionTab = function() {
		var $tabTitle = $('.layui-tab[lay-filter=' + this.option.elem + ']  .layui-tab-title');
		var autoLeft = 0;
		$tabTitle.children("li").each(function() {
			if ($(this).hasClass('layui-this')) {
				return false;
			} else {
				autoLeft += $(this).outerWidth();
			}
		});
		$tabTitle.animate({
			scrollLeft: autoLeft - $tabTitle.width() / 3
		}, 200);
	}

	pearTab.prototype.clear = function() {
		sessionStorage.removeItem(this.option.elem + "-pear-tab-data");
		sessionStorage.removeItem(this.option.elem + "-pear-tab-data-current");
	}

	pearTab.prototype.addTab = function(opt) {
		opt = normalizeTab(opt);
		if (!opt) return false;
		var title = '';
		if (opt.close) {
			title += '<span class="pear-tab-active"></span><span class="able-close title">' + escapeHtml(opt.title) +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		} else {
			title += '<span class="pear-tab-active"></span><span class="disable-close title">' + escapeHtml(opt.title) +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		}
		element.tabAdd(this.option.elem, {
			title: title,
			content: '<iframe id="' + escapeHtml(opt.id) + '" data-frameid="' + escapeHtml(opt.id) +
				'" scrolling="auto" frameborder="0" src="' +
				escapeHtml(opt.url) + '" style="width:100%;height:100%;" allowfullscreen="true"></iframe>',
			id: opt.id
		});
		tabData.push(opt);
		storageSet(this.option.elem + "-pear-tab-data", JSON.stringify(tabData));
		storageSet(this.option.elem + "-pear-tab-data-current", opt.id);
		element.tabChange(this.option.elem, opt.id);
	}

	var index = 0;
	// 根据过滤 fliter 标识, 重置选项卡标题
	pearTab.prototype.changeTabTitleById = function(elem, id, title) {
		var currentTab = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title [lay-id='" + id +
			"'] .title");
		currentTab.text(title);
	}

	// 根据过滤 filter 标识, 删除指定选项卡
	pearTab.prototype.delTabByElem = function(elem, id, callback) {
		var currentTab = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title [lay-id='" + id + "']");
		if (currentTab.find("span").is(".able-close")) {
			tabDelete(elem, id, callback);
		}
	}
	// 根据过滤 filter 标识, 删除其他选项卡
	pearTab.prototype.delOtherTabByElem = function(elem, callback) {
		var currentId = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title .layui-this").attr("lay-id");
		return closeMatchingTabs(elem, currentId, callback);
	}

	// 根据过滤 filter 标识, 删除全部选项卡
	pearTab.prototype.delAllTabByElem = function(elem, callback) {
		return closeMatchingTabs(elem, null, callback);
	}
	// 根据过滤 filter 标识, 删除当前选项卡
	pearTab.prototype.delCurrentTabByElem = function(elem, callback) {
		var currentTab = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title .layui-this");
		if (currentTab.find("span").is(".able-close")) {
			var currentId = currentTab.attr("lay-id");
			tabDelete(elem, currentId, callback);
		}
	}

	// 通过过滤 filter 标识, 新增标签页
	pearTab.prototype.addTabOnlyByElem = function(elem, opt, time) {
		opt = normalizeTab(opt);
		if (!opt) return false;
		var title = '';
		if (opt.close) {
			title += '<span class="pear-tab-active"></span><span class="able-close title">' + escapeHtml(opt.title) +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>'
		} else {
			title += '<span class="pear-tab-active"></span><span class="disable-close title">' + escapeHtml(opt.title) +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>'
		}
		if ($(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title li[lay-id]").length <= 0) {
			element.tabAdd(elem, {
				title: title,
				content: '<iframe id="' + escapeHtml(opt.id) + '" data-frameid="' + escapeHtml(opt.id) +
					'" scrolling="auto" frameborder="0" src="' +
					escapeHtml(opt.url) + '" style="width:100%;height:100%;" allowfullscreen="true"></iframe>',
				id: opt.id
			});
			if (time != false && time != 0) {
				tabIframeLoading(elem, opt.id);
			}
			tabData.push(opt);
			storageSet(elem + "-pear-tab-data", JSON.stringify(tabData));
		} else {
			var isData = false;
			$.each($(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title li[lay-id]"), function() {
				if ($(this).attr("lay-id") == opt.id) {
					isData = true;
				}
			})

			if (isData == false) {
				element.tabAdd(elem, {
					title: title,
					content: '<iframe id="' + escapeHtml(opt.id) + '" data-frameid="' + escapeHtml(opt.id) +
						'" scrolling="auto" frameborder="0" src="' +
						escapeHtml(opt.url) + '" style="width:100%;height:100%;" allowfullscreen="true"></iframe>',
					id: opt.id
				});
				if (time != false && time != 0) {
					tabIframeLoading(elem, opt.id);
				}
				tabData.push(opt);
				storageSet(elem + "-pear-tab-data", JSON.stringify(tabData));

			}
		}
		storageSet(elem + "-pear-tab-data-current", opt.id);
		element.tabChange(elem, opt.id);
	}

	/** 添 加 唯 一 选 项 卡 */
	pearTab.prototype.addTabOnly = function(opt, time) {
		opt = normalizeTab(opt);
		if (!opt) return false;
		var title = '';
		if (opt.close) {
			title += '<span class="pear-tab-active"></span><span class="able-close title">' + escapeHtml(opt.title) +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		} else {
			title += '<span class="pear-tab-active"></span><span class="disable-close title">' + escapeHtml(opt.title) +
				'</span><i class="layui-icon layui-unselect layui-tab-close">ဆ</i>';
		}
		if ($(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-title li[lay-id]").length <=
			0) {
			element.tabAdd(this.option.elem, {
				title: title,
				content: '<iframe id="' + escapeHtml(opt.id) + '" data-frameid="' + escapeHtml(opt.id) +
					'" scrolling="auto" frameborder="0" src="' +
					escapeHtml(opt.url) + '" style="width:100%;height:100%;" allowfullscreen="true"></iframe>',
				id: opt.id
			});
			if (time != false && time != 0) {
				tabIframeLoading(this.option.elem, opt.id);
			}
			tabData.push(opt);
			storageSet(this.option.elem + "-pear-tab-data", JSON.stringify(tabData));
			storageSet(this.option.elem + "-pear-tab-data-current", opt.id);
		} else {
			var isData = false;
			$.each($(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-title li[lay-id]"),
				function() {
					if ($(this).attr("lay-id") == opt.id) {
						isData = true;
					}
				})
			if (isData == false) {

				if (this.option.tabMax != false) {
					if ($(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-title li[lay-id]")
						.length >= this.option.tabMax) {
						layer.msg("最多打开" + this.option.tabMax + "个标签页", {
							icon: 2,
							time: 1000,
							shift: 6
						});
						return false;
					}
				}

				element.tabAdd(this.option.elem, {
					title: title,
					content: '<iframe id="' + escapeHtml(opt.id) + '" data-frameid="' + escapeHtml(opt.id) +
						'" scrolling="auto" frameborder="0" src="' +
						escapeHtml(opt.url) + '" style="width:100%;height:100%;" allowfullscreen="true"></iframe>',
					id: opt.id
				});
				if (time != false && time != 0) {
					tabIframeLoading(this.option.elem, opt.id);
				}
				tabData.push(opt);
				storageSet(this.option.elem + "-pear-tab-data", JSON.stringify(tabData));
				storageSet(this.option.elem + "-pear-tab-data-current", opt.id);
			}
		}
		element.tabChange(this.option.elem, opt.id);
		storageSet(this.option.elem + "-pear-tab-data-current", opt.id);
	}

	// Update an existing module tab without adding or selecting another tab.
	pearTab.prototype.replaceTabPage = function(id, url, title) {
		id = String(id);
		if (typeof url !== "string" || !url.trim()) return false;
		var item = tabData.find(function(tab) { return tab.id === id; });
		var container = $("#" + this.option.elem);
		var iframe = container.find("iframe").filter(function() { return $(this).attr("id") === id; });
		if (!item || !iframe.length) return false;
		item.url = url;
		if (title !== undefined) {
			item.title = String(title);
			container.find(".layui-tab-title li").filter(function() { return $(this).attr("lay-id") === id; })
				.find(".title").text(item.title);
		}
		iframe.attr("src", url);
		storageSet(this.option.elem + "-pear-tab-data", JSON.stringify(tabData));
		return true;
	};

	pearTab.prototype.getData = function() {
		return JSON.parse(JSON.stringify(tabData));
	};

	// 刷 新 指 定 的 选 项 卡
	pearTab.prototype.refresh = function(time) {
		// 刷 新 指 定 的 选 项 卡
		var currentId = $(".layui-tab[lay-filter=\'" + this.option.elem + "\'] .layui-tab-title .layui-this").attr("lay-id");
		if (!canLeaveTab(currentId)) return false;
		var $iframe = $(".layui-tab[lay-filter='" + this.option.elem + "'] .layui-tab-content .layui-show")
			.find("iframe");
		if (time != false && time != 0) {
			tabIframeLoading(this.option.elem);
		}
		$iframe.attr("src", $iframe.attr("src"));
	}

	function tabIframeLoading(elem, id) {
		var load = '<div id="pear-tab-loading' + index + '" class="pear-tab-loading">' +
			'<div class="ball-loader">' +
			'<span></span><span></span><span></span><span></span>' +
			'</div>' +
			'</div>'
		var $iframe = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-content .layui-show").find("iframe");
		if (id) {
			$iframe = $(".layui-tab[lay-filter='" + elem + "'] .layui-tab-content").find("iframe[id='" + id +
				"']");
		}
		$iframe.parent().append(load);
		var pearLoad = $("#" + elem).find("#pear-tab-loading" + index);
		pearLoad.css({
			display: "block"
		});
		index++;
		$iframe.on("load", function() {
			pearLoad.fadeOut(1000, function() {
				pearLoad.remove();
			});
		})
	}

	// Preflight the whole set before any removal, so a blocked later tab cannot cause partial closure.
	function canLeaveTab(id) {
		try {
			var navigation = window.CmsNavigation;
			return !navigation || typeof navigation.canLeave !== "function" || navigation.canLeave(String(id)) !== false;
		} catch (error) { return false; }
	}

	function closeMatchingTabs(elem, keepId, callback, option) {
		var ids = [];
		$(".layui-tab[lay-filter='" + elem + "'] .layui-tab-title li").each(function() {
			var id = String($(this).attr("lay-id"));
			if ((keepId === null || id !== String(keepId)) && $(this).find("span").is(".able-close")) ids.push(id);
		});
		if (!ids.every(canLeaveTab)) return false;
		ids.forEach(function(id) { tabDelete(elem, id, callback, option, true); });
		return true;
	}

	function tabDelete(elem, id, callback, option, prechecked) {
		id = String(id);
		if (!prechecked && !canLeaveTab(id)) return false;
		//根据 elem id 来删除指定的 layui title li
		var tabTitle = $(".layui-tab[lay-filter='" + elem + "']").find(".layui-tab-title");

		// 删除指定 id 的 title
		var removeTab = tabTitle.find("li[lay-id='" + id + "']");
		var nextNode = removeTab.next("li");
		if (!removeTab.hasClass("layui-this")) {
			removeTab.remove();
			var tabContent = $(".layui-tab[lay-filter='" + elem + "']").find("iframe[id='" + id + "']")
		.parent();
			tabContent.remove();

			tabDataCurrent = storageGet(elem + "-pear-tab-data-current");
			tabData = tabData.filter(function(item) {
				return item.id != id;
			})
			storageSet(elem + "-pear-tab-data", JSON.stringify(tabData));
			return false;
		}

		var currId;
		if (nextNode.length) {
			nextNode.addClass("layui-this");
			currId = nextNode.attr("lay-id");
			$("#" + elem + " [id='" + currId + "']").parent().addClass("layui-show");
		} else {
			var prevNode = removeTab.prev("li");
			prevNode.addClass("layui-this");
			currId = prevNode.attr("lay-id");
			$("#" + elem + " [id='" + currId + "']").parent().addClass("layui-show");
		}
		var nextFrame = $("#" + elem).find("iframe").filter(function() { return $(this).attr("id") === currId; });
		if (!nextFrame.attr("src") || nextFrame.attr("src") === "about:blank") {
			var nextTab = tabData.find(function(item) { return item.id === currId; });
			if (nextTab) nextFrame.attr("src", nextTab.url);
		}
		if (typeof callback === "function") callback(currId);
		tabDataCurrent = storageGet(elem + "-pear-tab-data-current");
		tabData = tabData.filter(function(item) {
			return item.id != id;
		})
		storageSet(elem + "-pear-tab-data", JSON.stringify(tabData));
		storageSet(elem + "-pear-tab-data-current", currId);

		removeTab.remove();
		// 删除 content
		var tabContent = $(".layui-tab[lay-filter='" + elem + "']").find("iframe[id='" + id + "']").parent();
		// 删除
		tabContent.remove();
	}

	function createTab(option) {

		var type = "";
		var types = option.type + " ";
		if (option.roll == true) {
			type = "layui-tab-roll";
		}
		if (option.tool != false) {
			type = "layui-tab-tool";
		}
		if (option.roll == true && option.tool != false) {
			type = "layui-tab-rollTool";
		}
		var tab = '<div class="pear-tab ' + types + type + ' layui-tab" lay-filter="' + option.elem +
			'" lay-allowClose="true">';
		var title = '<ul class="layui-tab-title">';
		var content = '<div class="layui-tab-content">';
		var control =
			'<div class="layui-tab-control"><li class="layui-tab-prev layui-icon layui-icon-left"></li><li class="layui-tab-next layui-icon layui-icon-right"></li><li class="layui-tab-tool layui-icon layui-icon-down"><ul class="layui-nav" lay-filter=""><li class="layui-nav-item"><a href="javascript:;"></a><dl class="layui-nav-child">';

		// 处 理 选 项 卡 头 部
		var index = 0;
		$.each(option.data, function(i, item) {
			var TitleItem = '';
			if (option.index == index) {
				TitleItem += '<li lay-id="' + escapeHtml(item.id) +
					'" class="layui-this"><span class="pear-tab-active"></span>';
			} else {
				TitleItem += '<li lay-id="' + escapeHtml(item.id) + '" ><span class="pear-tab-active"></span>';
			}

			if (item.close) {
				// 当 前 选 项 卡 可 以 关 闭
				TitleItem += '<span class="able-close title">' + escapeHtml(item.title) + '</span>';
			} else {
				// 当 前 选 项 卡 不 允 许 关 闭
				TitleItem += '<span class="disable-close title">' + escapeHtml(item.title) + '</span>';
			}
			TitleItem += '<i class="layui-icon layui-unselect layui-tab-close">ဆ</i></li>';
			title += TitleItem;
			if (option.index == index) {

				// 处 理 显 示 内 容
				content += '<div class="layui-show layui-tab-item"><iframe id="' + escapeHtml(item.id) +
					'" data-frameid="' + escapeHtml(item.id) +
					'"  src="' + escapeHtml(item.url) +
					'" frameborder="no" border="0" marginwidth="0" marginheight="0" style="width: 100%;height: 100%;" allowfullscreen="true"></iframe></div>'
			} else {
				var frameUrl = option.preload ? item.url : "about:blank";
				// 处 理 显 示 内 容
				content += '<div class="layui-tab-item"><iframe id="' + escapeHtml(item.id) + '" data-frameid="' +
					escapeHtml(item.id) + '"  src="' +
					escapeHtml(frameUrl) +
					'" frameborder="no" border="0" marginwidth="0" marginheight="0" style="width: 100%;height: 100%;" allowfullscreen="true"></iframe></div>'
			}
			index++;
		});

		title += '</ul>';
		content += '</div>';
		control += '<dd id="closeThis"><a href="#">关 闭 当 前</a></dd>'
		control += '<dd id="closeOther"><a href="#">关 闭 其 他</a></dd>'
		control += '<dd id="closeAll"><a href="#">关 闭 全 部</a></dd>'
		control += '</dl></li></ul></li></div>';

		tab += title;
		tab += control;
		tab += content;
		tab += '</div>';
		tab += ''
		return tab;
	}

	function rollPage(d, option) {
		var $tabTitle = $('#' + option.elem + '  .layui-tab-title');
		var left = $tabTitle.scrollLeft();
		if ('left' === d) {
			$tabTitle.animate({
				scrollLeft: left - 450
			}, 200);
		} else {
			$tabTitle.animate({
				scrollLeft: left + 450
			}, 200);
		}
	}

	function closeEvent(option) {
		$(".layui-tab[lay-filter='" + option.elem + "']").on("click", ".layui-tab-close", function() {
			var layid = $(this).parent().attr("lay-id");
			tabDelete(option.elem, layid, option.closeEvent, option);
		})
	}

	function menuEvent(option, index) {

		$("#" + option.elem + "closeThis").click(function() {
			var currentTab = contextTabDOM;

			if (currentTab.find("span").is(".able-close")) {
				var currentId = currentTab.attr("lay-id");
				tabDelete(option.elem, currentId, option.closeEvent, option);
			} else {
				layer.msg("当前页面不允许关闭", {
					icon: 3,
					time: 800
				})
			}
			layer.close(index);
		})

		$("#" + option.elem + "closeOther").click(function() {
			var currentId = contextTabDOM.attr("lay-id");
			closeMatchingTabs(option.elem, currentId, option.closeEvent, option);
			layer.close(index);
		})

		$("#" + option.elem + "closeAll").click(function() {
			var currentId = contextTabDOM.attr("lay-id");
			closeMatchingTabs(option.elem, null, option.closeEvent, option);
			layer.close(index);
		})
	}

	function toolEvent(option) {
		$("body .layui-tab[lay-filter='" + option.elem + "']").on("click", "#closeThis", function() {
			var currentTab = $(".layui-tab[lay-filter='" + option.elem +
				"'] .layui-tab-title .layui-this");
			if (currentTab.find("span").is(".able-close")) {
				var currentId = currentTab.attr("lay-id");
				tabDelete(option.elem, currentId, option.closeEvent, option);
			} else {
				layer.msg("当前页面不允许关闭", {
					icon: 3,
					time: 800
				})
			}
		})

		$("body .layui-tab[lay-filter='" + option.elem + "']").on("click", "#closeOther", function() {
			var currentId = $(".layui-tab[lay-filter='" + option.elem +
				"'] .layui-tab-title .layui-this").attr("lay-id");
			closeMatchingTabs(option.elem, currentId, option.closeEvent, option);
		})

		$("body .layui-tab[lay-filter='" + option.elem + "']").on("click", "#closeAll", function() {
			var currentId = $(".layui-tab[lay-filter='" + option.elem +
				"'] .layui-tab-title .layui-this").attr("lay-id");
			closeMatchingTabs(option.elem, null, option.closeEvent, option);
		})
	}

	function mousewheelAndTouchmoveHandler(option) {
		var $bodyTab = $("body .layui-tab[lay-filter='" + option.elem + "'] .layui-tab-title")
		var $tabTitle = $('#' + option.elem + '  .layui-tab-title');
		var mouseScrollStep = 100
		// 鼠标滚轮
		$bodyTab.on("mousewheel DOMMouseScroll", function(e) {
			e.originalEvent.preventDefault()
			var delta = (e.originalEvent.wheelDelta && (e.originalEvent.wheelDelta > 0 ? "top" :
					"down")) || // chrome & ie
				(e.originalEvent.detail && (e.originalEvent.detail > 0 ? "down" : "top")); // firefox
			var scrollLeft = $tabTitle.scrollLeft();

			if (delta === "top") {
				scrollLeft -= mouseScrollStep
			} else if (delta === "down") {
				scrollLeft += mouseScrollStep
			}
			$tabTitle.scrollLeft(scrollLeft)
		});

		// 触摸移动
		var touchX = 0;
		$bodyTab.on("touchstart", function(e) {
			var touch = e.originalEvent.targetTouches[0];
			touchX = touch.pageX
		})
		$bodyTab.on("touchmove", function(e) {
			var event = e.originalEvent;
			if (event.targetTouches.length > 1) return;
			event.preventDefault();
			var touch = event.targetTouches[0];
			var distanceX = touchX - touch.pageX
			var scrollLeft = $tabTitle.scrollLeft();
			touchX = touch.pageX
			$tabTitle.scrollLeft(scrollLeft += distanceX)
		});
	}

	exports(MOD_NAME, new pearTab());
})
