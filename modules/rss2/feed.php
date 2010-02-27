<?php
//
// Created on: <01-Sep-2008 19:00:00 GKUL>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: leZRSS
// SOFTWARE RELEASE: 1.0
// BUILD VERSION:
// COPYRIGHT NOTICE: Copyright (c) 2008-2010 Guillaume Kulakowski and contributors
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

$Module = $Params['Module'];
$rssIni = eZINI::instance('lezrss.ini');

// Add default rss feed
if ( !isset ( $Params['RSSFeed'] ) )
{
    $feedName = $rssIni->variable('RSSSettings', 'DefaultRSS');
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
$cacheTime = 0; // for testing

$lastModified = gmdate( 'D, d M Y H:i:s', time() ) . ' GMT';

if ( $cacheTime <= 0 )
{
    $rssContent = $RSSExport->tplRSS( $lastModified );
}
else
{
    $cacheDir = eZSys::cacheDirectory();
    $currentSiteAccessName = $GLOBALS['eZCurrentAccess']['name'];
    $cacheFilePath = $cacheDir . '/rss/' . md5( $currentSiteAccessName . $feedName ) . '.xml';

    if ( !is_dir( dirname( $cacheFilePath ) ) )
    {
        eZDir::mkdir( dirname( $cacheFilePath ), false, true );
    }

    $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );

    if ( !$cacheFile->exists() or ( time() - $cacheFile->mtime() > $cacheTime ) )
    {
        $xmlDoc = $RSSExport->attribute( 'rss-xml' );
        // Get current charset
        $charset = eZTextCodec::internalCharset();
        $rssContent = $xmlDoc->saveXML();
        $cacheFile->storeContents( $rssContent, 'rsscache', 'xml' );
    }
    else
    {
        $lastModified = gmdate( 'D, d M Y H:i:s', $cacheFile->mtime() ) . ' GMT';

        if( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
        {
            $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];

            // Internet Explorer specific
            $pos = strpos($ifModifiedSince,';');
            if ( $pos !== false )
                $ifModifiedSince = substr( $ifModifiedSince, 0, $pos );

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
header( 'Content-Length: '.strlen($rssContent) );
header( 'X-Powered-By: eZ Publish' );

while ( @ob_end_clean() );

echo $rssContent;

eZExecution::cleanExit();

?>