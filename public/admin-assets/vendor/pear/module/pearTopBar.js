layui.define(['jquery', 'element','util'], function(exports) {
	"use strict";

	var MOD_NAME = 'pearTopBar',
		$ = layui.jquery,
		util = layui.util,
		element = layui.element;
	
	var topBar = new function() {
		util.fixbar({});
	}
	exports(MOD_NAME,topBar);
});