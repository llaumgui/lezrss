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

class leZRSSExport extends eZRSSExport
{

/*!
     Get a RSS xml document based on the RSS 2.0 standard based on the RSS Export settings defined by this object

     \return RSS 2.0 XML document
    */
    function fetchRSS2_0()
    {
        $locale = eZLocale::instance();

        // Get URL Translation settings.
        $config = eZINI::instance();
        if ( $config->variable( 'URLTranslator', 'Translation' ) == 'enabled' )
        {
            $useURLAlias = true;
        }
        else
        {
            $useURLAlias = false;
        }

        if ( $this->attribute( 'url' ) == '' )
        {
            $baseItemURL = '';
            eZURI::transformURI( $baseItemURL, false, 'full' );
            $baseItemURL .= '/';
        }
        else
        {
            $baseItemURL = $this->attribute( 'url' ).'/'; //.$this->attribute( 'site_access' ).'/';
        }

        $tpl = templateInit();
        $tpl->setVariable( 'version', '2.0' );

        $channel = array(
            'atom_link' => array(
                'href', $baseItemURL . "rss/feed/" . $this->attribute( 'access_url' ),
                'rel', 'self',
                'type', 'application/rss+xml'
            ),
            'title' => $this->attribute( 'title' ),
            'link' => $this->attribute( 'url' ),
            'description' => $this->attribute( 'description' ),
            'language' => $locale->httpLocaleCode()
        );
echo 'rr(';
        return $tpl->fetch( 'design:rss2/channel.tpl' );

        $imageURL = $this->fetchImageURL();
        if ( $imageURL !== false )
        {
            $image = $doc->createElement( 'image' );

            $imageUrlNode = $doc->createElement( 'url' );
            $imageUrlNode->appendChild( $doc->createTextNode( $imageURL ) );
            $image->appendChild( $imageUrlNode );

            $imageTitle = $doc->createElement( 'title' );
            $imageTitle->appendChild( $doc->createTextNode( $this->attribute( 'title' ) ) );
            $image->appendChild( $imageTitle );

            $imageLink = $doc->createElement( 'link' );
            $imageLink->appendChild( $doc->createTextNode( $this->attribute( 'url' ) ) );
            $image->appendChild( $imageLink );

            $channel->appendChild( $image );
        }

        $cond = array(
                    'rssexport_id'  => $this->ID,
                    'status'        => $this->Status
                    );
        $rssSources = eZRSSExportItem::fetchFilteredList( $cond );

        $nodeArray = eZRSSExportItem::fetchNodeList( $rssSources, $this->getObjectListFilter() );

        if ( is_array( $nodeArray ) && count( $nodeArray ) )
        {
            $attributeMappings = eZRSSExportItem::getAttributeMappings( $rssSources );

            foreach ( $nodeArray as $node )
            {
                $object = $node->attribute( 'object' );
                $dataMap = $object->dataMap();
                if ( $useURLAlias === true )
                {
                    $nodeURL = $this->urlEncodePath( $baseItemURL . $node->urlAlias() );
                }
                else
                {
                    $nodeURL = $baseItemURL . 'content/view/full/' . $node->attribute( 'node_id' );
                }

                // keep track if there's any match
                $doesMatch = false;
                // start mapping the class attribute to the respective RSS field
                foreach ( $attributeMappings as $attributeMapping )
                {
                    // search for correct mapping by path
                    if ( $attributeMapping[0]->attribute( 'class_id' ) == $object->attribute( 'contentclass_id' ) and
                         in_array( $attributeMapping[0]->attribute( 'source_node_id' ), $node->attribute( 'path_array' ) ) )
                    {
                        // found it
                        $doesMatch = true;
                        // now fetch the attributes
                        $title =  $dataMap[$attributeMapping[0]->attribute( 'title' )];
                        $description =  $dataMap[$attributeMapping[0]->attribute( 'description' )];
                        // category is optional
                        $catAttributeIdentifier = $attributeMapping[0]->attribute( 'category' );
                        $category = $catAttributeIdentifier ? $dataMap[$catAttributeIdentifier] : false;
                        break;
                    }
                }

                if( !$doesMatch )
                {
                    // no match
                    eZDebug::writeWarning( __METHOD__ . ': Cannot find matching RSS source node for content object in '.__FILE__.', Line '.__LINE__ );
                    $retValue = null;
                    return $retValue;
                }

                $item = $doc->createElement( 'item' );

                // title RSS element with respective class attribute content
                $titleContent =  $title->attribute( 'content' );
                if ( $titleContent instanceof eZXMLText )
                {
                    $outputHandler = $titleContent->attribute( 'output' );
                    $itemTitleText = $outputHandler->attribute( 'output_text' );
                }
                else
                {
                    $itemTitleText = $titleContent;
                }

                $itemTitle = $doc->createElement( 'title' );
                $itemTitle->appendChild( $doc->createTextNode( $itemTitleText ) );
                $item->appendChild( $itemTitle );

                $itemLink = $doc->createElement( 'link' );
                $itemLink->appendChild( $doc->createTextNode( $nodeURL ) );
                $item->appendChild( $itemLink );

                $itemGuid = $doc->createElement( 'guid' );
                $itemGuid->appendChild( $doc->createTextNode( $nodeURL ) );
                $item->appendChild( $itemGuid );

                // description RSS element with respective class attribute content
                $descriptionContent =  $description->attribute( 'content' );
                if ( $descriptionContent instanceof eZXMLText )
                {
                    $outputHandler =  $descriptionContent->attribute( 'output' );
                    $itemDescriptionText = $outputHandler->attribute( 'output_text' );
                }
                else
                {
                    $itemDescriptionText = $descriptionContent;
                }

                $itemDescription = $doc->createElement( 'description' );
                $itemDescription->appendChild( $doc->createTextNode( $itemDescriptionText ) );
                $item->appendChild( $itemDescription );

                // category RSS element with respective class attribute content
                if ( $category )
                {
                    $categoryContent =  $category->attribute( 'content' );
                    if ( $categoryContent instanceof eZXMLText )
                    {
                        $outputHandler = $categoryContent->attribute( 'output' );
                        $itemCategoryText = $outputHandler->attribute( 'output_text' );
                    }
                    elseif ( $categoryContent instanceof eZKeyword )
                    {
                        $itemCategoryText = $categoryContent->keywordString();
                    }
                    else
                    {
                        $itemCategoryText = $categoryContent;
                    }

                    $itemCategory = $doc->createElement( 'category' );
                    $itemCategory->appendChild( $doc->createTextNode( $itemCategoryText ) );
                    $item->appendChild( $itemCategory );
                }

                $itemPubDate = $doc->createElement( 'pubDate' );
                $itemPubDate->appendChild( $doc->createTextNode( gmdate( 'D, d M Y H:i:s', $object->attribute( 'published' ) ) .' GMT' ) );

                $item->appendChild( $itemPubDate );

                $channel->appendChild( $item );
            }
        }

        return $doc;
    }

}

?>