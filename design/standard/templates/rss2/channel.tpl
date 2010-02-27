<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
  <title>{$channel['title']}</title>
  <link>{$channel['link']}</link>
  <atom:link href="{$channel['atom:link']['href']}" rel="{$channel['atom:link']['rel']}" type="{$channel['atom:link']['type']}"/>
  <description>{$channel['description']}</description>
  <language>{$channel['language']}</language>
  <pubDate>{$channel['pubDate']}</pubDate>
  <copyright>{$channel['copyright']}</copyright>
  <generator>{$channel['generator']}</generator>

  {if is_set( $channel['image'] )}<image>
    <url>{$channel['image']['url']}</url>
    <title>{$channel['image']['title']}</title>
    <link>{$channel['image']['link']}</link>
  </image>{/if}

  {foreach $node_array as $key => $node}
    {node_view_gui view='rss2' content_node=$node item=$items[$key]}
  {/foreach}
</channel>
</rss>