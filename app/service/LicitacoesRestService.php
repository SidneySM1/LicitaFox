<?php
class LicitacoesRestService extends AdiantiRecordService
{
 const DATABASE = 'licitacoesdb';
 const ACTIVE_RECORD = 'licitacoes';

    public static function getBetween( $request )
    {
        TTransaction::open('licitacoesdb');
        $response = array();

        // carrega os contatos
        $all = Licitacoes::where('id', '>=', $request['from'])
        ->where('id', '<=', $request['to'])
        ->load();
        foreach ($all as $product)
        {
            $response[] = $product->toArray();
        }
        TTransaction::close();
        return $response;
    }
    public static function getObjeto($request){
        TTransaction::open('licitacoesdb');
        $response = array();

        // carrega os contatos "%{$data->palavra_chave}%"
        $all = Licitacoes::where('objeto', 'like', "%{$request['objeto']}%")
                                ->where('estado', '=', $request['estado'])
                                ->load();
        foreach ($all as $product)
        {
            $response[] = $product->toArray();
        }
        TTransaction::close();
        return $response;
    }
}
