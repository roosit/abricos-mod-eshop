/*
@version $Id$
@package Abricos
@copyright Copyright (C) 2008 Abricos All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	yahoo: ['tabview','dragdrop'],
    mod:[
         {name: 'eshop', files: ['billing.js', 'config.js']},
         {name: 'catalog', files: ['catalog.js','eltype.js']}
    ]
};
Component.entryPoint = function(NS){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;

	var API = NS.API;
	
	var buildTemplate = this.buildTemplate;
	
	var ManagerWidget = function(container){
		this.init(container);
	};
	ManagerWidget.prototype = {
		init: function(container){
		
			var TM = buildTemplate(this, 'widget');
			container.innerHTML = TM.replace('widget');
			
			var tabView = new YAHOO.widget.TabView(TM.getElId('widget.id'));
			this.page = {
				// 'billing': new NS.billing.Manager(TM.getEl('widget.orders')),
					
				'catalog': new Brick.mod.catalog.API.showElementTypeManagerWidget({
					'container': TM.getEl('widget.catalog'),
					'mmPrefix': 'eshop' 
				}),
				'config': new NS.config.Manager(TM.getEl('widget.config'))
			};
		}
	};
	NS.ManagerWidget = ManagerWidget;
	
	var ManagerPanel = function(){
		ManagerPanel.superclass.constructor.call(this,{
			fixedcenter: true, width: '780px', height: '480px'
		});
	};
	YAHOO.extend(ManagerPanel, Brick.widget.Panel, {
		initTemplate: function(){
			buildTemplate(this, 'panel');
			return this._T['panel'];
		},
		onLoad: function(){
			this.widget = new ManagerWidget(this._TM.getEl('panel.widget'));
		}
	});
	NS.ManagerPanel = ManagerPanel;
	
	API.showManagerWidget = function(container){
		var widget = new ManagerWidget(container);
		NS.data.request();
		return widget;
	};
	
	API.showManagerPanel = function(){
		var panel = new ManagerPanel();
		NS.data.request();
		return panel;
	};
	
	API.showCatalogManagerPanel = function(){
		new Brick.mod.catalog.API.showManagerPanel('eshop');
	}
	
	API.showCatalogManagerWidget = function(container){
		new Brick.mod.catalog.API.showManagerWidget({
			'container': container, 
			'mmPrefix': 'eshop'
		});
	}
	
};
