<?php

namespace App\Http\Controllers;

use App\Models\DocLinha;
use App\Models\Docs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocAccessController extends Controller
{
    /**
     * Exibe a página para buscar documentos por empresa e intervalo de datas
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Buscar todas as empresas para o dropdown
        $empresas = DB::table('doc_empresas')->select('CompanyID', 'CompanyName')->orderBy('CompanyName', 'asc')->get();

        return view('doc-access.index', compact('empresas'));
    }

    /**
     * Busca documentos com base no ID da empresa e intervalo de datas
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $request->validate([
            'company_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $companyId = $request->input('company_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Buscar os documentos da empresa no intervalo de datas
        $docs = Docs::where('CompanyID', $companyId)
            ->whereBetween('InvoiceDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('InvoiceDate', 'desc')
            ->get();

        if ($docs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum documento encontrado para a empresa no período especificado'
            ], 404);
        }

        // Buscar o nome da empresa para exibição
        $empresa = DB::table('doc_empresas')
            ->where('CompanyID', $companyId)
            ->select('CompanyName')
            ->first();

        return response()->json([
            'success' => true,
            'docs' => $docs,
            'empresa' => $empresa,
            'filtros' => [
                'company_id' => $companyId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    /**
     * Exibe a página de resultados da busca de documentos
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function results(Request $request)
    {
        $request->validate([
            'company_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $companyId = $request->input('company_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Buscar os documentos da empresa no intervalo de datas
        $docs = Docs::where('CompanyID', $companyId)
            ->whereBetween('InvoiceDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('InvoiceDate', 'desc')
            ->get();

        // Buscar o nome da empresa para exibição
        $empresa = DB::table('doc_empresas')
            ->where('CompanyID', $companyId)
            ->select('CompanyName')
            ->first();

        return view('doc-access.results', compact('docs', 'empresa', 'companyId', 'startDate', 'endDate'));
    }

    /**
     * Gera SQL em lote para múltiplos documentos
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateBatchSql(Request $request)
    {
        $request->validate([
            'company_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'document_ids' => 'nullable|array',
            'document_ids.*' => 'string'
        ]);

        $companyId = $request->input('company_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $documentIds = $request->input('document_ids', []);

        // Buscar os documentos
        $query = Docs::where('CompanyID', $companyId)
            ->whereBetween('InvoiceDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Se IDs específicos foram fornecidos, filtrar por eles
        if (!empty($documentIds)) {
            $query->whereIn('InvoiceId', $documentIds);
        }

        $docs = $query->orderBy('InvoiceDate', 'desc')->get();

        if ($docs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum documento encontrado para os critérios especificados'
            ], 404);
        }

        // Instanciar o controlador DocLinhaAccessController
        $docLinhaController = new DocLinhaAccessController();
        
        $allFacturasSql = [];
        $allLinhasSql = [];
        $processedCount = 0;
        $skippedCount = 0;
        $accessFacturaId = 1; // ID inicial para as faturas no Access

        foreach ($docs as $doc) {
            try {
                // Buscar as linhas do documento
                $docLinhas = DocLinha::where('InvoiceId', $doc->InvoiceId)
                    ->where(function ($query) {
                        $query->where('CreditAmount', '>', 0)
                            ->orWhere('DebitAmount', '>', 0);
                    })->get();

                if ($docLinhas->isEmpty()) {
                    $skippedCount++;
                    continue;
                }

                // Gerar SQL da fatura com ID específico
                $facturaSql = $docLinhaController->generateFacturaAccessSql($doc, $accessFacturaId);
                $allFacturasSql[] = "-- FATURA: {$doc->InvoiceNo} (ID: {$doc->InvoiceId}) - Access ID: {$accessFacturaId}\n" . $facturaSql;

                // Gerar SQL das linhas usando o mesmo ID da fatura
                $linhasSql = $docLinhaController->generateAccessSql($doc, $docLinhas, $accessFacturaId);
                $allLinhasSql[] = "-- LINHAS DA FATURA: {$doc->InvoiceNo} - Access ID: {$accessFacturaId}\n" . implode("\n", $linhasSql);

                $processedCount++;
                $accessFacturaId++; // Incrementar para a próxima fatura
            } catch (\Exception $e) {
                $skippedCount++;
                continue;
            }
        }

        // Buscar o nome da empresa
        $empresa = DB::table('doc_empresas')
            ->where('CompanyID', $companyId)
            ->select('CompanyName')
            ->first();

        return response()->json([
            'success' => true,
            'facturas_sql' => $allFacturasSql,
            'linhas_sql' => $allLinhasSql,
            'processed_count' => $processedCount,
            'skipped_count' => $skippedCount,
            'total_docs' => $docs->count(),
            'empresa' => $empresa,
            'periodo' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'id_range' => [
                'start_id' => 1,
                'end_id' => $accessFacturaId - 1
            ]
        ]);
    }

    /**
     * Exibe a página de detalhes do documento com suas linhas e SQL
     *
     * @param string $invoiceId
     * @return \Illuminate\Http\Response
     */
    public function showDocument($invoiceId)
    {
        // Substituir @ por / no InvoiceId para buscar corretamente no banco de dados
        $invoiceId = str_replace('@', '/', $invoiceId);

        // Buscar o documento principal
        $doc = Docs::where('InvoiceId', $invoiceId)->first();

        if (!$doc) {
            return redirect()->route('doc-access.index')
                ->with('error', 'Documento não encontrado com o ID ' . $invoiceId);
        }

        // Buscar as linhas do documento
        $docLinhas = DocLinha::where('InvoiceId', $doc->InvoiceId)
        ->where(function ($query)  {
            $query->where('CreditAmount', '>', 0)
                ->orWhere('DebitAmount', '>', 0);
        })->get();
        //dd($docLinhas);

        if ($docLinhas->isEmpty()) {
            return redirect()->route('doc-access.index')
                ->with('error', 'Nenhuma linha encontrada para o documento ' . $invoiceId);
        }

        // Verificar se há um ID de fatura do Access na sessão
        $accessFacturaId = session('access_factura_id');

        // Instanciar o controlador DocLinhaAccessController para usar o método generateAccessSql
        $docLinhaController = new DocLinhaAccessController();

        // Gerar o SQL para inserção no Access usando o método do controlador DocLinhaAccessController
        $sqlStatements = $docLinhaController->generateAccessSql($doc, $docLinhas, $accessFacturaId);
        
        // Gerar o SQL para inserção da fatura no Access
        $facturaSql = $docLinhaController->generateFacturaAccessSql($doc, $accessFacturaId);

        return view('doc-linha-access.show', compact('doc', 'docLinhas', 'sqlStatements', 'facturaSql', 'accessFacturaId'));
    }
}
