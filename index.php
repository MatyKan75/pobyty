<?php
	session_start();
    session_regenerate_id();

    $app_name = "In-Life Pobyty";
    $app_version = "3.0.6";

    mb_internal_encoding("UTF-8");

    function classAutoloader($class_name) {
        // Končí název třídy řetězcem "Ctrl" ?
        if (preg_match('/Ctrl$/', $class_name))
            require("controls/" . $class_name . ".php");
        else
            require("models/" . $class_name . ".php");
	}

    spl_autoload_register("classAutoloader");
    
    $GLOBALS['Logger'] = new Logger();
    $GLOBALS['Logger']->open(true);
    $GLOBALS['Logger']->info("Aplikace $app_name $app_version byla spuštěna.");
    
    DatabaseHelper::connect();

    $router = new RouterCtrl();
    $router->process(array($_SERVER['REQUEST_URI']));
    $router->displayView();

?>