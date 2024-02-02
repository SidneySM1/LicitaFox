<?php

class sif_est extends TRecord
{
    const TABLENAME  = 'sif_est';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'max'; // {max, serial}
    private $sis_cli;
    //const CREATEDAT = 'created_at';
    //const UPDATEDAT = 'updated_at';

   /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addattribute('emp');
        parent::addattribute('cli');
        parent::addattribute('est');
        parent::addattribute('vig');
        parent::addattribute('den');
        parent::addattribute('des');
        parent::addattribute('fan');
        parent::addattribute('tip');
        parent::addattribute('tci');
        parent::addattribute('con');
        parent::addattribute('fre');
        parent::addattribute('ven');
        parent::addattribute('end');
        parent::addattribute('bai');
        parent::addattribute('cep');
        parent::addattribute('cid');
        parent::addattribute('tel');
        parent::addattribute('fax');
        parent::addattribute('cgc');
        parent::addattribute('cgf');
        parent::addattribute('ins');
        parent::addattribute('rep');
        parent::addattribute('etq');
        parent::addattribute('nas');
        parent::addattribute('abc');
        parent::addattribute('csg');
        parent::addattribute('loc');
        parent::addattribute('nat');
        parent::addattribute('cfo');
        parent::addattribute('ntf');
        parent::addattribute('dat');
        parent::addattribute('pag');
        parent::addattribute('num');
        parent::addattribute('tx1');
        parent::addattribute('un1');
        parent::addattribute('vl1');
        parent::addattribute('tx2');
        parent::addattribute('un2');
        parent::addattribute('vl2');
        parent::addattribute('tx3');
        parent::addattribute('un3');
        parent::addattribute('vl3');
        parent::addattribute('tx4');
        parent::addattribute('un4');
        parent::addattribute('vl4');
        parent::addattribute('tx5');
        parent::addattribute('un5');
        parent::addattribute('vl5');
        parent::addattribute('tx6');
        parent::addattribute('un6');
        parent::addattribute('vl6');
        parent::addattribute('tx7');
        parent::addattribute('un7');
        parent::addattribute('vl7');
        parent::addattribute('tx8');
        parent::addattribute('un8');
        parent::addattribute('vl8');
        parent::addattribute('tx9');
        parent::addattribute('un9');
        parent::addattribute('vl9');
        parent::addattribute('sit');
        parent::addattribute('flg');
        parent::addattribute('rec');
        parent::addattribute('blo');
        parent::addattribute('rin');
        parent::addattribute('ini');
        parent::addattribute('ter');
        parent::addattribute('esc');
        parent::addattribute('ob1');
        parent::addattribute('ob2');
        parent::addattribute('val');
        parent::addattribute('ref');
        parent::addattribute('tic');
        parent::addattribute('ban');
        parent::addattribute('tge');
        parent::addattribute('tie');
        parent::addattribute('npc');
        parent::addattribute('fnr');
        parent::addattribute('fpu');
        parent::addattribute('fsa');
        parent::addattribute('fso');
        parent::addattribute('fma');
        parent::addattribute('ftr');
        parent::addattribute('frf');
        parent::addattribute('ffa');
        parent::addattribute('foc');
        parent::addattribute('fda');
        parent::addattribute('fdc');
        parent::addattribute('fdf');
        parent::addattribute('ffi');
        parent::addattribute('fft');
        parent::addattribute('aif');
        parent::addattribute('aip');
        parent::addattribute('aie');
        parent::addattribute('aio');
        parent::addattribute('aig');
        parent::addattribute('aid');
        parent::addattribute('fl1');
        parent::addattribute('fl2');
        parent::addattribute('fl3');
        parent::addattribute('fl4');
        parent::addattribute('fl5');
        parent::addattribute('fl6');
        parent::addattribute('fl7');
        parent::addattribute('fl8');
        parent::addattribute('fl9');
        parent::addattribute('imp');
        parent::addattribute('bco');
        parent::addattribute('dia');
        parent::addattribute('apr');
        parent::addattribute('ant');
        parent::addattribute('eqv');
        parent::addattribute('ob3');
        parent::addattribute('ob4');
        parent::addattribute('com');
        parent::addattribute('rci');
        parent::addattribute('tri');
        parent::addattribute('mes');
        parent::addattribute('ret');
        parent::addattribute('cuf');
        parent::addattribute('ob5');
        parent::addattribute('ob6');
        parent::addattribute('qt1');
        parent::addattribute('qt2');
        parent::addattribute('qt3');
        parent::addattribute('qt4');
        parent::addattribute('qt5');
        parent::addattribute('qt6');
        parent::addattribute('qt7');
        parent::addattribute('qt8');
        parent::addattribute('qt9');
        parent::addattribute('fed');
        parent::addattribute('fet');
        parent::addattribute('tot');
        parent::addattribute('bra');
        parent::addattribute('se1');
        parent::addattribute('se2');
        parent::addattribute('se3');
        parent::addattribute('se4');
        parent::addattribute('se5');
        parent::addattribute('se6');
        parent::addattribute('se7');
        parent::addattribute('se8');
        parent::addattribute('se9');
        parent::addattribute('isv');
        parent::addattribute('ser');
        parent::addattribute('mel');
        parent::addattribute('na2');
        parent::addattribute('tis');
        parent::addattribute('iss');
        parent::addattribute('cna');
        parent::addattribute('atv');
        parent::addattribute('etb');
        parent::addattribute('lps');
        parent::addattribute('ise');
        parent::addattribute('fal');
        parent::addattribute('bas');
        parent::addattribute('bru');
        parent::addattribute('lib');
        parent::addattribute('ult');
        parent::addattribute('banide');
        parent::addattribute('sifemi');
        parent::addattribute('sifval');
        parent::addattribute('sifliq');
        parent::addattribute('sifidy');
        parent::addattribute('bantitemi');
        parent::addattribute('bantitval');
        parent::addattribute('bantitnos');
        parent::addattribute('bantitrea');
        parent::addattribute('bantitpag');
        parent::addattribute('banflg');
        parent::addattribute('banope');
        parent::addattribute('sifdoc');
        parent::addattribute('bantitdoc');
        parent::addattribute('bantitdf1');
        parent::addattribute('bantitdf2');
        parent::addattribute('ultmes');
        parent::addattribute('ultnfs');
        parent::addattribute('ultemi');
        parent::addattribute('ultval');
        parent::addattribute('ultliq');
        parent::addattribute('extra');
        parent::addattribute('avaven');
        parent::addattribute('avames');
        parent::addattribute('ultven');
        parent::addattribute('cei');
        parent::addattribute('efd');
        parent::addattribute('cpf');
        parent::addattribute('idy');
    }
           
    /**
    * Executado sempre que a propriedade ->city é acessada
    */
    
    public function get_sis_cli_des()
    {
        if (empty($this->sis_cli))
        {
            //$this->SIS_CLI = new sis_cli($this->id);
            
            $this->sis_cli = sis_cli::where('cli','=', $this->cli)->where('emp','=', $this->emp)->first();
            //echo var_dump($this->sis_cli);
            
        }
        return $this->sis_cli->des;
        
    }
    
   
    public static function get_sis_cli_des2()
    {
        $repository = new TRepository('sis_cli');
        //$wcli = $this->CLI;
        return $repository->where('cli', '=', $wcli)->load();
    }    
        
    
    public function onBeforeLoad($id)
    {
        //file_put_contents('/tmp/log1.txt', "onBeforeLoad: $id\n", FILE_APPEND);
    }
    public function onAfterLoad($object)
    {
        //file_put_contents('/tmp/log2.txt', 'onAfterLoad:' . json_encode($object)."\n", FILE_APPEND);
    }
    public function onBeforeStore($object)
    {
        //file_put_contents('/tmp/log1.txt', 'onBeforeStore:' .json_encode($object)."\n", FILE_APPEND);
    }
    public function onAfterStore($object)
    {
        //file_put_contents('/tmp/log2.txt', 'onAfterStore:' .json_encode($object)."\n", FILE_APPEND);
    }
    public function onBeforeDelete($object)
    {
        //file_put_contents('/tmp/log.txt', 'onBeforeDelete:' .json_encode($object)."\n", FILE_APPEND);
    }
    public function onAfterDelete($object)
    {
        //file_put_contents('/tmp/log.txt', 'onAfterDelete:' .json_encode($object)."\n", FILE_APPEND);
    }
        
    
 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
        
}

