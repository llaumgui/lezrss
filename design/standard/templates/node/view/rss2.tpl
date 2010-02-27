
  <item>
    <pubDate>{$item.pubDate}</pubDate>
    <title>{$item.title|wash()}</title>
    <link>{$item.link}</link>
    <guid>{$item.guid}</guid>
    <description>
    <![CDATA[{$item.description}]]>
    </description>
    {if $item.category}<category>{$item.category|wash()}</category>{/if}

  </item>
