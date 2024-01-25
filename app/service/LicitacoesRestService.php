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

            $dataFim = !empty($request['aberturaFim']) ? $request['aberturaFim'] : '2124-12-31';
            
            if (isset($request['abertura']) AND ($request['abertura'] != '')){
                $all = Licitacoes::where('estado', '=', $request['estado'])
                                    ->where('objeto', 'like', "%{$request['objeto']}%")
                                    ->where('abertura', '>=', $request['abertura'] . ' 00:00')
                                    ->where('abertura', '<=', $dataFim . ' 23:59')
                                    ->load();
            }
            else{
                $all = Licitacoes::where('estado', '=', $request['estado'])
                                    ->where('objeto', 'like', "%{$request['objeto']}%")
                                    ->load();
            }
            
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
