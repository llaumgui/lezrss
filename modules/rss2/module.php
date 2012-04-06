<?php
/**
 * File containing the module definition
 *
 * @version //autogentag//
 * @package LeZRSS
 * @copyright Copyright (C) 2008-2012 Guillaume Kulakowski and contributors
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

$Module = array( 'name' => 'leZRSS : eZRSS improvement' );
$ViewList['feed'] = array(
    'script' => 'feed.php',
    'functions' => array( 'feed' ),
    'params' => array ( 'RSSFeed' ) );


$FunctionList = array( );
$FunctionList['feed'] = array();

?>