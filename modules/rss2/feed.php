<?php
//
// Created on: <01-Sep-2008 19:00:00 GKUL>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: leZRSS
// SOFTWARE RELEASE: 0.9
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

include_once( 'kernel/common/template.php' );
$rssId = $Params['RssId'];
$sectionName = 'Global';

$tpl = templateInit();
$ini = eZINI::instance('site.ini');
$rssIni = eZINI::instance('rss.ini');
$Module = $Params["Module"];



/*
 * On détermine le nodeID
 */
if ( $rssId )
{
    $nodeID = $rssId;
}
else
{
    $uriString = '';

    if ( array_key_exists(0, $Module->OriginalParameters) )
    {
        $uriString = $Module->OriginalParameters[0];
    }

    /* Prefixe */
    if ( $rssIni->hasVariable( 'RSSSettings', 'PathPrefix' )
      && $rssIni->variable( 'RSSSettings', 'PathPrefix' ) != ''  )
    {
        $uriString = eZURLAliasML::cleanURL( $rssIni->variable( 'RSSSettings', 'PathPrefix' ) ) . $uriString;
    }

    eZURLAliasML::cleanURL($uriString);

    if ( empty( $uriString ) )
    {
        $uri = eZURI::instance( $ini->variable( 'SiteSettings', 'IndexPage') );
        $url = $uri->elements();
        $url = eZURLAliasML::urlToAction( $url );
        $nodeID = eZURLAliasML::nodeIDFromAction( $url );
    }
    else
    {
       $uri = eZURI::instance($uriString);
       eZURLAliasML::translate($uri);
       $url = $uri->elements();
       $url = eZURLAliasML::urlToAction( $url );
       $nodeID = eZURLAliasML::nodeIDFromAction( $url );
    }
}


/*
 * Configuration spécifique
 */
if ( $rssIni->hasSection('NodeID_'.$nodeID) )
{
    $sectionName = 'NodeID_'. $nodeID;
}
$feedTitle = $rssIni->variable( $sectionName, "FeedTitle" );
$feedDescription = $rssIni->variable( $sectionName, "FeedDescription" );
$feedImage = $rssIni->variable( $sectionName, "FeedImage" );
$feedLimit = $rssIni->variable( $sectionName, "FeedLimit" );

$node = eZContentObjectTreeNode::fetch( $nodeID );


/*
 * On affecte les templates
 */
$tpl->setVariable( 'node', $node );
$tpl->setVariable( 'title', $feedTitle );
$tpl->setVariable( 'description', $feedDescription );
$tpl->setVariable( 'image', $feedImage );
$tpl->setVariable( 'limit', $feedLimit );


/* headers */
$httpCharset = eZTextCodec::httpCharset();
header( 'Content-Type: text/xml; charset=' . $httpCharset );

$Result = array();
$Result['pagelayout'] = 'feed/rss_pagelayout.tpl';
$Result['content'] = $tpl->fetch( 'design:feed/channel_full.tpl' );


?>