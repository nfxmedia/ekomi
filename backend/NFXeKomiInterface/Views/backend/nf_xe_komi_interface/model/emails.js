Ext.define('Shopware.apps.NFXeKomiInterface.model.Emails', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
		//{block name="backend/article_list/model/list/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'email', type: 'string' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read:    '{url action="list"}',
            create:  '{url action="create"}',
            update:  '{url action="create"}',
            destroy: '{url action="delete"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});