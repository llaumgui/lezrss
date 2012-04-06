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
     * @return multitype:NULL
     */
    function fetchList()
    {
        $result = array( 'result' => eZRSSExport::fetchList() );
        return $result;
    }
}

?>