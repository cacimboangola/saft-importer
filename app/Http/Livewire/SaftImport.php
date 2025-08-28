<?php

namespace App\Livewire;

use App\Services\SAFTImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class SaftImport extends Component
{
    use WithFileUploads;

    public $saft_file;
    public $importTypes = [
        'company' => true,
        'customers' => false,
        'sales' => false,
        'purchases' => false,
        'working_docs' => false,
        'payments' => false
    ];
    public $importing = false;
    public $progress = 0;
    public $results = [];
    public $error = null;

    protected $rules = [
        'saft_file' => 'required|file|mimes:xml',
        'importTypes' => 'required|array'
    ];

    public function mount()
    {
        $this->importing = false;
        $this->progress = 0;
        $this->results = [];
        $this->error = null;
    }

    public function import()
    {
        try {
            $this->validate();

            $this->importing = true;
            $this->progress = 0;
            $this->error = null;
            $this->results = [];

            Log::info('Iniciando importação SAFT');

            // Get selected import types
            $selectedTypes = collect($this->importTypes)
                ->filter(fn($value) => $value)
                ->keys()
                ->toArray();

            if (empty($selectedTypes)) {
                $this->error = 'Selecione pelo menos um tipo de documento para importar.';
                return;
            }

            Log::info('Tipos de documentos selecionados', ['types' => $selectedTypes]);

            // Configure database session
            DB::statement('SET SESSION innodb_lock_wait_timeout = 300');
            DB::statement('SET SESSION wait_timeout = 300');
            DB::statement('SET SESSION max_execution_time = 300000'); // 5 minutes

            $saftImporter = app(SAFTImporter::class);
            $saftImporter->loadFile($this->saft_file->getRealPath());
            Log::info('Arquivo SAFT carregado com sucesso');

            $result = [];
            $totalSteps = count($selectedTypes);
            $currentStep = 0;

            // Import company
            if (in_array('company', $selectedTypes)) {
                DB::beginTransaction();
                try {
                    Log::info('Importando dados da empresa');
                    $result['company'] = $saftImporter->importCompany();
                    Log::info('Empresa importada', ['company' => $result['company']]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao importar empresa', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                $currentStep++;
                $this->progress = ($currentStep / $totalSteps) * 100;
            }

            // Import customers
            if (in_array('customers', $selectedTypes)) {
                DB::beginTransaction();
                try {
                    Log::info('Importando clientes');
                    $result['customers'] = $saftImporter->importCustomers();
                    Log::info('Clientes importados', ['count' => count($result['customers'])]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao importar clientes', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                $currentStep++;
                $this->progress = ($currentStep / $totalSteps) * 100;
            }

            // Import sales invoices
            if (in_array('sales', $selectedTypes)) {
                DB::beginTransaction();
                try {
                    Log::info('Importando faturas de venda');
                    $result['sales_invoices'] = $saftImporter->importSalesInvoices();
                    Log::info('Faturas de venda importadas', ['count' => count($result['sales_invoices'])]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao importar faturas de venda', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                $currentStep++;
                $this->progress = ($currentStep / $totalSteps) * 100;
            }

            // Import purchase invoices
            if (in_array('purchases', $selectedTypes)) {
                DB::beginTransaction();
                try {
                    Log::info('Importando faturas de compra');
                    $result['purchase_invoices'] = $saftImporter->importPurchaseInvoices();
                    Log::info('Faturas de compra importadas', ['count' => count($result['purchase_invoices'])]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao importar faturas de compra', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                $currentStep++;
                $this->progress = ($currentStep / $totalSteps) * 100;
            }

            // Import working documents
            if (in_array('working_docs', $selectedTypes)) {
                DB::beginTransaction();
                try {
                    Log::info('Importando documentos de trabalho');
                    $result['working_documents'] = $saftImporter->importWorkingDocuments();
                    Log::info('Documentos de trabalho importados', ['count' => count($result['working_documents'])]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao importar documentos de trabalho', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                $currentStep++;
                $this->progress = ($currentStep / $totalSteps) * 100;
            }

            // Import payments
            if (in_array('payments', $selectedTypes)) {
                DB::beginTransaction();
                try {
                    Log::info('Importando pagamentos');
                    $result['payments'] = $saftImporter->importPayments();
                    Log::info('Pagamentos importados', ['count' => count($result['payments'])]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao importar pagamentos', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                $currentStep++;
                $this->progress = ($currentStep / $totalSteps) * 100;
            }

            // Prepare results summary
            if (isset($result['company'])) {
                $this->results['company'] = $result['company'];
            }
            if (isset($result['customers'])) {
                $this->results['customers_count'] = count($result['customers']);
            }
            if (isset($result['sales_invoices'])) {
                $this->results['sales_invoices_count'] = count($result['sales_invoices']);
            }
            if (isset($result['purchase_invoices'])) {
                $this->results['purchase_invoices_count'] = count($result['purchase_invoices']);
            }
            if (isset($result['working_documents'])) {
                $this->results['working_documents_count'] = count($result['working_documents']);
            }
            if (isset($result['payments'])) {
                $this->results['payments_count'] = count($result['payments']);
            }

            Log::info('Importação concluída com sucesso', ['summary' => $this->results]);
            $this->importing = false;

        } catch (\Exception $e) {
            Log::error('Erro na importação SAFT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error = 'Erro ao importar SAFT: ' . $e->getMessage();
            $this->importing = false;
        }
    }

    public function render()
    {
        return view('livewire.saft-import');
    }
}
