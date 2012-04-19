<?php
/**
 * File containing the eZRssFunctionCollection class
 *
 * @version //autogentag//
 * @package LeZRSS
 * @copyright Copyright (C) 2008-2012 Guillaume Kulakowski and contributors
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

/**
 * The eZRssFunctionCollection provide RSS template function
 *
 * @package LeZRSS
 * @version //autogentag//
 */
class eZRssFunctionCollection
{
    /**
     * Constructor
     *
     */
    function __construct()
    {
    }

    /**
     * Fetch
     *
     * @param int $eZContentObjectTreeNodeID
     * @return multitype:NULL
     */
    function fetchList( $eZContentObjectTreeNodeID = null )
    {
        if ( is_null( $eZContentObjectTreeNodeID ) )
        {
            $result = array( 'result' => eZRSSExport::fetchList() );
        }
        else
        {
            $asObject = true;
            $conds = array();
            $conds['ezrss_export.status'] = eZRSSExport::STATUS_VALID;
            $conds['ezrss_export_item.source_node_id'] = $eZContentObjectTreeNodeID;

            $custom_fields = array( 'ezrss_export.*' );
            $custom_tables = array( 'ezrss_export_item' );
            $custom_conds = ' AND ezrss_export.id = ezrss_export_item.rssexport_id GROUP BY ezrss_export.id';

            $result = array( 'result' => eZPersistentObject::fetchObjectList( eZRSSExport::definition(), array(), $conds, null, null, $asObject, false, $custom_fields, $custom_tables, $custom_conds ) );
        }
        return $result;
    }
}

?>