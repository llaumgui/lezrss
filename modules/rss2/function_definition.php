<?php
/**
 * File containing the function definition
 *
 * @version //autogentag//
 * @package LeZRSS
 * @copyright Copyright (C) 2008-2012 Guillaume Kulakowski and contributors
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

$FunctionList = array();
$FunctionList['list'] = array(
    'name' => 'list',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'include_file' => 'extension/lezrss/modules/rss2/ezrssfunctioncollection.php',
        'class' => 'eZRssFunctionCollection',
        'method'  => 'fetchList'
    ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'node_id',
            'type' => 'integer',
            'required' => false,
            'default' => null
        )
    )
);

?>