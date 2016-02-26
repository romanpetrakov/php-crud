<?php

require_once '../vendor/autoload.php';

\OLOG\ConfWrapper::assignConfig(\CRUDDemo\Config::get());

\OLOG\Router::match3(\CRUDDemo\MainPageController::mainPageAction(\OLOG\Router::GET_METHOD));

// TODO: move routing to CRUD
\OLOG\Router::match3(\OLOG\CRUD\CRUDController::listAction(\OLOG\Router::GET_METHOD));
\OLOG\Router::match3(\OLOG\CRUD\CRUDController::addAction(\OLOG\Router::GET_METHOD));
\OLOG\Router::match3(\OLOG\CRUD\CRUDController::editAction(\OLOG\Router::GET_METHOD));