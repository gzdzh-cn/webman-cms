/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/
CKEDITOR.plugins.add("addon",{init:function(editor){var pluginName="addon";CKEDITOR.dialog.add(pluginName,this.path+"dialogs/addon.js");editor.addCommand(pluginName,new CKEDITOR.dialogCommand(pluginName));editor.ui.addButton("Addon",{label:"附件",icon:"./plugins/addon//images/addon.gif",command:pluginName})}});
