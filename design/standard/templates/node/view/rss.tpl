    
        <item>
            <pubDate>{gmdate( 'D, d M Y H:i:s', $node.object.published )} GMT</pubDate>
            <title>{$node.name|wash()}</title>
            <link>{$node.url_alias|ezurl('no','full')}</link>
            <guid>{$node.url_alias|ezurl('no','full')}</guid>
            <description>{$node.name|trim()|wash()}</description>
        </item>
