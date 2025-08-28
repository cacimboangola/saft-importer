<?php

namespace App\Http\Controllers;

use App\Models\DocLinha;
use App\Models\Docs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocLinhaAccessController extends Controller
{
    /**
     * Exibe a página para buscar linhas de documentos e gerar SQL para Access
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('doc-linha-access.index');
    }

    /**
     * Busca as linhas de documentos com base no ID da fatura
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string',
            'access_factura_id' => 'nullable|numeric'
        ]);

        $invoiceId = $request->input('invoice_number');
        $accessFacturaId = $request->input('access_factura_id');

        // Buscar o documento principal
        $doc = Docs::where('InvoiceId', $invoiceId)->first();

        if (!$doc) {
            return response()->json([
                'success' => false,
                'message' => 'Documento não encontrado com o ID ' . $invoiceId
            ], 404);
        }

        // Buscar as linhas do documento
        $docLinhas = DocLinha::where('InvoiceId', $doc->InvoiceId)
        ->where(function ($query)  {
            $query->where('CreditAmount', '>', 0)
                ->orWhere('DebitAmount', '>', 0);
        })->get();

        if ($docLinhas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma linha encontrada para o documento ' . $invoiceId
            ], 404);
        }

        // Gerar o SQL para inserção no Access
        $sqlStatements = $this->generateAccessSql($doc, $docLinhas, $accessFacturaId);
        
        // Gerar o SQL para inserção da fatura no Access
        $facturaSql = $this->generateFacturaAccessSql($doc, $accessFacturaId);

        return response()->json([
            'success' => true,
            'doc' => $doc,
            'doc_linhas' => $docLinhas,
            'sql_statements' => $sqlStatements,
            'factura_sql' => $facturaSql,
            'access_factura_id' => $accessFacturaId
        ]);
    }

    /**
     * Gera declarações SQL para inserção da fatura no Access
     *
     * @param  \App\Models\Docs  $doc
     * @param  int|null  $accessFacturaId  ID da fatura no Access (opcional)
     * @return string
     */
    public function generateFacturaAccessSql($doc, $accessFacturaId = null)
    {
        // Mapeamento de campos do Laravel para o Access (tabela FACTURAS)
        $facturaFields = [
            'ID' => null, // AUTOINCREMENT
            'Numero' => 'InvoiceNo',
            'FORNECEDOR_ID' => null,
            'CLIENTE_ID' => 'CustomerID',
            'idFuncionario' => null,
            'CLIENTE_ALIAS' => null,
            'TIPO_ID' => 'InvoiceType',
            'DATA' => 'InvoiceDate',
            'SUB_UNIDADE_ID' => null,
            'MOEDA_CODIGO' => 'CurrencyCode',
            'MOEDA_CODIGO_CV' => null,
            'CONTRA_VALOR' => null,
            'DIAS_PAGAMENTO' => null,
            'LIMITE_DATA' => null,
            'LIMITE_DIAS' => null,
            'NrIMPRESSOES' => null,
            'UTILIZADOR_ID' => 'idUser',
            'PAGAMENTO_ID' => null,
            'NrEncomenda' => null,
            'NrDocumento' => 'InvoiceNo',
            'Anulada' => null,
            'Desconto' => null,
            'Lancada' => null,
            'Nr_REM' => null,
            'bloqueada' => null,
            'TAXA_CONVERSAO' => null,
            'CAMBIO' => null,
            'VALEXT' => null,
            'CAIXA_ID' => null,
            'HORA' => 'SystemEntryDate',
            'TOTAL' => 'GrossTotal',
            'TOTAL_PAGO' => null,
            'ValExtPago' => null,
            'C_Nome' => null,
            'M_Morada' => 'AddressDetail',
            'M_Bairro' => null,
            'M_CP' => null,
            'M_Localidade_ID' => null,
            'M_Provincia_ID' => null,
            'M_Pais_ID' => null,
            'M_Telefone' => null,
            'M_Fax' => null,
            'M_Email' => null,
            'C_NIF' => null,
            'OBS' => 'DocObs',
            'ISeloProcessado' => null,
            'DocOrigem' => null,
            'Encargos' => null,
            'EncargosProcessados' => null,
            'EncargosTotal' => null,
            'ServicoID' => null,
            'ServicoDescricao' => null,
            'ServicoMargem' => null,
            'ServicoTotal' => null,
            'ServicoDocID' => null,
            'ServicoItemID' => null,
            'EntidadeComissao' => null,
            'PercentagemComissao' => null,
            'IdDocComissao' => null,
            'TOTALComissao' => null,
            'ComissaoProcessada' => null,
            'DocExterno' => null,
            'EncargosDocID' => null,
            'EncargosCoeficiente' => null,
            'ValorSujeitoRetencao' => null,
            'ValorSujeitoEncargos' => null,
            'Moeda_Movimento' => null,
            'PostoNome' => null,
            'NrLinhas' => null,
            'TotalPCM' => null,
            'TotalPCU' => null,
            'TipoFinanceiro' => null,
            'MovimentoStk' => null,
            'ActualizaPCM' => null,
            'ActualizaPCU' => null,
            'DetalhePagPOS' => null,
            'NrCONTRACTO' => null,
            'TOTALalt' => null,
            'TOTAL_PAGOalt' => null,
            'TOTAL_DESCONTOS' => null,
            'VALExtMda2' => null,
            'DATAInicio' => null,
            'DATAFim' => null,
            'MesaID' => null,
            'TOTALArtigosPesoBruto' => 'grossWeight',
            'TOTALArtigosVolume' => 'grossVolume',
            'TOTALInterno' => null,
            'TOTALInterno_Pago' => null,
            'UltimaParcela' => null,
            'NrParcelas' => null,
            'EstadoActual' => 'doc_status',
            'UltimaUtilizacao' => null,
            'DocDestino' => null,
            'DATA_CRIACAO' => 'created_at',
            'DATA_GRAVACAO' => 'updated_at',
            'DATA_PRIMEIRA_IMPRESSAO' => null,
            'DocNovo' => null,
            'Fechado' => null,
            'EncagosDocID' => null,
            'DocVendedorID' => 'idVendedor',
            'RECIBO_ID' => null,
            'TOTAL_DESCONTOSArtigos' => null,
            'ReciboModoID' => null,
            'ReciboContaID' => null,
            'ReciboBancoID' => null,
            'idVeiculo' => 'idVeiculo',
            'idReserva' => null,
            'idProjecto' => 'idProjecto',
            'TOTALIPC' => null,
            'IDOriginal' => null,
            'IDRegistoExportacao' => null,
            'idProcessamento' => null,
            'total_IVA' => 'TaxPayable',
            'HASH' => 'HASH',
            'PostoUser' => null,
            'IVA_cat_perc' => null,
            'IVA_cat_valor' => null,
            'IVA_cat_modoID' => null,
            'TotalPontos' => 'PontosTotal',
            'CAMBIO_ALT' => null
        ];

        // Campos a serem incluídos no SQL
        $accessFields = [
            'Numero', 'FORNECEDOR_ID', 'CLIENTE_ID', 'idFuncionario', 'CLIENTE_ALIAS',
            'TIPO_ID', 'DATA', 'SUB_UNIDADE_ID', 'MOEDA_CODIGO', 'MOEDA_CODIGO_CV',
            'CONTRA_VALOR', 'DIAS_PAGAMENTO', 'LIMITE_DATA', 'LIMITE_DIAS', 'NrIMPRESSOES',
            'UTILIZADOR_ID', 'PAGAMENTO_ID', 'NrEncomenda', 'NrDocumento', 'Anulada',
            'Desconto', 'Lancada', 'Nr_REM', 'bloqueada', 'TAXA_CONVERSAO', 'CAMBIO',
            'VALEXT', 'CAIXA_ID', 'HORA', 'TOTAL', 'TOTAL_PAGO', 'ValExtPago', 'C_Nome',
            'M_Morada', 'M_Bairro', 'M_CP', 'M_Localidade_ID', 'M_Provincia_ID', 'M_Pais_ID',
            'M_Telefone', 'M_Fax', 'M_Email', 'C_NIF', 'OBS', 'ISeloProcessado', 'DocOrigem',
            'Encargos', 'EncargosProcessados', 'EncargosTotal', 'ServicoID', 'ServicoDescricao',
            'ServicoMargem', 'ServicoTotal', 'ServicoDocID', 'ServicoItemID', 'EntidadeComissao',
            'PercentagemComissao', 'IdDocComissao', 'TOTALComissao', 'ComissaoProcessada',
            'DocExterno', 'EncargosDocID', 'EncargosCoeficiente', 'ValorSujeitoRetencao',
            'ValorSujeitoEncargos', 'Moeda_Movimento', 'PostoNome', 'NrLinhas', 'TotalPCM',
            'TotalPCU', 'TipoFinanceiro', 'MovimentoStk', 'ActualizaPCM', 'ActualizaPCU',
            'DetalhePagPOS', 'NrCONTRACTO', 'TOTALalt', 'TOTAL_PAGOalt', 'TOTAL_DESCONTOS',
            'VALExtMda2', 'DATAInicio', 'DATAFim', 'MesaID', 'TOTALArtigosPesoBruto',
            'TOTALArtigosVolume', 'TOTALInterno', 'TOTALInterno_Pago', 'UltimaParcela',
            'NrParcelas', 'EstadoActual', 'UltimaUtilizacao', 'DocDestino', 'DATA_CRIACAO',
            'DATA_GRAVACAO', 'DATA_PRIMEIRA_IMPRESSAO', 'DocNovo', 'Fechado', 'EncagosDocID',
            'DocVendedorID', 'RECIBO_ID', 'TOTAL_DESCONTOSArtigos', 'ReciboModoID',
            'ReciboContaID', 'ReciboBancoID', 'idVeiculo', 'idReserva', 'idProjecto',
            'TOTALIPC', 'IDOriginal', 'IDRegistoExportacao', 'idProcessamento', 'total_IVA',
            'HASH', 'PostoUser', 'IVA_cat_perc', 'IVA_cat_valor', 'IVA_cat_modoID',
            'TotalPontos', 'CAMBIO_ALT'
        ];

        // Iniciar a construção do SQL
        $sql = "INSERT INTO [FACTURAS] (";
        $sql .= "[" . implode("], [", $accessFields) . "]) ";
        $sql .= "VALUES (";

        // Valores para cada campo
        $values = [];

        foreach ($accessFields as $accessField) {
            $laravelField = $facturaFields[$accessField];

            if ($accessField === 'Numero') {
                // Usar o ID fornecido ou extrair número do InvoiceNo
                if (!empty($accessFacturaId)) {
                    $value = (int)$accessFacturaId;
                } else {
                    $numericPart = preg_replace('/[^0-9]/', '', $doc->InvoiceNo ?? '');
                    $value = !empty($numericPart) ? (int)$numericPart : ($doc->id ?? 0);
                }
                $values[] = $value;
            } else if ($accessField === 'CLIENTE_ID') {
                // Extrair ID numérico do CustomerID
                $customerId = $doc->CustomerID ?? '';
                $numericPart = preg_replace('/[^0-9]/', '', $customerId);
                $value = !empty($numericPart) ? (int)$numericPart : 0;
                $values[] = $value;
            } else if ($accessField === 'TIPO_ID') {
                // Mapear tipo de fatura para ID numérico
                $invoiceType = $doc->InvoiceType ?? '';
                $tipoMap = [
                    'FT' => 1, 'FR' => 2, 'GF' => 3, 'FG' => 4, 'AC' => 5,
                    'AR' => 6, 'ND' => 7, 'NC' => 8, 'AF' => 9, 'TV' => 10,
                    'RP' => 11, 'RE' => 12, 'CS' => 13, 'LD' => 14, 'RA' => 15
                ];
                $value = $tipoMap[$invoiceType] ?? 1;
                $values[] = $value;
            } else if ($accessField === 'DATA') {
                // Data da fatura
                $data = $doc->InvoiceDate ?? now();
                $dataFormatada = $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($data));
                $values[] = "#" . $dataFormatada . "#";
            } else if ($accessField === 'HORA') {
                // Hora do sistema
                $hora = $doc->SystemEntryDate ?? now();
                $horaFormatada = $hora instanceof \DateTime ? $hora->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($hora));
                $values[] = "#" . $horaFormatada . "#";
            } else if ($accessField === 'TOTAL') {
                // Total da fatura
                $total = (float)($doc->GrossTotal ?? 0);
                $formattedTotal = str_replace('.', ',', number_format($total, 2, ',', ''));
                $values[] = "'" . $formattedTotal . "'";
            } else if ($accessField === 'total_IVA') {
                // Total do IVA
                $totalIva = (float)($doc->TaxPayable ?? 0);
                $formattedIva = str_replace('.', ',', number_format($totalIva, 2, ',', ''));
                $values[] = "'" . $formattedIva . "'";
            } else if ($accessField === 'DATA_CRIACAO') {
                // Data de criação
                $dataCriacao = $doc->created_at ?? now();
                $dataFormatada = $dataCriacao instanceof \DateTime ? $dataCriacao->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($dataCriacao));
                $values[] = "#" . $dataFormatada . "#";
            } else if ($accessField === 'DATA_GRAVACAO') {
                // Data de gravação
                $dataGravacao = $doc->updated_at ?? now();
                $dataFormatada = $dataGravacao instanceof \DateTime ? $dataGravacao->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($dataGravacao));
                $values[] = "#" . $dataFormatada . "#";
            } else if ($accessField === 'UltimaUtilizacao') {
                // Última utilização (data atual)
                $values[] = "#" . date('Y-m-d H:i:s') . "#";
            } else if (in_array($accessField, ['FORNECEDOR_ID', 'SUB_UNIDADE_ID', 'DIAS_PAGAMENTO', 'NrIMPRESSOES', 'UTILIZADOR_ID', 'PAGAMENTO_ID', 'CAIXA_ID', 'DocOrigem', 'ServicoID', 'ServicoDocID', 'ServicoItemID', 'IdDocComissao', 'EncargosDocID', 'M_Localidade_ID', 'M_Provincia_ID', 'M_Pais_ID', 'NrLinhas', 'TipoFinanceiro', 'MovimentoStk', 'MesaID', 'UltimaParcela', 'NrParcelas', 'DocDestino', 'DocVendedorID', 'RECIBO_ID', 'ReciboModoID', 'ReciboContaID', 'ReciboBancoID', 'idReserva', 'IDOriginal', 'IDRegistoExportacao', 'idProcessamento', 'IVA_cat_modoID', 'TotalPontos'])) {
                // Campos numéricos inteiros com valor padrão 0
                if ($laravelField && isset($doc->{$laravelField})) {
                    $value = (int)$doc->{$laravelField};
                } else {
                    $value = 0;
                }
                $values[] = $value;
            } else if (in_array($accessField, ['Desconto', 'TAXA_CONVERSAO', 'CAMBIO', 'EncargosTotal', 'ServicoMargem', 'ServicoTotal', 'EntidadeComissao', 'PercentagemComissao', 'TOTALComissao', 'EncargosCoeficiente', 'ValorSujeitoRetencao', 'ValorSujeitoEncargos', 'TotalPCM', 'TotalPCU', 'TOTALalt', 'TOTAL_PAGOalt', 'TOTAL_DESCONTOS', 'TOTALArtigosPesoBruto', 'TOTALArtigosVolume', 'TOTALInterno', 'TOTALInterno_Pago', 'TOTAL_DESCONTOSArtigos', 'TOTALIPC', 'IVA_cat_perc', 'IVA_cat_valor', 'CAMBIO_ALT'])) {
                // Campos numéricos decimais
                if ($laravelField && isset($doc->{$laravelField})) {
                    $value = (float)$doc->{$laravelField};
                } else {
                    $value = 0.00;
                }
                $formattedValue = str_replace('.', ',', number_format($value, 2, ',', ''));
                $values[] = "'" . $formattedValue . "'";
            } else if (in_array($accessField, ['CONTRA_VALOR', 'Anulada', 'Lancada', 'bloqueada', 'ISeloProcessado', 'Encargos', 'EncargosProcessados', 'ComissaoProcessada', 'ActualizaPCM', 'ActualizaPCU', 'DocNovo', 'Fechado'])) {
                // Campos booleanos
                if ($laravelField && isset($doc->{$laravelField})) {
                    $value = $doc->{$laravelField} ? -1 : 0; // Access usa -1 para True, 0 para False
                } else {
                    $value = 0;
                }
                $values[] = $value;
            } else if (in_array($accessField, ['LIMITE_DATA', 'LIMITE_DIAS', 'DATAInicio', 'DATAFim', 'DATA_PRIMEIRA_IMPRESSAO'])) {
                // Campos de data opcionais
                if ($laravelField && isset($doc->{$laravelField}) && !empty($doc->{$laravelField})) {
                    $data = $doc->{$laravelField};
                    $dataFormatada = $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($data));
                    $values[] = "#" . $dataFormatada . "#";
                } else {
                    $values[] = "null";
                }
            } else {
                // Campos de texto
                if ($laravelField && isset($doc->{$laravelField})) {
                    $value = $doc->{$laravelField};
                } else {
                    $value = '';
                }
                $values[] = "'" . addslashes($value) . "'";
            }
        }

        // Concatenar os valores ao SQL
        $sql .= implode(", ", $values);
        $sql .= ");";

        return $sql;
    }

    /**
     * Gera declarações SQL para inserção no Access
     *
     * Esta função é especialmente útil para recuperar itens de faturas que foram perdidos
     * na tabela facturas_itens do Access, mas ainda existem na base de dados Laravel.
     *
     * @param  \App\Models\Docs  $doc
     * @param  \Illuminate\Database\Eloquent\Collection  $docLinhas
     * @param  int|null  $accessFacturaId  ID da fatura no Access (opcional)
     * @return array
     */
    public function generateAccessSql($doc, $docLinhas, $accessFacturaId = null)
    {
        $sqlStatements = [];

        // Definir o mapeamento de campos do Laravel para o Access
        // Isso facilita ajustes futuros se a estrutura da tabela Access mudar
        $fieldMapping = [
            // Campos obrigatórios e principais
            'FACTURA_ID' => 'Numero',         // Número da fatura no Access (campo chave estrangeira)
            'docTipoID' => null,             // Tipo de documento
            'docSerie' => null,              // Série do documento
            'P_CODIGO' => 'ProductCode',      // Código do produto
            'DESCRICAO' => 'Description',     // Descrição do produto
            'QUANT' => 'Quantity',           // Quantidade
            'UN' => 'UnitOfMeasure',         // Unidade de medida
            'PVP' => 'UnitPrice',            // Preço unitário
            'DESCONTO' => 'SettlementAmount', // Valor do desconto
            'IVA' => 'TaxPercentage',        // Percentagem de IVA
            'IVA_Valor' => null,             // Valor do IVA (calculado)
            'TOTAL_ITEM' => null,            // Total do item (calculado)
            'TOTAL_ITEM_DESCONTO' => null,   // Total do item com desconto (calculado)
            'ArmazemID' => 'idArmazem',      // ID do armazém

            // Campos adicionais
            'DEB_CRE' => null,               // Débito/Crédito
            'MovStock' => null,              // Movimentação de stock
            'OBS' => null,                   // Observações
            'DataMov' => null,               // Data do movimento
            'DataInsercao' => null,          // Data de inserção
            'TipoMovStk' => null,            // Tipo de movimento de stock
            'Vendedor_ID' => null,           // ID do vendedor
            'linhaRemovida' => null,         // Linha removida
            'LinhaNr' => null,               // Número da linha

            // Campos adicionais do exemplo UPDATE
            'QuantCaixa' => null,            // Quantidade por caixa
            'NrLote' => null,                // Número de lote
            'FOB' => null,                   // FOB
            'CIF' => null,                   // CIF
            'PUN' => null,                   // Preço unitário
            'LANCADO' => null,               // Lançado
            'COR' => null,                   // Cor
            'ServicosRetencao' => null,      // Serviços retenção
            'ServicosRetencaoPercent' => null, // Percentagem de retenção de serviços
            'StockActual' => null,           // Stock atual
            'PCM0' => null,                  // PCM0
            'PCU0' => null,                  // PCU0
            'PVP0' => null,                  // PVP0
            'QuantFormula' => null,          // Quantidade fórmula
            'UnidadeBaseID' => null,         // ID da unidade base
            'UnidadeMovimentoID' => null,    // ID da unidade de movimento
            'ArtigoSujeitoEncargos' => null, // Artigo sujeito a encargos
            'DataUltimaModificacao' => null, // Data da última modificação
            'Veiculo_ID' => null,            // ID do veículo
            'ArtigoPesoBruto' => null,       // Peso bruto do artigo
            'ArtigoVolume' => null,          // Volume do artigo
            'ArtigoSN' => null,              // Número de série do artigo
            'CaixaID' => null,               // ID da caixa
            'FactorConvOriginal' => null,    // Fator de conversão original
            'TipoUnMedida' => null,          // Tipo de unidade de medida
            'StockActualizado' => null,      // Stock atualizado
            'StkIni' => null,                // Stock inicial
            'ArtigoPaiID' => null,           // ID do artigo pai
            'Comprimento' => null,           // Comprimento
            'Largura' => null,               // Largura
            'TAXA_CONV' => null,             // Taxa de conversão
            'IPC' => null,                   // IPC
            'IVA_ID' => null,                // ID do IVA
            'DataEntrega' => null,           // Data de entrega
            'EncargosDocID' => null,         // ID de encargos do documento
            'ArtigoPontos' => null,          // Pontos do artigo
            'ImprimirEmDoc' => null,         // Imprimir em documento
            'Veiculo_Obs' => null,           // Observações do veículo
            'linhaGUID' => null              // GUID da linha
        ];

        foreach ($docLinhas as $linha) {
            // Campos a serem incluídos no SQL
            $accessFields = [
                // Campos obrigatórios e principais
                'FACTURA_ID',
                'docTipoID',
                'docSerie',
                'P_CODIGO',
                'DESCRICAO',
                'QUANT',
                'QuantCaixa',
                'NrLote',
                'DESCONTO',
                'FOB',
                'CIF',
                'PVP',
                'PUN',
                'LANCADO',
                'COR',
                'UN',
                'ServicosRetencao',
                'ServicosRetencaoPercent',
                'StockActual',
                'PCM0',
                'PCU0',
                'PVP0',
                'QuantFormula',
                'UnidadeBaseID',
                'UnidadeMovimentoID',
                'ArtigoSujeitoEncargos',
                'DataUltimaModificacao',
                'Vendedor_ID',
                'Veiculo_ID',
                'ArtigoPesoBruto',
                'ArtigoVolume',
                'MovStock',
                'ArtigoSN',
                'CaixaID',
                'FactorConvOriginal',
                'TipoUnMedida',
                'StockActualizado',
                'StkIni',
                'ArtigoPaiID',
                'Comprimento',
                'Largura',
                'TAXA_CONV',
                'IPC',
                'IVA_ID',
                'IVA',
                'IVA_Valor',
                'ArmazemID',
                'DataEntrega',
                'EncargosDocID',
                'ArtigoPontos',
                'ImprimirEmDoc',
                'Veiculo_Obs',
                'OBS',
                'linhaGUID',
                'TOTAL_ITEM',
                'TOTAL_ITEM_DESCONTO',
                'DEB_CRE',
                'DataMov',
                'DataInsercao',
                'TipoMovStk',
                'linhaRemovida',
                'LinhaNr'
            ];

            // Iniciar a construção do SQL
            $sql = "INSERT INTO [Facturas_itens] (";
            $sql .= "[" . implode("], [", $accessFields) . "]) ";
            $sql .= "VALUES (";

            // Valores para cada campo
            $values = [];

            foreach ($accessFields as $accessField) {
                // Campos calculados e especiais
                if ($accessField === 'TOTAL_ITEM') {
                    // Total (CreditAmount ou DebitAmount, dependendo de qual não é zero)
                    $total = ($linha->CreditAmount > 0) ? (float)$linha->CreditAmount : (float)$linha->DebitAmount;
                    // Garantir que não seja nulo ou vazio e formatar com vírgula para o Access
                    $formattedTotal = str_replace('.', ',', number_format(($total > 0) ? $total : 0, 2, ',', ''));
                    $values[] = "'" . $formattedTotal . "'";

                } else if ($accessField === 'TOTAL_ITEM_DESCONTO') {
                    // Total com desconto
                    $total = ($linha->CreditAmount > 0) ? (float)$linha->CreditAmount : (float)$linha->DebitAmount;
                    $desconto = ($linha->SettlementAmount > 0) ? (float)$linha->SettlementAmount : 0;
                    $totalComDesconto = $total - $desconto;
                    // Formatar com vírgula para o Access
                    $formattedTotal = str_replace('.', ',', number_format(($totalComDesconto > 0) ? $totalComDesconto : 0, 2, ',', ''));
                    $values[] = "'" . $formattedTotal . "'";

                } else if ($accessField === 'IVA_Valor') {
                    // Valor do IVA
                    $total = ($linha->CreditAmount > 0) ? (float)$linha->CreditAmount : (float)$linha->DebitAmount;
                    $taxaIva = (float)$linha->TaxPercentage;
                    $valorIva = $total * ($taxaIva / 100);
                    // Formatar com vírgula para o Access
                    $formattedIva = str_replace('.', ',', number_format($valorIva, 4, ',', ''));
                    $values[] = "'" . $formattedIva . "'";

                } else if ($accessField === 'DESCONTO') {
                    // Desconto (usar SettlementAmount ou 0 se não houver)
                    $desconto = ($linha->SettlementAmount > 0) ? (float)$linha->SettlementAmount : 0;
                    // Formatar com vírgula para o Access
                    $formattedDesconto = str_replace('.', ',', number_format($desconto, 2, ',', ''));
                    $values[] = "'" . $formattedDesconto . "'";

                } else if ($accessField === 'ArmazemID') {
                    // Armazém (0 se não houver - não usar NULL para campos numéricos no Access)
                    $armazemId = !empty($linha->idArmazem) ? (int)$linha->idArmazem : 0;
                    $values[] = $armazemId;
                } else if ($accessField === 'docTipoID') {
                    // Tipo de documento (usar o tipo do documento principal ou 0)
                    $tipoId = $doc->TIPO_ID ?? 0;
                    $values[] = (int)$tipoId;
                } else if ($accessField === 'docSerie') {
                    // Série do documento (extrair da InvoiceNo ou usar string vazia)
                    $serie = '';
                    if (!empty($doc->InvoiceTypeSerie)) {
                        $serie = $doc->InvoiceTypeSerie;
                    } else if (!empty($doc->InvoiceNo)) {
                        // Tentar extrair a série do InvoiceNo (geralmente é a parte alfabética)
                        preg_match('/^([A-Za-z]+)/', $doc->InvoiceNo, $matches);
                        if (!empty($matches[1])) {
                            $serie = $matches[1];
                        }
                    }
                    $values[] = "'" . addslashes($serie) . "'";
                } else if ($accessField === 'DEB_CRE') {
                    // Débito/Crédito (0 para débito, -1 para crédito)
                    // Assumindo que CreditAmount > 0 significa crédito
                    $debCre = ($linha->CreditAmount > 0) ? -1 : 0;
                    $values[] = $debCre;
                } else if ($accessField === 'MovStock') {
                    // Movimentação de stock (assumindo 0 como padrão)
                    $values[] = 0;
                } else if ($accessField === 'OBS') {
                    // Observações (usar campo Description ou string vazia)
                    $obs = !empty($linha->Description) ? $linha->Description : '';
                    $values[] = "'" . addslashes($obs) . "'";
                } else if ($accessField === 'DataMov') {
                    // Data do movimento (usar TaxPointDate ou data atual)
                    $dataMov = !empty($linha->TaxPointDate) ? $linha->TaxPointDate : $doc->InvoiceDate ?? new \DateTime();
                    $dataFormatada = $dataMov instanceof \DateTime ? $dataMov->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
                    $values[] = "#" . $dataFormatada . "#";
                } else if ($accessField === 'DataInsercao') {
                    // Data de inserção (usar data atual)
                    $values[] = "#" . date('Y-m-d H:i:s') . "#";
                } else if ($accessField === 'TipoMovStk') {
                    // Tipo de movimento de stock (assumindo 0 como padrão)
                    $values[] = 0;
                } else if ($accessField === 'Vendedor_ID') {
                    // ID do vendedor (usar idVendedor do documento ou 0)
                    $vendedorId = $doc->idVendedor ?? 0;
                    $values[] = (int)$vendedorId;
                } else if ($accessField === 'linhaRemovida') {
                    // Linha removida (0 = não removida)
                    $values[] = 0;
                } else if ($accessField === 'LinhaNr') {
                    // Número da linha (usar LineNumber ou índice + 1)
                        // Se não tiver LineNumber, usar o índice da linha no array + 1
                        static $lineIndex = 0;
                        $linhaNr = ++$lineIndex;
                    $values[] = $linhaNr;
                } else if ($accessField === 'QuantCaixa') {
                    // Quantidade por caixa (padrão: 1)
                    $values[] = "'1,00'";

                } else if ($accessField === 'NrLote') {
                    // Número de lote (padrão: string vazia)
                    $values[] = "''";
                } else if ($accessField === 'FOB') {
                    // FOB (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'CIF') {
                    // CIF (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'IVA') {
                    // Taxa de IVA (usar TaxPercentage ou 0 se não houver)
                    $taxaIva = !empty($linha->TaxPercentage) ? (float)$linha->TaxPercentage : 0;
                    // Formatar com vírgula para o Access
                    $formattedIva = str_replace('.', ',', number_format($taxaIva, 2, ',', ''));
                    $values[] = "'" . $formattedIva . "'";

                } else if ($accessField === 'PUN') {
                    // PUN - Preço unitário (usar UnitPrice se disponível)
                    $pun = !empty($linha->UnitPrice) ? (float)$linha->UnitPrice : 0;
                    // Formatar com vírgula para o Access
                    $formattedPun = str_replace('.', ',', number_format($pun, 2, ',', ''));
                    $values[] = "'" . $formattedPun . "'";

                } else if ($accessField === 'LANCADO') {
                    // LANCADO (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'COR') {
                    // COR (padrão: string vazia)
                    $values[] = "''";
                } else if ($accessField === 'ServicosRetencao') {
                    // ServicosRetencao (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'ServicosRetencaoPercent') {
                    // ServicosRetencaoPercent (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'StockActual') {
                    // StockActual (padrão: mesma quantidade do item)
                    $stockActual = !empty($linha->Quantity) ? (float)$linha->Quantity : 1;
                    // Formatar com vírgula para o Access
                    $formattedStock = str_replace('.', ',', number_format($stockActual, 2, ',', ''));
                    $values[] = "'" . $formattedStock . "'";

                } else if ($accessField === 'QUANT') {
                    // Quantidade (usar Quantity ou 1 se não houver)
                    $quantidade = !empty($linha->Quantity) ? (float)$linha->Quantity : 1;
                    // Formatar com vírgula para o Access
                    $formattedQuant = str_replace('.', ',', number_format($quantidade, 2, ',', ''));
                    $values[] = "'" . $formattedQuant . "'";

                } else if ($accessField === 'PCM0') {
                    // PCM0 (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'PCU0') {
                    // PCU0 (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'PVP0') {
                    // PVP0 (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'QuantFormula') {
                    // QuantFormula (padrão: 1)
                    $values[] = "'1,00'";

                } else if ($accessField === 'UnidadeBaseID') {
                    // UnidadeBaseID (padrão: 1)
                    $values[] = 1;
                } else if ($accessField === 'UnidadeMovimentoID') {
                    // UnidadeMovimentoID (padrão: 1)
                    $values[] = 1;
                } else if ($accessField === 'ArtigoSujeitoEncargos') {
                    // ArtigoSujeitoEncargos (padrão: 1)
                    $values[] = 1;
                } else if ($accessField === 'DataUltimaModificacao') {
                    // DataUltimaModificacao (usar data atual)
                    $values[] = "#" . date('Y-m-d H:i:s') . "#";
                } else if ($accessField === 'Veiculo_ID') {
                    // Veiculo_ID (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'ArtigoPesoBruto') {
                    // ArtigoPesoBruto (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'ArtigoVolume') {
                    // ArtigoVolume (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'ArtigoSN') {
                    // ArtigoSN (padrão: string vazia)
                    $values[] = "''";
                } else if ($accessField === 'CaixaID') {
                    // CaixaID (padrão: 526 como no exemplo)
                    $values[] = 526;
                } else if ($accessField === 'FactorConvOriginal') {
                    // FactorConvOriginal (padrão: 1)
                    $values[] = "'1,00'";

                } else if ($accessField === 'TipoUnMedida') {
                    // TipoUnMedida (padrão: 1)
                    $values[] = 1;
                } else if ($accessField === 'StockActualizado') {
                    // StockActualizado (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'StkIni') {
                    // StkIni (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'ArtigoPaiID') {
                    // ArtigoPaiID (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'Comprimento') {
                    // Comprimento (padrão: 1)
                    $values[] = "'1,00'";

                } else if ($accessField === 'Largura') {
                    // Largura (padrão: 1)
                    $values[] = "'1,00'";

                } else if ($accessField === 'TAXA_CONV') {
                    // TAXA_CONV (padrão: 1)
                    $values[] = "'1,00'";

                } else if ($accessField === 'IPC') {
                    // IPC (padrão: 0)
                    $values[] = "'0,00'";
                } else if ($accessField === 'IVA_ID') {
                    // IVA_ID (padrão: 1)
                    $values[] = 1;
                } else if ($accessField === 'DataEntrega') {
                    // DataEntrega (padrão: null)
                    $values[] = "null";

                } else if ($accessField === 'EncargosDocID') {
                    // EncargosDocID (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'ArtigoPontos') {
                    // ArtigoPontos (padrão: 0)
                    $values[] = 0;
                } else if ($accessField === 'ImprimirEmDoc') {
                    // ImprimirEmDoc (padrão: 1)
                    $values[] = 1;
                } else if ($accessField === 'Veiculo_Obs') {
                    // Veiculo_Obs (padrão: string vazia)
                    $values[] = "''";
                } else if ($accessField === 'linhaGUID') {
                    // linhaGUID (gerar um GUID único ou usar um valor padrão)
                    $guid = !empty($linha->LineGUID) ? $linha->LineGUID : strtoupper(md5(uniqid(rand(), true)));
                    $values[] = "'" . $guid . "'";
                } else {
                    // Campos normais
                    $laravelField = $fieldMapping[$accessField];

                    // Verificar se o valor deve ser tratado como string ou número
                    $isNumeric = in_array($accessField, ['QUANT', 'PVP', 'IVA', 'TOTAL_ITEM', 'DESCONTO', 'FACTURA_ID', 'ArmazemID', 'PUN', 'FOB', 'CIF', 'PCM0', 'PCU0', 'PVP0', 'QuantCaixa', 'QuantFormula', 'StockActual', 'ArtigoPesoBruto', 'ArtigoVolume', 'TAXA_CONV', 'IPC']);

                    if ($laravelField === 'Numero') {
                        // ATENÇÃO: Este é o campo mais importante para a recuperação dos itens perdidos!
                        // O FACTURA_ID deve corresponder exatamente ao ID da fatura existente no Access
                        // caso contrário, os itens não serão vinculados à fatura correta.

                        // Se o ID da fatura no Access foi fornecido, usá-lo com prioridade
                        if (!empty($accessFacturaId)) {
                            $value = (int)$accessFacturaId;
                        } else {
                            // Caso especial: Numero vem do documento principal, não da linha
                            // Garantir que seja um número inteiro válido
                            $value = $doc->Numero ?? 0;

                            // Se não tiver Numero, tentar usar o ID ou InvoiceNo como número
                            if (empty($value) || $value === '0') {
                                // Tentar extrair um número do InvoiceNo
                                if (!empty($doc->InvoiceNo)) {
                                    // Remover qualquer parte não numérica
                                    $numericPart = preg_replace('/[^0-9]/', '', $doc->InvoiceNo);
                                    if (!empty($numericPart)) {
                                        $value = (int)$numericPart;
                                    }
                                }

                                // Se ainda estiver vazio, usar o ID como último recurso
                                if (empty($value) || $value === '0') {
                                    $value = $doc->id ?? 0;
                                }
                            }
                        }
                    } else {
                        // Valor normal da linha
                        $value = $linha->{$laravelField};
                    }

                    if ($isNumeric) {
                        // Garantir que valores numéricos sejam tratados corretamente
                        if ($value === null || $value === '') {
                            // Para campos numéricos vazios, usar 0
                            $values[] = 0;
                        } else {
                            // Converter para o tipo numérico apropriado
                            if (in_array($accessField, ['QUANT', 'PVP', 'IVA', 'TOTAL_ITEM', 'DESCONTO', 'PUN', 'FOB', 'CIF', 'PCM0', 'PCU0', 'PVP0', 'IVA_Valor', 'ArtigoPesoBruto', 'ArtigoVolume'])) {
                                // Valores de ponto flutuante - formatar com vírgula para o Access
                                $formattedValue = str_replace('.', ',', number_format((float)$value, 2, ',', ''));
                                $values[] = "'" . $formattedValue . "'";
                            } else {
                                // Valores inteiros
                                $values[] = (int)$value;
                            }
                        }
                    } else {
                        // Tratar campos de texto
                        if ($value === null) {
                            $values[] = "''";
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                }
            }

            // Concatenar os valores ao SQL
            $sql .= implode(", ", $values);
            $sql .= ");";

            $sqlStatements[] = $sql;
        }

        return $sqlStatements;
    }

    /**
     * Exibe a página para visualizar as linhas de documentos e o SQL gerado
     *
     * @param  string  $invoiceId
     * @return \Illuminate\Http\Response
     */
    public function show($invoiceId)
    {
        // Buscar o documento principal
        $doc = Docs::where('InvoiceId', $invoiceId)->first();

        if (!$doc) {
            return redirect()->route('doc-linha-access.index')
                ->with('error', 'Documento não encontrado com o ID ' . $invoiceId);
        }

        // Buscar as linhas do documento
        $docLinhas = DocLinha::where('InvoiceId', $doc->InvoiceId)->get();

        if ($docLinhas->isEmpty()) {
            return redirect()->route('doc-linha-access.index')
                ->with('error', 'Nenhuma linha encontrada para o documento ' . $invoiceId);
        }

        // Verificar se há um ID de fatura do Access na sessão
        $accessFacturaId = session('access_factura_id');

        // Gerar o SQL para inserção no Access
        $sqlStatements = $this->generateAccessSql($doc, $docLinhas, $accessFacturaId);
        
        // Gerar o SQL para inserção da fatura no Access
        $facturaSql = $this->generateFacturaAccessSql($doc, $accessFacturaId);

        return view('doc-linha-access.show', compact('doc', 'docLinhas', 'sqlStatements', 'facturaSql', 'accessFacturaId'));
    }
}
