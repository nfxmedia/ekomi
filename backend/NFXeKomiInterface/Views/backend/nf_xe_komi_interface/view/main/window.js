Ext.define('Shopware.apps.NFXeKomiInterface.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.articleList-main-window',
    title : '{s name=list/title}Emails list{/s}',
    layout: 'border',
    width: 990,
    height: '90%',
    stateful: true,
    stateId: 'shopware-articleList-main-window',

    snippets: {
        title:         '{s name=list/blocked_email}eKomi E-Mail Blacklist{/s}'
    },

    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        me.addEvents(
            /**
             * @event
             * @param [Ext.view.View] view - the view that fired the event
             * @param [Ext.data.Model] record
             */
            'categoryChanged'
        );


        me.items = [{
            xtype: 'ekomi-emails-list',
            emailsStore: me.emailsStore,
            region: 'center'
        }];

        me.callParent(arguments);
    }
});
