Ext.define('Shopware.apps.NFXeKomiInterface.store.Emails', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model : 'Shopware.apps.NFXeKomiInterface.model.Emails',
    remoteSort: true,
    remoteFilter: true,
    pageSize: 40
});
