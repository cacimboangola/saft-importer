<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\SAFTImporterInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SAFTImportController extends Controller
{
    protected $saftImporter;

    public function __construct(SAFTImporterInterface $saftImporter)
    {
        $this->saftImporter = $saftImporter;
    }

    public function index()
    {
        return view('saft.import');
    }

    public function store(Request $request)
    {
        // Validação mais detalhada
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'saft_file' => 'required|file|mimes:xml', // Sem limite de tamanho
            'import_types' => 'required|array',
            'invoice_types' => 'array'
        ], [
            'saft_file.required' => 'Por favor, selecione um arquivo SAFT para importar.',
            'saft_file.file' => 'O arquivo SAFT deve ser um arquivo válido.',
            'saft_file.mimes' => 'O arquivo SAFT deve ser um arquivo XML.',
            'import_types.required' => 'Por favor, selecione pelo menos um tipo de documento para importar.',
            'import_types.array' => 'Os tipos de documentos devem ser fornecidos como uma lista.',
            'invoice_types.array' => 'Os tipos de faturas devem ser fornecidos como uma lista.'
        ]);
        
        if ($validator->fails()) {
            Log::warning('Validação falhou na importação SAFT', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()->toArray()
            ], 422);
        }
        
        // Validação adicional para tipos de faturas quando 'sales' está selecionado
        if (in_array('sales', $request->input('import_types', [])) && empty($request->input('invoice_types', []))) {
            Log::warning('Validação falhou: tipos de faturas não selecionados', [
                'import_types' => $request->input('import_types', []),
                'invoice_types' => $request->input('invoice_types', [])
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => [
                    'invoice_types' => ['Por favor, selecione pelo menos um tipo de fatura quando a opção "Vendas" estiver selecionada.']
                ]
            ], 422);
        }

        try {
            // Configura o PHP para sempre retornar JSON, mesmo em caso de erro fatal
            ini_set('display_errors', 'Off');
            set_time_limit(600); // 10 minutos timeout
            
            $file = $request->file('saft_file');
            $importTypes = $request->input('import_types', []);
            
            // Registra os dados recebidos para depuração
            Log::info('Iniciando importação SAFT', [
                'file' => $file ? $file->getClientOriginalName() : 'Nenhum arquivo',
                'import_types' => $importTypes,
                'invoice_types' => $request->input('invoice_types', [])
            ]);

            $this->saftImporter->loadFile($file->getRealPath());

            $results = [];
            $total = 0;
            $progress = [];

            foreach ($importTypes as $type) {
                switch ($type) {
                    case 'company':
                        $company = $this->saftImporter->importCompany();
                        $results['company'] = 1; // Sempre conta como 1 empresa
                        $progress['company'] = ['total' => 1, 'current' => 1];
                        break;
                    case 'customers':
                        $customers = $this->saftImporter->importCustomers();
                        $count = is_array($customers) ? count($customers) : 0;
                        $results['customers'] = $count;
                        $progress['customers'] = ['total' => $count, 'current' => $count];
                        break;
                    case 'sales':
                        $invoiceTypes = $request->input('invoice_types', []);
                        
                        Log::info('Tipos de faturas recebidos:', ['tipos' => $invoiceTypes]);
                        
                        if (empty($invoiceTypes)) {
                            // Se nenhum tipo de fatura for selecionado, importar todos
                            $invoiceTypes = [
                                'FT', 'FR', 'GF', 'FG', 'AC', 'AR', 'ND', 'NC', 'AF', 'TV',
                                'RP', 'RE', 'CS', 'LD', 'RA'
                            ];
                            Log::info('Nenhum tipo de fatura selecionado, usando todos os tipos', ['tipos' => $invoiceTypes]);
                        }
                        
                        $count = $this->saftImporter->importSales($invoiceTypes);
                        // Garantir que count seja um número inteiro
                        $count = is_numeric($count) ? (int)$count : 0;
                        $results['sales'] = $count;
                        $progress['sales'] = ['total' => $count, 'current' => $count];
                        $total += $count;
                        break;
                    case 'purchases':
                        $count = $this->saftImporter->importPurchases();
                        // Garantir que count seja um número inteiro
                        $count = is_numeric($count) ? (int)$count : 0;
                        $results['purchases'] = $count;
                        $progress['purchases'] = ['total' => $count, 'current' => $count];
                        $total += $count;
                        break;
                    case 'working_docs':
                        $count = $this->saftImporter->importWorkigs();
                        // Garantir que count seja um número inteiro
                        $count = is_numeric($count) ? (int)$count : 0;
                        $results['working_docs'] = $count;
                        $progress['working_docs'] = ['total' => $count, 'current' => $count];
                        $total += $count;
                        break;
                    case 'payments':
                        $payments = $this->saftImporter->importPayments();
                        $count = is_array($payments) ? count($payments) : 0;
                        $results['payments'] = $count;
                        $progress['payments'] = ['total' => $count, 'current' => $count];
                        break;
                }
            }

            Log::info('Importação SAFT concluída', [
                'total_documentos' => $total,
                'resultados' => $results,
                'progresso' => $progress
            ]);

            return response()->json([
                'success' => true,
                'message' => "Importação concluída com sucesso",
                'total' => $total,
                'results' => $results,
                'progress' => $progress,
                'debug' => [
                    'request_data' => $request->all()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na importação SAFT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar o arquivo SAFT: ' . $e->getMessage(),
                'error_details' => [
                    'type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        } catch (\Error $e) {
            // Captura erros fatais do PHP
            Log::error('Erro fatal na importação SAFT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro fatal ao processar o arquivo SAFT: ' . $e->getMessage(),
                'error_details' => [
                    'type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        } catch (\Throwable $e) {
            // Captura qualquer outro tipo de erro
            Log::error('Erro inesperado na importação SAFT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado ao processar o arquivo SAFT: ' . $e->getMessage(),
                'error_details' => [
                    'type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}
