{def $image = concat( ""|ezroot('no', 'full'), 'people_default.png'|ezimage('no', 'true') )}
{if $node.object.owner.current.data_map.image.content.is_valid}
    {set $image = $node.object.owner.current.data_map.image.content.hackergotchi.url|ezroot('no', 'full')}
{/if}
{set $image = concat( '<img src="', $image, '"  alt="', $node.object.owner.name, '" style="float: right" />' )}
  <item>
    <pubDate>{$item.pubDate}</pubDate>
    <title>{$node.object.owner.current.name|wash()} : {$item.title|wash()}</title>
    <link>{$node.data_map.location.content}</link>
    {if or( $node.data_map.guid.has_content|not(), $node.data_map.guid.content|begins_with('http://') )}
    <guid>{$node.data_map.location.content}</guid>
    {else}
    <guid isPermaLink="false">{$node.data_map.guid.content}</guid>
    {/if}
    <description>
    <![CDATA[{$image}{$item.description}]]>
    </description>
    {if $item.category}<category>{$item.category|wash()}</category>{/if}

  </item>
{undef $image}