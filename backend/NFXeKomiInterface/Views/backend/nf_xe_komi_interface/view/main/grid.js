Ext.define('Shopware.apps.NFXeKomiInterface.view.main.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ekomi-emails-list',

    snippets: {
        deleteEmail:  '{s name=list/delete_article}Delete email{/s}',
        columnEmail:     '{s name=list/column_email}Email{/s}',
        addEMail:    '{s name=list/manage_categories}Add email{/s}'
    },

    initComponent: function() {
        var me = this;

        me.store       = me.emailsStore;

        me.columns     = me.getColumns();
        me.tbar        = me.getToolbar();       

        me.addEvents();

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;
        me.actionColumItems = [];
        
        me.actionColumItems.push({
            iconCls:'sprite-minus-circle-frame',
            action:'ebay_delete',
            handler: function (view, rowIndex, colIndex, item, opts, record) {
               
									me.store.remove(record);
									me.store.sync({
										success : function(record, operation) {
										},
										failure : function(record, operation) {
											var message= me.store.getProxy().getReader().rawData.message;
											Shopware.Notification.createGrowlMessage("{s name=form/message/error_delete_title}Error with deleting{/s}", message, "{s name=form/message/error_email}Blcoked Email{/s}");
										}
									});
            }
          });

        return [{
            header: me.snippets.columnEmail,
            dataIndex: 'email',
            flex: 2
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: 30,
            items: me.actionColumItems
        }];
    },

    /**
     * Creates the grid toolbar
     *
     * @return [Ext.toolbar.Toolbar] grid toolbar
     */
    getToolbar: function() {
        var me = this, buttons = [];
        
        /*{if {acl_is_allowed resource=article privilege=save}}*/
        buttons.push(
                Ext.create('Ext.button.Button', {
                    text: '{s name=form/button/email_enter/label}E-Mail Adresse sperren{/s}',
                    iconCls:'sprite-plus-circle-frame',
                    handler: function() {
                           Ext.Msg.prompt('Blocked email', '{s name=form/button/email_enter}Please enter email address:{/s}', function(btn, text) {

								if (btn == 'ok') {
									var record = Ext.create("Shopware.apps.NFXeKomiInterface.model.Emails");
									record.set("email", text);
									me.store.loadData([record], true);
									me.store.sync({
										success : function(record, operation) {
										},
										failure : function(record, operation) {
											var message= me.store.getProxy().getReader().rawData.message;
											Shopware.Notification.createGrowlMessage("{s name=form/message/error_title}Error with saving{/s}", message, "{s name=form/message/error_email}Blcoked Email{/s}");
										}
									});
								}
							});
            }
                })
        );
        /*{/if}*/

        buttons.push({
            xtype: 'tbfill'
        });

        buttons.push({
            xtype: 'tbspacer',
            width: 6
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: buttons
        });
    },

    /**
     * Special ExtJS 4 method which will be fired
     * when the component is rendered.
     *
     * Enables the drag zone and collects the neccessary
     * data for the drop item.
     *
     * @private
     * @returm void
     */
    afterRender: function() {
        var me = this, view = me.getView();
        me.callParent(arguments);
    }
});
