<?php
/**
 * File containing the leZRSSExport class
 *
 * @version //autogentag//
 * @package LeZRSS
 * @copyright Copyright (C) 2008-2012 Guillaume Kulakowski and contributors
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

/**
 * The leZRSSExport class extends eZRSSExport to provide better functionnalities
 *
 * @package LeZRSS
 * @version //autogentag//
 */
class leZRSSExport extends eZRSSExport
{

    /**
     * Provide eZPersistentObject definition
     *
     * @return string
     */
    static function definition()
    {
        $definition = parent::definition();
        $definition['class_name'] = 'leZRSSExport';

        return $definition;
    }



    /**
     * Fetches the RSS Export by ID.
     *
     * @param integed $id
     * @param boolean $asObject
     * @param integer $status
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
     * @param string $access_url
     * @param boolean $asObject
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
     * @param string $lastModified
     * @return NULL
     */
    function tplRSS( $lastModified = null )
    {
        if ( is_null( $lastModified ) )
        {
            $lastModified = gmdate( 'D, d M Y H:i:s', time() ) . ' GMT';
        }

        $tpl = eZTemplate::factory();
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

        $metaDataArray = $config->variable( 'SiteSettings', 'MetaDataArray' );

        /*
         * Channel informations
         */
        $channel = array(
            'atom:link' => array(
                'href' => $baseItemURL . "rss2/feed/" . $this->attribute( 'access_url' ),
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
        $tpl->setVariable( 'channel', $channel );

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
        return $tpl->fetch( 'design:rss2/channel.tpl' );
    }

}

?>
