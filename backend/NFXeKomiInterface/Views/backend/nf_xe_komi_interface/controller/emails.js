Ext.define('Shopware.apps.NFXeKomiInterface.controller.Emails', {

	/**
	 * Extend from the standard ExtJS 4
	 * @string
	 */
	extend : 'Ext.app.Controller',

	/**
	 * Class property which holds the main application if it is created
	 *
	 * @default null
	 * @object
	 */
	mainWindow : null,

	/**
	 * Define references for the different parts of our application. The
	 * references are parsed by ExtJS and Getter methods are automatically created.
	 *
	 * @array
	 */
	refs : [],

	/**
	 * Contains all snippets for the component.
	 * @object
	 */
	snippets : {
	},

	/**
	 * Creates the necessary event listener for this
	 * specific controller and opens a new Ext.window.Window
	 *
	 * @return void
	 */
	init : function() {
		var me = this;

		me.mainWindow = me.getView('main.Window').create({
			emailsStore : me.getStore('Emails').load()
		});

		me.mainWindow.show();

		me.callParent(arguments);
	}
	
});