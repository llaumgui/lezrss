<?php
/**
 * File containing the feed module
 *
 * @version //autogentag//
 * @package LeZRSS
 * @copyright Copyright (C) 2008-2012 Guillaume Kulakowski and contributors
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

$Module = $Params['Module'];
$rssIni = eZINI::instance( 'lezrss.ini' );

// Add default rss feed
if ( !isset ( $Params['RSSFeed'] ) )
{
    $feedName = $rssIni->variable( 'RSSSettings', 'DefaultRSS' );
    if( empty($feedName) )
    {
        eZDebug::writeError( 'No RSS feed specified' );
        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
}
else
{
    $feedName = $Params['RSSFeed'];
}
$RSSExport = leZRSSExport::fetchByName( $feedName );

// Get and check if RSS Feed exists
if ( !$RSSExport )
{
    eZDebug::writeError( 'Could not find RSSExport : ' . $Params['RSSFeed'] );
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$config = eZINI::instance( 'site.ini' );
$cacheTime = intval( $config->variable( 'RSSSettings', 'CacheTime' ) );
//$cacheTime = 0; // for testing

$lastModified = gmdate( 'D, d M Y H:i:s', time() ) . ' GMT';

if ( $cacheTime <= 0 )
{
    $rssContent = $RSSExport->tplRSS( $lastModified );
}
else
{
    $cacheDir = eZSys::cacheDirectory();
    $currentSiteAccessName = $GLOBALS['eZCurrentAccess']['name'];
    $cacheFilePath = $cacheDir . '/rss2/' . md5( $currentSiteAccessName . $feedName ) . '.xml';

    if ( !is_dir( dirname( $cacheFilePath ) ) )
    {
        eZDir::mkdir( dirname( $cacheFilePath ), false, true );
    }

    $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );

    if ( !$cacheFile->exists() or ( time() - $cacheFile->mtime() > $cacheTime ) )
    {
        $rssContent = $RSSExport->tplRSS( $lastModified );
        $cacheFile->storeContents( $rssContent, 'rss2cache', 'xml' );
    }
    else
    {
        $lastModified = gmdate( 'D, d M Y H:i:s', $cacheFile->mtime() ) . ' GMT';

        if( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
        {
            $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];

            // Internet Explorer specific
            $pos = strpos( $ifModifiedSince, ';' );
            if ( $pos !== false )
            {
                $ifModifiedSince = substr( $ifModifiedSince, 0, $pos );
            }

            if( strcmp( $lastModified, $ifModifiedSince ) == 0 )
            {
                header( 'HTTP/1.1 304 Not Modified' );
                header( 'Last-Modified: ' . $lastModified );
                header( 'X-Powered-By: eZ Publish' );
                eZExecution::cleanExit();
           }
        }
        $rssContent = $cacheFile->fetchContents();
    }
}

// Set header settings
$httpCharset = eZTextCodec::httpCharset();
header( 'Last-Modified: ' . $lastModified );
header( 'Content-Type: application/rss+xml; charset=' . $httpCharset );
header( 'Content-Length: '.strlen( $rssContent ) );
header( 'X-Powered-By: eZ Publish' );

while ( @ob_end_clean() );

echo $rssContent;

eZExecution::cleanExit();

?>