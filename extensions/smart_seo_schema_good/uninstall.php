<?php
/*------------------------------------------------------------------------------
  Smart SEO Schema Assistant - Uninstallation Script
  
  Cleans up extension data and logs uninstallation
  Based on AvaTax Integration pattern
------------------------------------------------------------------------------*/

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

/**
 * @var AController $this
 */

// Additional cleanup if needed (cache, temporary files, etc.)
// The SQL uninstall.sql will handle database cleanup

// Log uninstallation
$warning = new AWarning('Smart SEO Schema extension uninstalled successfully. All data removed.');
$warning->toLog();