Ext.define('Shopware.apps.NFXeKomiStatistics.store.Statistics', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model : 'Shopware.apps.NFXeKomiStatistics.model.Statistics',
    remoteSort: true,
    remoteFilter: true,
    pageSize: 40
});
