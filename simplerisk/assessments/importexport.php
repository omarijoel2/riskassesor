<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Include required functions file
require_once(realpath(__DIR__ . '/../includes/functions.php'));
require_once(realpath(__DIR__ . '/../includes/authenticate.php'));
require_once(realpath(__DIR__ . '/../includes/display.php'));
require_once(realpath(__DIR__ . '/../includes/alerts.php'));

// Include Zend Escaper for HTML Output Encoding
require_once(realpath(__DIR__ . '/../includes/Component_ZendEscaper/Escaper.php'));
$escaper = new Zend\Escaper\Escaper('utf-8');

// Add various security headers
add_security_headers();

// Session handler is database
if (USE_DATABASE_FOR_SESSIONS == "true")
{
session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc');
}

// Start the session
session_set_cookie_params(0, '/', '', isset($_SERVER["HTTPS"]), true);

if (!isset($_SESSION))
{
    session_name('SimpleRisk');
    session_start();
}

// Include the language file
require_once(language_file());

require_once(realpath(__DIR__ . '/../includes/csrf-magic/csrf-magic.php'));

// Check for session timeout or renegotiation
session_check();

// Check if access is authorized
if (!isset($_SESSION["access"]) || $_SESSION["access"] != "granted")
{
    set_unauthenticated_redirect();
    header("Location: ../index.php");
    exit(0);
}

// Check if access is authorized
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != "1")
{
    header("Location: ../index.php");
    exit(0);
}

// Check if assessment extra is enabled
if(assessments_extra())
{
    // Include the assessments extra
    require_once(realpath(__DIR__ . '/../extras/assessments/index.php'));
}
else
{
    header("Location: ../index.php");
    exit(0);
}

// Process assessment import
if(process_assessment_import()){
    refresh();
}

?>

<!doctype html>
<html>

  <head>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/sorttable.js"></script>
    <title>SimpleRisk: Enterprise Risk Management Simplified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/bootstrap-responsive.css">

    <link rel="stylesheet" href="../css/divshot-util.css">
    <link rel="stylesheet" href="../css/divshot-canvas.css">
    <link rel="stylesheet" href="../css/display.css">

    <link rel="stylesheet" href="../bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/theme.css">
  </head>

  <body>

<?php
    view_top_menu("Assessments");

    // Get any alert messages
    get_alert();
?>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span3">
                <?php view_assessments_menu("AssessmentImportexport"); ?>
            </div>
            <div class="span9">
                <div class="hero-unit">
                    <?php display_import_of_assessment(); ?>
                </div>
                <div class="hero-unit">
                    <?php display_export_of_assessment(); ?>
                </div>
            </div>
        </div>
    </div>
  </body>

</html>
