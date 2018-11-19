Ext.define('Shopware.apps.NFXeKomiStatistics.view.main.Cronjobs', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ekomi-cronjobs-list',

    snippets: {        
        title: '{s name=cronjobs/title}Cronjobs{/s}',
        columnName:     '{s name=cronjobs/name}Name{/s}',
        columnActive:     '{s name=cronjobs/active}Aktiv{/s}',
        columnDate:     '{s name=cronjobs/date}Letzte Ausführung{/s}'
    },

    initComponent: function() {
    	var me = this;

        me.title = me.snippets.title;
        me.store       = me.cronjobsStore;
        me.selType 	   = 'rowmodel';
        me.columns     = me.getColumns();
        me.bbar        = me.getPagingbar();

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns: function() {
        var me = this;
        
        return [{
            header: me.snippets.columnName,
            dataIndex: 'name',
            flex: 5
        }, {
            header: me.snippets.columnActive,
            dataIndex: 'active',
            flex: 1,
            renderer: me.renderBooleanColumn
        }, {
            header: me.snippets.columnDate,
            dataIndex: 'date',
            flex: 2,
            xtype: 'datecolumn',
            format: Ext.Date.defaultFormat + ' H:i',
        }];
    },
    
    renderBooleanColumn: function(value, column, model) {
        var me = this;
        if (value) {
            return '<div style="width:100%;text-align:center;"><div class="sprite-tick"  style="width: 25px;display: inline-block;">&nbsp;</div></div>';
        } else {
            return '<div style="width:100%;text-align:center;"><div class="sprite-cross" style="width: 25px;display: inline-block;">&nbsp;</div></div>';
        }
    },
    
    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function() {
        var me = this;
        
        var pagingBar = Ext.create('Ext.toolbar.Paging', {
            store: me.cronjobsStore,
            dock:'bottom',
            displayInfo:true
        });

        return pagingBar;
    }
});
