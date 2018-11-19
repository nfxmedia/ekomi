Ext.define('Shopware.apps.NFXeKomiStatistics.model.Statistics', {
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
        /*{ name: 'id', type: 'int' },
        { name: 'mails_sent_day', type: 'int' },
        { name: 'mails_sent_total', type: 'int' },
        { name: 'mail_sent_date', type: 'string' },
        { name: 'rating_links_day', type: 'int' },
        { name: 'rating_links_total', type: 'int' },
        { name: 'rating_links_date', type: 'string' },
        { name: 'new_reviews_day', type: 'int' },
        { name: 'new_reviews_total', type: 'int' },
        { name: 'new_reviews_date', type: 'string' },
        { name: 'import_reviews_cron_status', type: 'bool' },
        { name: 'import_reviews_cron_last_date', type: 'string' },
        { name: 'shipping_mails_cron_status', type: 'bool' },
        { name: 'shipping_mails_cron_last_date', type: 'string' },*/
        { name: 'date', type: 'string' },
        { name: 'nr_emails', type: 'int' },
        { name: 'nr_links', type: 'int' },
        { name: 'nr_reviews', type: 'int' },
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
            //read:    '{url action="monitoring"}',
            read:    '{url action="getStatistics"}',
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