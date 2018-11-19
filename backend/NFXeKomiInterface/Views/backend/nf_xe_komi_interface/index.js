//{block name="backend/index/view/menu" append}

createiFrameDemo = function() {
    var requestUrl = 'https://www.ekomi.de/kundenbereich.php';
    var iframeDemo = Ext.create('Ext.window.Window', {
        title: '{s name=list/iframe_title}eKomi Backend{/s}',
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