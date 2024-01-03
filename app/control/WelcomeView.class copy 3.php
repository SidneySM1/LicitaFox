<?php

class WelcomeView extends TPage
{
    private $formNoticias;
    private $formLicitacoes;

    public function __construct()
    {
        parent::__construct();

        $this->formNoticias = new BootstrapFormBuilder('form_noticias');
        $this->formNoticias->setFormTitle('Notícias sobre Licitações');
        $this->formNoticias->generateAria();

        $this->formLicitacoes = new BootstrapFormBuilder('form_licitacoes');
        $this->formLicitacoes->setFormTitle('Minhas Últimas Licitações');
        $this->formLicitacoes->generateAria();

        parent::add($this->formNoticias);
        parent::add($this->formLicitacoes);
    }

    public function onReload()
    {
        try
        {
            $apiKey = 'e16c0d9d5d3341b59ea58fc3982fd3b0';
            $apiUrl = "https://newsapi.org/v2/everything?q=licitacoes&from=2023-12-03&sortBy=publishedAt&apiKey={$apiKey}";

            $curl = curl_init($apiUrl);
            $headers = [
                'Content-Type: application/json',
                'User-Agent: micromoney',
            ];
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            curl_close($curl);

            $noticias = json_decode($response);
            var_dump($noticias);
            foreach ($noticias->articles as $article)
            {
                $this->formNoticias->addContent([new TLabel("Título: " . $article->title)]);
                $this->formNoticias->addContent([new TLabel("Descrição: " . $article->description)]);
                $urlButton = new TButton("urlButton");
                $urlButton->setProperty('onclick', "window.open('$article->url', '_blank')");
                $urlButton->setLabel("Abrir Link");
                $this->formNoticias->addContent([$urlButton]);
                $this->formNoticias->addContent([new TLabel("Descrição: " . $article->publishedAt)]);
                $this->formNoticias->addContent([new TElement('hr')]);
            }

            
            TTransaction::open('licitacoes');

            $repository = new TRepository('MinhasLicitacoes');

            $criteria = new TCriteria;
            $criteria->setProperty('order', 'abertura_datetime DESC');
            $criteria->setProperty('limit', 5);

            $objects = $repository->load($criteria); 

            var_dump($objects);
            if ($objects) {
                
                foreach ($objects as $obj) {
                    
                    $this->formLicitacoes->addContent([new TLabel("Título: " . $obj->titulo)]);
                    $this->formLicitacoes->addContent([new TLabel("Descrição: " . $obj->objeto)]);
                    $urlButton = new TButton("urlButton");
                    $urlButton->setProperty('onclick', "window.open('$obj->linkExterno', '_blank')");
                    $urlButton->setLabel("Abrir Link");
                    $this->formLicitacoes->addContent([$urlButton]);
                    $this->formLicitacoes->addContent([new TLabel("Abertura: " . $obj->abertura)]);
                    $this->formLicitacoes->addContent([new TElement('hr')]);
                    
                }
            }

            TTransaction::close(); 
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    function show()
    {
        $this->onReload();
        parent::show();
    }
}
?>
