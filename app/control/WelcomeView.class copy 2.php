<?php
//e16c0d9d5d3341b59ea58fc3982fd3b0
class WelcomeView extends TPage
{
    private $form; // form

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_noticias');
        $this->form->setFormTitle('Notícias sobre Licitações');

        parent::add($this->form);
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

            // Decodifica a resposta JSON
            $noticias = json_decode($response);
            var_dump($noticias);
            foreach($noticias->articles as $article)
            {
                $this->form->addContent([new TLabel("Título: " . $article->title)]);
                $this->form->addContent([new TLabel("Descrição: " . $article->description)]);
                $urlButton = new TButton("urlButton");
                $urlButton->setProperty('onclick', "window.open('$article->url', '_blank')");
                $urlButton->setLabel("Abrir Link");
                $this->form->addContent([$urlButton]);
                $this->form->addContent([new TLabel("Descrição: " . $article->publishedAt)]);
                $this->form->addContent([new TElement('hr')]);
            }
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