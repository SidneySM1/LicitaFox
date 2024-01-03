<?php
// arquivo MinhaLicitacao.php
class MinhaLicitacao extends TRecord
{
    const TABLENAME = 'minhas_licitacoes'; // Nome da tabela
    const PRIMARYKEY= 'id_licitacao'; // Chave primária
    const IDPOLICY =  'max'; // Política de IDs

    // Propriedades correspondentes aos campos da tabela
    public $id_licitacao;
    public $titulo;
    public $municipio_IBGE;
    public $uf;
    public $orgao;
    public $abertura_datetime;
    public $objeto;
    public $link;
    public $linkExterno;
    public $municipio;
    public $abertura;
    public $aberturaComHora;
    public $id_tipo;
    public $tipo;
    public $valor;
    public $id_portal;
    public $emailContato;
    public $status; //atualizado posteriormente pelo usuario

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('titulo');
        parent::addAttribute('municipio_IBGE');
        parent::addAttribute('uf');
        parent::addAttribute('orgao');
        parent::addAttribute('abertura_datetime');
        parent::addAttribute('objeto');
        parent::addAttribute('link');
        parent::addAttribute('linkExterno');
        parent::addAttribute('municipio');
        parent::addAttribute('abertura');
        parent::addAttribute('aberturaComHora');
        parent::addAttribute('id_tipo');
        parent::addAttribute('tipo');
        parent::addAttribute('valor');
        parent::addAttribute('id_portal');
        parent::addAttribute('emailContato');
        parent::addAttribute('status');//inserido posteriormente pelo usuario.
    }

    // Aqui você pode implementar métodos específicos da sua classe,
    // como validações, transformações e lógica relacionada a negócios.
}
?>