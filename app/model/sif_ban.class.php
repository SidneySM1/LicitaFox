<?php
/**
 * sis_cli Active Record
 * @author  <your-name-here>
 */
class sif_ban extends TRecord
{
    const TABLENAME = 'sif\sif_ban';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addattribute('emp');
        parent::addattribute('ban');
        parent::addattribute('des');
        parent::addattribute('sal');        
    }


}
