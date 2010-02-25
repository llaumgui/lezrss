<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
    <channel>
    {cache-block keys=array( $uri_string ) expiry=ezini('RSSSettings', 'CacheTime', 'site.ini')}
        {$module_result.content}
    {/cache-block}
    </channel>
</rss>
<!--DEBUG_REPORT-->