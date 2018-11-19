{literal}
    <style style="text/css" rel="stylesheet">
        body{
            padding:10px;    
        }

        *{
            padding:0;
            margin:auto;
            box-sizing: border-box;
        }

        section {
            margin: 20px auto;
        }

        h1 {
            background-color: #777;
            color: #fff;
            margin: auto -10px;
            padding: 10px;
        }

        table{
            width: 100%;
            margin:10px auto;
            border-collapse: collapse;    
            text-align: center;
        }

        caption{
            font-weight: 600;
            background-color: #ccc;
            border:1px solid;
            border-bottom:none;
            padding:5px;
        }

        table td{
            border:1px solid; 
        }
        table td h3{
            padding: 5px;
            background-color: #f3f3f3;
        }

    </style>
{/literal}

{function name="nfx_ekomi_table"}
    {foreach from=$tables key=table_label item=table}
        {if $table|is_array}
            {foreach from=$table key=table_row_label item=table_row}
                <table>
                    <caption>
                        <h3>
                            {$table_row_label|upper|replace:"_":" "}   
                        </h3> 
                    </caption>
                    {foreach from=$table_row key=table_cell_label item=table_cell name=table_cell}
                        {assign var="first_row"  value="<tr>"}
                        {if $table_cell|is_array}
                            <tr>
                                {if $table_row_label=="sql"}
                                    <td>
                                        {$table_cell}
                                    </td>
                                {else}
                                    {foreach from=$table_cell key=table_item_label item=table_item name=table_item}
                                        <td>
                                            {if $smarty.foreach.table_cell.index==0}
                                                <h3>
                                                    {$table_item_label}
                                                </h3>
                                                {$first_row = $first_row|cat:"<td>"|cat:$table_item}
                                                {if $smarty.foreach.table_item.last}
                                                    {$first_row = "</td></tr>"|cat:$first_row}
                                                    {$first_row}
                                                {else}
                                                    {$first_row = $first_row|cat:"</td>"}
                                                {/if}
                                            {else}
                                                {$table_item}
                                            {/if}
                                        </td>
                                    {/foreach}
                                {/if}
                            </tr>
                        {else}
                            <tr>
                                <td>
                                    {$table_cell}
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                </table>
            {/foreach}
        {else}
            <table>
                <tr>
                    <td>
                        {$table_label|upper|replace:"_":" "}
                    </td>
                    <td>
                        {$table}&nbsp;
                    </td>
                </tr>
            </table>
        {/if}
    {/foreach}
{/function}

{foreach from=$nfx_ekomi_debug key=debug_label item=debug_item}
    <section>
        <h1 style="text-align: center;">
            {$debug_label|upper|replace:"_":" "}    
        </h1>
        {call name=nfx_ekomi_table tables=$debug_item}
    </section>
{/foreach}