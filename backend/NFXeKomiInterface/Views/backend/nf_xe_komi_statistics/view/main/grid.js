Ext.define('Shopware.apps.NFXeKomiStatistics.view.main.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.ekomi-statistics-list',

    snippets: {
        columnDate:     '{s name=list/date}Datum{/s}',
        columnMailsSent:     '{s name=list/column_mails_sent}E-Mail versandet{/s}',
        columnRatingLinks:     '{s name=list/column_rating_links}Bewertungslinks genieriert{/s}',
        columnNewReviews:     '{s name=list/column_new_reviews}neue Produktbewertungen{/s}',
    },

    initComponent: function() {
    	var me = this;

        me.store       = me.statisticsStore;
        me.selType 	   = 'rowmodel';
        me.columns     = me.getColumns();
        me.bbar        = me.getPagingbar();
        
        me.addEvents(
            // 'editArticle',
            // 'deleteArticle',
            // 'bulk_export_articles'
        );

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
            header: me.snippets.columnDate,
            dataIndex: 'date',
            flex: 1,
            renderer: me.totalColumnRenderer
        }, {
            header: me.snippets.columnMailsSent,
            dataIndex: 'nr_emails',
            flex: 1,
            align: 'right',
            renderer: me.totalColumnRenderer
        }, {
            header: me.snippets.columnRatingLinks,
            dataIndex: 'nr_links',
            flex: 1,
            align: 'right',
            renderer: me.totalColumnRenderer
        }, {
            header: me.snippets.columnRatingLinks,
            dataIndex: 'nr_reviews',
            flex: 1,
            align: 'right',
            renderer: me.totalColumnRenderer
        }];
    },

    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingbar: function() {
        var me = this;
        var pageSize = Ext.create('Ext.form.field.ComboBox', {
            labelWidth: 120,
            cls: Ext.baseCSSPrefix + 'page-size',
            queryMode: 'local',
            width: 180,
            listeners: {
                scope: me,
                select: me.onPageSizeChange
            },
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value' ],
                data: [
                    { value: '20' },
                    { value: '40' },
                    { value: '60' },
                    { value: '80' },
                    { value: '100' },
                    { value: '250' },
                ]
            }),
            displayField: 'value',
            valueField: 'value'
        });
        pageSize.setValue(me.statisticsStore.pageSize);

        var pagingBar = Ext.create('Ext.toolbar.Paging', {
            store: me.statisticsStore,
            dock:'bottom',
            displayInfo:true
        });

        pagingBar.insert(pagingBar.items.length - 2, [ { xtype: 'tbspacer', width: 6 }, pageSize ]);
        return pagingBar;
    },
    
    /**
     * Event listener method which fires when the user selects
     * a entry in the "number of orders"-combo box.
     *
     * @event select
     * @param [object] combo - Ext.form.field.ComboBox
     * @param [array] records - Array of selected entries
     * @return void
     */
    onPageSizeChange: function(combo, records) {
        var record = records[0],
            me = this;

        me.statisticsStore.pageSize = record.get('value');
        me.statisticsStore.loadPage(1);
    },

    totalColumnRenderer: function(value, metaData, record) {
        if (record instanceof Ext.data.Model) {
            if(record.get('date') == 'SUMME'){
                return '<span style="font-weight:bold;color:blue;">'+value+'</span>';
            }
        }
        return value;
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
