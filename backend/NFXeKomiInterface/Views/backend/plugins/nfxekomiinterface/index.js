//{block name="backend/index/controller/main" append}
createiFrameDemo = function() {
    var requestUrl = 'http://www.bing.com/';
    var iframeDemo = Ext.create('Ext.window.Window', {
        title: 'iFrame-Demo',
        autoShow: true,
        unstyled: false,
        layout: 'fit',
        width: '80%',
        header: true,
        height: 600,
        resizable: true,
        closable: true,
        items: [{
            xtype: 'container',
            region: 'center',
            layout: 'fit',
            html: '<ifr' + 'ame border="0" src="'+ requestUrl +'"></ifr' + 'ame>'
    }]
});
}
//{/block}