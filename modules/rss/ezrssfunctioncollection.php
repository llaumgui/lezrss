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

/*!
 http://issues.ez.no/9933
 */

class eZRssFunctionCollection
{
    /*!
     Constructor
    */
    function __construct()
    {
    }

    function fetchList()
    {
        //include_once( 'kernel/classes/ezrssexport.php' );
        $result = array( 'result' => eZRSSExport::fetchList() );
        return $result;
    }
}

?>