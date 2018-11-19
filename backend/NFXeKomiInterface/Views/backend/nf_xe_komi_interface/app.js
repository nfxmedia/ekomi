/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.NFXeKomiInterface', {

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.NFXeKomiInterface',

    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend: 'Enlight.app.SubApplication',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath: '{url action=load}',

    /**
     * load all files at once
     * @string
     */
    bulkLoad: true,

    /**
     * Required controllers
     * @array
     */
    controllers: [ 'Emails' ],

    /**
     * Required stores
     * @array
     */
    stores: [ 'Emails'],

    /**
     * Required models
     * @array
     */
    models: [ 'Emails'],

    /**
     * Required views
     * @array
     */
    views: [ 'main.Window', 'main.Grid'],

    /**
     * Returns the main application window for this is expected
     * by the Enlight.app.SubApplication class.
     * The class sets a new event listener on the "destroy" event of
     * the main application window to perform the destroying of the
     * whole sub application when the user closes the main application window.
     *
     * This method will be called when all dependencies are solved and
     * all member controllers, models, views and stores are initialized.
     *
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me             = this,
            mainController = me.getController('Emails');

        return mainController.mainWindow;
    }
});
