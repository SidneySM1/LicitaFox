<?php
require_once 'request.php';
try
{
    $location = 'http://localhost/licitafox/rest.php';
    $parameters = array();
    $parameters['class'] = 'LicitacoesRestService';
    $parameters['method'] = 'getObjeto';
    $parameters['objeto'] = 'informatica';
    $parameters['estado'] = 'CE';
    //$parameters['to'] = '3';
    print_r(request($location, 'GET', $parameters));
}
catch (Exception $e)
{
    echo 'Error: '. $e->getMessage();
}