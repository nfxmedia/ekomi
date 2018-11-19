Ext.define('Shopware.apps.NFXeKomiStatistics.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias : 'widget.statistics-main-window',
    title : '{s name=list/title}Statistiken{/s}',
    layout: 'border',
    width: '90%',
    height: '90%',
    stateful: true,
    stateId: 'shopware-statistics-main-window',

    snippets: {
    	filterTitle:   '{s name=ekomi/statistics/filter_title}Filter{/s}',
        type: {
            type:   '{s name=ekomi/statistics/type}Typ{/s}',
            monthly:   '{s name=ekomi/statistics/monthly}Monat{/s}',
            daily:   '{s name=ekomi/statistics/daily}Tag{/s}',
        },
        date: {
            from:   '{s name=ekomi/statistics/date_from}Von{/s}',
            to:   '{s name=ekomi/statistics/date_to}Bis{/s}',
        }
    },

    initComponent: function() {
        var me = this;

        me.addEvents();

        me.items = [{
            xtype: 'ekomi-statistics-list',
            statisticsStore: me.statisticsStore,
            region: 'center'
        }, {
            xtype: 'ekomi-cronjobs-list',
            cronjobsStore: me.cronjobsStore,
            region: 'east',
            width: 400,
        },{
        	xtype: 'container',
            width: 230,
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },
            region: 'west',
            items: [
                me.createFilterPanel()
            ]
        }];

        me.callParent(arguments);
    },

    createFilterPanel: function() {
        var me = this;

        return new Ext.create('Ext.form.Panel', {
		    bodyPadding: 5,
		    title: me.snippets.filterTitle,
		    items: [
                        {
                            xtype: 'base-element-select',
                            name: 'type',
                            anchor: '100%',
                            fieldLabel: me.snippets.type.type,
                            queryMode: 'local',
                            store: Ext.create('Ext.data.Store', {
                                fields: ['id', 'name'],
                                data: [
                                    {
                                        "id": "M",
                                        "name": me.snippets.type.monthly
                                    },
                                    {
                                        "id": "D",
                                        "name": me.snippets.type.daily
                                    }
                                ]
                            }),
                            valueField: 'id',
                            displayField: 'name',
                            allowBlank: false,
                            editable: false,
                            value: 'M',
                            parent: me,
                            listeners: {
                                'change': function(field, value) {
                                    var me    = this,
                                        store =  me.parent.statisticsStore;
                                    store.getProxy().extraParams.type = value;
                                    store.getProxy().extraParams.from_date = me.parent.items.items[2].items.items[0].items.items[1].lastValue;
                                    store.getProxy().extraParams.to_date = me.parent.items.items[2].items.items[0].items.items[2].lastValue;
                                    store.load();
                                }
                            }
                        },{
		        xtype: 'datefield',
		        anchor: '100%',
		        fieldLabel: me.snippets.date.from,
		        name: 'from_date',
		        id: 'from_date',
		        renderer: me.weekAgo,
		        listeners: {
                    change: {
                        fn: function(view, newValue, oldValue) {
                            var me    = this,
                                store =  me.statisticsStore;
                            store.getProxy().extraParams.type = me.items.items[2].items.items[0].items.items[0].value;
                            store.getProxy().extraParams.from_date = newValue;
                            store.getProxy().extraParams.to_date = me.items.items[2].items.items[0].items.items[2].lastValue;
                            store.load();

                        },
                        scope: me
                    }
                },
		        maxValue: new Date()  // limited to the current date or prior
		    }, {
		        xtype: 'datefield',
		        anchor: '100%',
		        fieldLabel: me.snippets.date.to,
		        name: 'to_date',
		        id: 'to_date',
		        listeners: {
                    change: {
                        fn: function(view, newValue, oldValue) {
                            var me    = this,
                                store =  me.statisticsStore;
                            store.getProxy().extraParams.type = me.items.items[2].items.items[0].items.items[0].value;
                            store.getProxy().extraParams.from_date = me.items.items[2].items.items[0].items.items[1].lastValue;
                            store.getProxy().extraParams.to_date = newValue;
                            store.load();

                        },
                        scope: me
                    }
                },
		        value: new Date()  // defaults to today
		    }]
		});
    },
    
    weekAgo: function(value, metaData, record) {
    	var date = new Date();
    	date.setDate(date.getDate() - 7);
    	return date;
    },
});
