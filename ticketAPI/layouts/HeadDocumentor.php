<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<head>
    <base href="<?php echo _HOST_ . _MAIN_DIRECTORY ?>">
    <title><?php echo \lib\Config::$_TITLE_APP ?></title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="favicon.ico">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <!-- Global CSS -->
    <?php
        Route::getCss(array("bootstrap.min"), "PrettyDocs", array("css", "bootstrap", "css"), FALSE);
    ?>
    <!-- Plugins CSS -->
    <?php
        Route::getCss(array("font-awesome"), "PrettyDocs", array("css","fontawesome", "css"), FALSE);
        Route::getCss(array("style"), "PrettyDocs", array("css","elegantfont"), FALSE);
    ?>
    <!-- Theme CSS -->
    <?php
        Route::getCss(array("styles"), "PrettyDocs", array("css"), FALSE);
    ?>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="landing-page">

    <div class="page-wrapper">
        <!-- ******Header****** -->
        <header class="header text-center">
            <div class="container">
                <div class="branding">
                    <h1 class="logo">
                        <span aria-hidden="true" class="icon_documents_alt icon"></span>
                        <span class="text-highlight">CrowPHP</span><span class="text-bold">Docs</span>
                    </h1>
                </div><!--//branding-->
                <div class="tagline">
                    <p>Free Framework in PHP for your applications</p>
                </div><!--//tagline-->
            </div><!--//container-->
        </header><!--//header-->