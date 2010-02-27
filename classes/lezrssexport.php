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

  static function definition()
  {
        $definition = parent::definition();
        $definition['class_name'] = 'leZRSSExport';
        return $definition;
    }



    /**
     * Fetches the RSS Export by ID.
     *
     * @param RSS Export ID
     * @return leZRSSExport
     */
    static function fetch( $id, $asObject = true, $status = leZRSSExport::STATUS_VALID )
    {
        return eZPersistentObject::fetchObject( leZRSSExport::definition(),
                                                null,
                                                array( "id" => $id, 'status' => $status ),
                                                $asObject );
    }



    /**
     * Fetches the RSS Export by feed access url and is active.
     *
     * @param RSS Export access url
     * @return leZRSSExport
     */
    static function fetchByName( $access_url, $asObject = true )
    {
        return eZPersistentObject::fetchObject( leZRSSExport::definition(),
                                                null,
                                                array( 'access_url' => $access_url,
                                                       'active' => 1,
                                                       'status' => 1 ),
                                                $asObject );
    }



    /**
     * Get a RSS xml document based on rss2 template based on the RSS Export settings defined by this object
     *
     * @return RSS XML document
     */
    function tplRSS( $lastModified = null )
    {
    	if ( is_null($lastModified) )
    	   $lastModified = gmdate( 'D, d M Y H:i:s', time() ) . ' GMT';

    	// eZP 4.3
        if ( is_callable( array( 'eZTemplate', 'factory') ) )
        {
	        $tpl = ezTemplate::factory();
        }
        // Deprecated on eZP 4.3
        else
        {
            include_once( 'kernel/common/template.php' );
            $tpl = templateInit();
        }

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

        $metaDataArray = $config->variable('SiteSettings','MetaDataArray');

        /*
         * Channel informations
         */
        $channel = array(
            'atom:link' => array(
                'href' => $baseItemURL . "rss/feed/" . $this->attribute( 'access_url' ),
                'rel' => 'self',
                'type' => 'application/rss+xml'
            ),
            'title' => $this->attribute( 'title' ),
            'link' => $this->attribute( 'url' ),
            'description' => $this->attribute( 'description' ),
            'language' => $locale->httpLocaleCode(),
            'pubDate' => $lastModified,
            'copyright' => $metaDataArray['copyright'],
            'generator' => 'eZ Publish (leZRSS)'
        );

        $imageURL = $this->fetchImageURL();
        if ( $imageURL !== false )
        {
            $channel['image'] = array(
                'url' => $imageURL,
                'title' => $this->attribute( 'title' ),
                'link' => $this->attribute( 'url' )
            );
        }
        $tpl->setVariable('channel', $channel );

        /*
         * Items informations
         */
        $cond = array(
                    'rssexport_id'  => $this->ID,
                    'status'        => $this->Status
                    );
        $rssSources = eZRSSExportItem::fetchFilteredList( $cond );

        $nodeArray = eZRSSExportItem::fetchNodeList( $rssSources, $this->getObjectListFilter() );
        $items = array();

        if ( is_array( $nodeArray ) && count( $nodeArray ) )
        {
            $attributeMappings = eZRSSExportItem::getAttributeMappings( $rssSources );

            foreach ( $nodeArray as $key => $node )
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

                // category RSS element with respective class attribute content
                $itemCategoryText = '';
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
                }

                $itemPubDate = gmdate( 'D, d M Y H:i:s', $object->attribute( 'published' ) ) .' GMT';

                $items[$key] = array(
                    'title' => $itemTitleText,
                    'link' => $nodeURL,
                    'guid' => $nodeURL,
                    'description' => $itemDescriptionText,
                    'category' => $itemCategoryText,
                    'pubDate' => $itemPubDate,
                );
            }
        }
        $tpl->setVariable( 'node_array', $nodeArray );
        $tpl->setVariable( 'items', $items );
        return $tpl->fetch('design:rss2/channel.tpl');
    }

}

?>