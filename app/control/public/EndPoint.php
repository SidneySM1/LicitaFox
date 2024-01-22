<?php
/**
 * PublicView
 *
 * @version    7.6
 * @package    control
 * @subpackage public
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-template
 */
class EndPoint extends TPage
{
    private $token = '1234';

    public function __construct()
    {
        parent::__construct();
        
        //$html = new THtmlRenderer('app/resources/public.html');

        // replace the main section variables
        //$html->enableSection('main', array());
        
        //$panel = new TPanelGroup('Public!');
        //$panel->add($html);
        
        // add the template to the page
        //parent::add( $panel );

        echo "Oi \n";
        //Avaliar chamada
        $this->apiStart();
    }
    function apiStart(){
        if (!isset($_GET["token"])){
            echo 'token invalido/ausente';
        } else{
            echo 'Bem vindo';
        }
    }
}
