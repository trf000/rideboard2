<?php

/**
 * WARNING: This module has been deprecated. It will no longer be
 * maintained by phpwebsite and no further bug/security patches will
 * be released. It will be removed from the phpWebsite distribution
 * at some point in the future.
 *
 * @deprecated since phpwebsite 1.8.0
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at appstate dot edu>
 */

//Deprecate::moduleWarning('whodis');

PHPWS_Core::initModClass('rideboard', 'Rideboard.php');
$rideboard = new Rideboard;

if (isset($_REQUEST['aop'])) {
    $rideboard->admin();
} else {
    $rideboard->user();
}

?>