<?php
require_once 'request.php';
try
{
 $location = 'http://localhost/licitafox/rest.php';
 $parameters = [];
 $parameters['class'] = 'LicitacoesRestService';
 $parameters['method'] = 'loadAll';
 //$parameters['id'] = '1';
 $parameters['filters'] = [ ['id', '<', 3] ];
 print_r(request($location, 'GET', $parameters));
}
catch (Exception $e)
{
 echo 'Error: '. $e->getMessage();
}
catch (Exception $e)
{
 echo 'Error: '. $e->getMessage();
}
