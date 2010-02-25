{def $items = fetch( content, list, hash(
        parent_node_id, $node.node_id,
        sort_by, array( 'published', false() ),
        limit, $limit ) )
}
        <title>{$title}</title>
        <link>{"/"|ezroot('no', 'full')}</link>
        <description>{$description}</description>
        {if $image|ne('')}
        <image>
            <url>{concat( ""|ezroot('no', 'full'), $image|ezimage('no', 'true') )}</url>
            <title>{$title}</title>
            <link>{"/"|ezroot('no', 'full')}</link>
        </image>{/if}
{foreach $items as $item}
    {node_view_gui content_node=$item view=rss}
{/foreach}
{undef}