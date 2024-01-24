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
    public static function getFox($request)
    {
        if (isset($request['estado']) AND ($request['estado'] != '')){
            TTransaction::open('licitacoesdb');
            $response = array();
            
            $all = Licitacoes::where('estado', '=', $request['estado'])
                                    ->where('objeto', 'like', "%{$request['objeto']}%")
                                    ->load();

            foreach ($all as $product)
            {
                $response[] = $product->toArray();
            }

            TTransaction::close();
            return $response;
        } else {
            throw new Exception("Estado é obrigatório");
        }
        
    }
}
