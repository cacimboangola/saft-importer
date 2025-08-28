<?php
namespace App\Services;

use App\Models\DocEmpresa;
use App\Models\DocEntidade;
use App\Models\DocLinha;
use App\Models\DocPayment;
use App\Models\Docs;
use App\Services\Interfaces\SAFTImporterInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SAFTImporter implements SAFTImporterInterface
{
    private $xmlContent;

    public function loadFile($filePath)
    {
        try {
            Log::info("Iniciando carregamento do arquivo SAFT", ['file' => $filePath]);

            // Verifica se o arquivo existe
            if (! file_exists($filePath)) {
                Log::error("Arquivo SAFT não encontrado", ['file' => $filePath]);
                return false;
            }

            // Verifica o tamanho do arquivo
            $fileSize = filesize($filePath);
            Log::info("Tamanho do arquivo SAFT", [
                'size_bytes'     => $fileSize,
                'size_formatted' => $this->formatBytes($fileSize),
            ]);

            // Carrega o arquivo XML com libxml_use_internal_errors para capturar erros de parsing
            libxml_use_internal_errors(true);
            $this->xmlContent = simplexml_load_file($filePath);

            // Verifica se houve erros de parsing
            if ($this->xmlContent === false) {
                $errors        = libxml_get_errors();
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->message;
                }
                libxml_clear_errors();

                Log::error("Erro ao fazer parse do XML", [
                    'errors' => $errorMessages,
                ]);

                return false;
            }

            // Verifica a estrutura básica do XML
            if (! isset($this->xmlContent->Header)) {
                Log::error("Estrutura XML inválida: nó Header não encontrado");
                return false;
            }

            // Log de sucesso
            Log::info("Arquivo SAFT carregado com sucesso", [
                'company'            => isset($this->xmlContent->Header->CompanyName) ?
                (string) $this->xmlContent->Header->CompanyName : 'N/A',
                'tax_id'             => isset($this->xmlContent->Header->TaxRegistrationNumber) ?
                (string) $this->xmlContent->Header->TaxRegistrationNumber : 'N/A',
                'has_sales_invoices' => isset($this->xmlContent->SourceDocuments->SalesInvoices) ? 'sim' : 'não',
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erro ao processar arquivo SAFT', [
                'error' => $e->getMessage(),
                'path'  => $filePath,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    private function sanitizeString($str)
    {
        // Remove caracteres especiais mantendo acentos
        $str = preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $str);
        return trim($str);
    }

    private function validateDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning('Data inválida encontrada', ['date' => $date]);
            return null;
        }
    }

    private function validateAmount($amount)
    {
        $amount = (float) $amount;
        return $amount >= 0 ? $amount : 0;
    }

    /**
     * Formata bytes para uma unidade legível
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Converte um array para XML
     */
    private function arrayToXml($array, &$xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; // Elementos numéricos não são válidos em XML
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                if (is_numeric($key)) {
                    $key = 'item' . $key; // Elementos numéricos não são válidos em XML
                }
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    public function importCompany()
    {
        if (! $this->xmlContent) {
            Log::error('Tentativa de importar empresa sem arquivo SAFT carregado');
            throw new Exception("Nenhum arquivo SAFT foi carregado.");
        }

        $header = $this->xmlContent->Header;

        $companyData = [
            'CompanyID'             => (string) $header->TaxRegistrationNumber . "-999",
            'TaxRegistrationNumber' => (string) $header->TaxRegistrationNumber,
            'CompanyName'           => $this->sanitizeString((string) $header->CompanyName),
            'CurrencyCode'          => (string) $header->CurrencyCode ?? 'AOA',
        ];

        Log::info('Importando dados da empresa', $companyData);

        try {
            // Verifica se já existe uma empresa com este CompanyID
            $existingCompany = DocEmpresa::where('CompanyID', $companyData['CompanyID'])->first();

            if ($existingCompany) {
                // Atualiza a empresa existente
                $existingCompany->update($companyData);
                $company = $existingCompany;
            } else {
                // Cria uma nova empresa
                $company = DocEmpresa::create($companyData);
            }

            Log::info('Empresa importada com sucesso', ['company_id' => $company->id]);
            return $company;

        } catch (\Exception $e) {
            Log::error('Erro ao importar empresa', [
                'error'        => $e->getMessage(),
                'company_data' => $companyData,
            ]);
            throw new Exception("Erro ao importar empresa: " . $e->getMessage());
        }
    }

    public function importCustomers()
    {
        if (! $this->xmlContent) {
            Log::error('Tentativa de importar clientes sem arquivo SAFT carregado');
            throw new Exception("Nenhum arquivo SAFT foi carregado.");
        }

        $customers      = [];
        $companyId      = (string) $this->xmlContent->Header->TaxRegistrationNumber . "-999";
        $totalCustomers = 0;

        try {
            if (isset($this->xmlContent->MasterFiles->Customer)) {
                foreach ($this->xmlContent->MasterFiles->Customer as $customer) {
                    $customerData = [
                        'CustomerID'    => $companyId . "-" . (string) $customer->CustomerID,
                        'CompanyID'     => $companyId,
                        'AccountID'     => (string) $customer->AccountID,
                        'CustomerTaxID' => (string) $customer->CustomerTaxID,
                        'CompanyName'   => $this->sanitizeString((string) $customer->CompanyName),
                        'AddressDetail' => $this->sanitizeString((string) $customer->BillingAddress->AddressDetail),
                        'City'          => $this->sanitizeString((string) $customer->BillingAddress->City),
                        'Country'       => (string) $customer->BillingAddress->Country,
                        'Email'         => filter_var((string) $customer->Email, FILTER_SANITIZE_EMAIL) ?? '',
                        'Telefone'      => preg_replace('/[^0-9+\-\s]/', '', (string) $customer->Telephone) ?? '',
                    ];

                    Log::debug('Importando cliente', [
                        'customer_id' => $customerData['CustomerID'],
                        'name'        => $customerData['CompanyName'],
                    ]);

                    // Verifica se o cliente já existe
                    $existingCustomer = DocEntidade::where('CustomerID', $customerData['CustomerID'])->first();

                    if ($existingCustomer) {
                        // Atualiza o cliente existente
                        $existingCustomer->update($customerData);
                        $customers[] = $existingCustomer;
                    } else {
                        // Cria um novo cliente
                        $customers[] = DocEntidade::create($customerData);
                    }

                    $totalCustomers++;
                }
            }

            Log::info('Clientes importados com sucesso', ['total' => $totalCustomers]);
            return $customers;

        } catch (\Exception $e) {
            Log::error('Erro ao importar clientes', [
                'error'      => $e->getMessage(),
                'company_id' => $companyId,
            ]);
            throw new Exception("Erro ao importar clientes: " . $e->getMessage());
        }
    }

    private function importDocumentLines($document, $invoiceData)
    {
        // Verifica se o documento tem linhas
        if (! isset($document->Line) || count($document->Line) === 0) {
            Log::warning("Documento sem linhas", [
                'invoice_id' => $invoiceData['InvoiceId'],
                'invoice_no' => $invoiceData['InvoiceNo'],
            ]);
            return;
        }

        Log::info("Importando linhas do documento", [
            'invoice_id'   => $invoiceData['InvoiceId'],
            'total_linhas' => count($document->Line),
        ]);

        // Implementação futura - apenas log para depuração
        foreach ($document->Line as $index => $line) {
            // Garantir que o índice seja um inteiro
            $indexNum = is_numeric($index) ? (int) $index : 0;

            Log::debug("Linha do documento", [
                'invoice_id'    => $invoiceData['InvoiceId'],
                'linha_num'     => $indexNum + 1, // Agora ambos são inteiros
                'product_code'  => isset($line->ProductCode) ? (string) $line->ProductCode : 'N/A',
                'description'   => isset($line->Description) ? (string) $line->Description : 'N/A',
                'quantity'      => isset($line->Quantity) ? (string) $line->Quantity : '0',
                'unit_price'    => isset($line->UnitPrice) ? (string) $line->UnitPrice : '0.00',
                'credit_amount' => isset($line->CreditAmount) ? (string) $line->CreditAmount : '0.00',
            ]);
        }

        // Remove as linhas existentes
        DocLinha::where('InvoiceId', $invoiceData['InvoiceId'])->delete();

        // Prepara todos os dados das linhas
        $lines = [];
        foreach ($document->Line as $line) {
            // Garantir que LineNumber seja uma string
            $lineNumberStr = isset($line->LineNumber) ? (string) $line->LineNumber : '0';
            $lineNumber    = $invoiceData['InvoiceId'] . '-' . str_pad($lineNumberStr, 3, '0', STR_PAD_LEFT);

            $lines[] = [
                'InvoiceId'          => $invoiceData['InvoiceId'],
                'LineNumber'         => $lineNumber,
                'ProductCode'        => (string) $line->ProductCode,
                'ProductDescription' => $this->sanitizeString((string) $line->ProductDescription),
                'Quantity'           => (float) $line->Quantity,
                'UnitOfMeasure'      => 'Un',
                'UnitPrice'          => $this->validateAmount((string) $line->UnitPrice),
                'TaxPointDate'       => $this->validateDate((string) $line->TaxPointDate),
                'Description'        => $this->sanitizeString((string) $line->Description),
                'CreditAmount'       => $this->validateAmount((string) $line->CreditAmount ?? 0),
                'DebitAmount'        => $this->validateAmount((string) $line->DebitAmount ?? 0),
                'TaxType'            => isset($line->Tax->TaxType) ? (string) $line->Tax->TaxType : '',
                'TaxCode'            => isset($line->Tax->TaxCode) ? (string) $line->Tax->TaxCode : '',
                'TaxPercentage'      => isset($line->Tax->TaxPercentage) ? (float) $line->Tax->TaxPercentage : 0,
                'TaxExemptionReason' => (string) $line->TaxExemptionReason ?? '',
                'SettlementAmount'   => $this->validateAmount((string) $line->SettlementAmount ?? 0),
                'linhaRemovida'      => '0',
                'ArtigoPontos'       => '0.00',
                'artigo_peso'        => '0',
                'artigo_volume'      => '0',
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        // Insere todas as linhas de uma vez
        if (! empty($lines)) {
            try {
                DB::connection('cacimbodocs')->table('doc_linhas')->insert($lines);
                Log::info('Linhas do documento inseridas com sucesso', [
                    'invoice_id'   => $invoiceData['InvoiceId'],
                    'total_linhas' => count($lines),
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao inserir linhas do documento', [
                    'invoice_id' => $invoiceData['InvoiceId'],
                    'error'      => $e->getMessage(),
                    'trace'      => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('Linhas do documento importadas com sucesso', [
            'invoice_id'  => $invoiceData['InvoiceId'],
            'total_lines' => count($document->Line),
        ]);

    }

    public function importSales(array $invoiceTypes = [])
    {
        Log::info("Iniciando importação de vendas", ['tipos_selecionados' => $invoiceTypes]);

        // Verifica se o arquivo SAFT foi carregado
        if (! isset($this->xmlContent)) {
            Log::error("Arquivo SAFT não carregado");
            return 0;
        }

        $result = $this->importSalesInvoices($invoiceTypes);
        Log::info("Resultado da importação de vendas", ['count' => $result]);
        return $result;
    }

    public function importPurchases()
    {
        try {
            if (! $this->xmlContent) {
                throw new Exception("Nenhum arquivo SAFT foi carregado.");
            }

            $count = $this->importPurchaseInvoices();
            return $count;

        } catch (\Exception $e) {
            Log::error('Erro ao importar faturas de compra', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("Erro ao importar faturas de compra: " . $e->getMessage());
        }
    }

    public function importWorkigs()
    {
        try {
            if (! $this->xmlContent) {
                throw new Exception("Nenhum arquivo SAFT foi carregado.");
            }

            $count = $this->importWorkingDocuments();
            return $count;

        } catch (\Exception $e) {
            Log::error('Erro ao importar documentos de trabalho', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("Erro ao importar documentos de trabalho: " . $e->getMessage());
        }
    }

    public function importPayments()
    {
        if (! $this->xmlContent) {
            Log::error('Tentativa de importar pagamentos sem arquivo SAFT carregado');
            throw new Exception("Nenhum arquivo SAFT foi carregado.");
        }

        $payments      = [];
        $companyId     = (string) $this->xmlContent->Header->TaxRegistrationNumber . "-999";
        $totalPayments = 0;

        if (isset($this->xmlContent->SourceDocuments->Payments->Payment)) {
            foreach ($this->xmlContent->SourceDocuments->Payments->Payment as $payment) {
                $paymentData = [
                    'PaymentRefNo'      => (string) $payment->PaymentRefNo,
                    'TransactionID'     => (string) $payment->TransactionID,
                    'TransactionDate'   => $this->validateDate((string) $payment->TransactionDate),
                    'PaymentType'       => (string) $payment->PaymentType,
                    'Description'       => (string) $payment->Description,
                    'SystemID'          => (string) $payment->SystemID,
                    'DocumentStatus'    => (string) $payment->DocumentStatus->PaymentStatus,
                    'PaymentStatusDate' => $this->validateDate((string) $payment->DocumentStatus->PaymentStatusDate),
                    'PaymentAmount'     => $this->validateAmount((float) $payment->DocumentTotals->GrossTotal),
                    'CompanyID'         => $companyId,
                    'CustomerID'        => (string) $payment->CustomerID,
                ];

                Log::debug('Importando pagamento', [
                    'ref_no' => $paymentData['PaymentRefNo'],
                    'amount' => $paymentData['PaymentAmount'],
                ]);

                $paymentModel = DocPayment::updateOrCreate(
                    ['PaymentRefNo' => $paymentData['PaymentRefNo']],
                    $paymentData
                );

                if (isset($payment->Line)) {
                    foreach ($payment->Line as $line) {
                        $lineData = [
                            'LineNumber'       => (string) $line->LineNumber,
                            'SourceDocumentID' => (string) $line->SourceDocumentID,
                            'SettlementAmount' => $this->validateAmount((float) $line->SettlementAmount),
                            'PaymentRefNo'     => $paymentData['PaymentRefNo'],
                        ];

                        Log::debug('Processando linha de pagamento', [
                            'source_doc' => $lineData['SourceDocumentID'],
                            'amount'     => $lineData['SettlementAmount'],
                        ]);

                        if ($lineData['SourceDocumentID']) {
                            // Removida a atualização do campo PaymentStatus que não existe na tabela
                            Docs::where('InvoiceId', $lineData['SourceDocumentID'])
                                ->update(['doc_status' => 'Pago']);

                            Log::debug('Documento marcado como pago', [
                                'invoice_id' => $lineData['SourceDocumentID'],
                            ]);
                        }
                    }
                }

                $payments[] = $paymentModel;
                $totalPayments++;
            }
        }

        Log::info('Pagamentos importados com sucesso', ['total' => $totalPayments]);

        return $payments;
    }

    private function importDocuments($documents, $sourceType)
    {
        try {
            // Verifica se documents é iterável
            if (! is_array($documents) && ! ($documents instanceof \Traversable)) {
                Log::error("Documentos não são iteráveis", [
                    'tipo'        => gettype($documents),
                    'source_type' => $sourceType,
                ]);
                return 0;
            }

            $count = 0;
            $total = count($documents);

            Log::info("Iniciando importação de documentos", [
                'tipo'       => $sourceType,
                'total'      => $total,
                'tipo_dados' => gettype($documents),
            ]);

            foreach ($documents as $document) {
                $invoiceNo = (string) $document->InvoiceNo;
                if (empty($invoiceNo)) {
                    Log::warning("Documento sem InvoiceNo encontrado", ['document' => json_encode($document)]);
                    continue;
                }

                $series = '';
                if (preg_match('/^(.+?)[^a-zA-Z0-9]\d+/', (string) $document->InvoiceNo, $matches)) {
                    $series = $matches[1];
                }

                // Preparar os dados do documento para inserção
                $invoiceData = [
                    'InvoiceId'                     => (string) $this->xmlContent->Header->TaxRegistrationNumber . "-999 " . $invoiceNo,
                    'InvoiceNo'                     => $invoiceNo,
                    'InvoiceStatus'                 => (string) $document->DocumentStatus->InvoiceStatus ?: 'N',
                    'InvoiceStatusDate'             => $this->validateDate((string) $document->DocumentStatus->InvoiceStatusDate),
                    'SourceBilling'                 => (string) $document->SourceBilling ?: null,
                    'HASH'                          => (string) $document->HASH ?: '',
                    'Period'                        => null,
                    'InvoiceDate'                   => $this->validateDate((string) $document->InvoiceDate),
                    'SourceDocuments'               => $sourceType,
                    'InvoiceType'                   => $document->InvoiceType ?: '',
                    'InvoiceTypeSerie'              => (string) $series ?: null,
                    'SystemEntryDate'               => $this->validateDate((string) $document->SystemEntryDate),
                    'CustomerID'                    => $this->xmlContent->Header->TaxRegistrationNumber . "-999" . "-" . $document->CustomerID,
                    'CustomerComID'                 => null,
                    'CompanyID'                     => (string) $this->xmlContent->Header->TaxRegistrationNumber . "-999",
                    'AddressDetail'                 => null,
                    'City'                          => null,
                    'Country'                       => null,
                    'TaxPayable'                    => $this->validateAmount((string) $document->DocumentTotals->TaxPayable) ?: '0.00',
                    'NetTotal'                      => $this->validateAmount((string) $document->DocumentTotals->NetTotal) ?: '0.00',
                    'GrossTotal'                    => $this->validateAmount((string) $document->DocumentTotals->GrossTotal) ?: '0.00',
                    'IRT_WithholdingTaxType'        => null,
                    'IRT_WithholdingTaxDescription' => null,
                    'IRT_WithholdingTaxAmount'      => null,
                    'IS_WithholdingTaxType'         => null,
                    'IS_WithholdingTaxDescription'  => null,
                    'IS_WithholdingTaxAmount'       => null,
                    'idVeiculo'                     => null,
                    'idVendedor'                    => null,
                    'idProjecto'                    => null,
                    'idUser'                        => null,
                    // Campos adicionais para evitar erros de colunas não encontradas
                    'PontosTotal'                   => '0.00',
                    'Settlement'                    => 0,
                    'sync_status'                   => 0,
                    'doc_status'                    => 'Pendente',
                    'OnlineStorePO'                 => 0,
                    'grossWeight'                   => '0.00',
                    'grossVolume'                   => '0.00',
                    // Campos adicionais para metadados
                    'idApiUser'                     => null,
                    'DocDescription'                => null,
                    'DocObs'                        => null,
                    'DocRef'                        => null,
                    'idRetornoApi'                  => null,
                    'hash_sinc'                     => null,
                    'created_at'                    => now(),
                    'updated_at'                    => now(),
                ];

                // Verifica se a classe Docs existe e está acessível
                try {
                    // Log detalhado dos dados do documento
                    Log::debug("Dados do documento para importação", [
                        'invoice_id'   => $invoiceData['InvoiceId'],
                        'invoice_no'   => $invoiceData['InvoiceNo'],
                        'invoice_type' => $invoiceData['InvoiceType'],
                        'customer_id'  => $invoiceData['CustomerID'],
                        'gross_total'  => $invoiceData['GrossTotal'],
                    ]);

                    // Verifica se a classe Docs existe
                    if (! class_exists('App\\Models\\Docs')) {
                        Log::error("Classe Docs não encontrada");
                        throw new \Exception("Classe Docs não encontrada");
                    }

                    $doc = Docs::updateOrCreate(
                        ['InvoiceId' => $invoiceData['InvoiceId']],
                        $invoiceData
                    );

                    Log::info("Documento importado com sucesso", [
                        'invoice_id' => $invoiceData['InvoiceId'],
                        'id'         => $doc->id,
                    ]);

                    // Importa as linhas do documento
                    $this->importDocumentLines($document, $invoiceData);

                    $count++;

                    if ($count % 10 == 0 || $count == $total) {
                        Log::info("Progresso da importação", [
                            'tipo'            => $sourceType,
                            'documento_atual' => $invoiceData['InvoiceNo'],
                            'progresso'       => "$count/$total",
                            'porcentagem'     => round(($count / $total) * 100, 2) . '%',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao importar documento individual", [
                        'invoice_id' => $invoiceData['InvoiceId'],
                        'error'      => $e->getMessage(),
                    ]);
                    // Continua para o próximo documento
                    continue;
                }
            }

            Log::info("Documentos importados com sucesso", [
                'tipo'             => $sourceType,
                'total'            => $count,
                'total_processado' => $total,
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error("Erro ao importar $sourceType", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("Erro ao importar $sourceType: " . $e->getMessage());
        }
    }

    public function importSalesInvoices(array $invoiceTypes = [])
    {
        Log::info("Iniciando importação de faturas de venda", [
            'tipos_selecionados' => $invoiceTypes,
            'xml_loaded'         => isset($this->xmlContent) ? 'sim' : 'não',
        ]);

        // Configuração para debug
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Verificar se o XML está carregado corretamente
        if (! isset($this->xmlContent)) {
            Log::error("XML não carregado");
            return 0;
        }

        if (! is_object($this->xmlContent)) {
            Log::error("XML não é um objeto válido", ['xml_content' => gettype($this->xmlContent)]);
            return 0;
        }

        Log::info("XML carregado corretamente", [
            'tipo'                 => get_class($this->xmlContent),
            'tem_source_documents' => isset($this->xmlContent->SourceDocuments) ? 'sim' : 'não',
        ]);

        // Verificar se a estrutura do XML contém faturas de venda
        if (! isset($this->xmlContent->SourceDocuments)) {
            Log::error("Estrutura SourceDocuments não encontrada no XML");
            return 0;
        }

        if (! isset($this->xmlContent->SourceDocuments->SalesInvoices)) {
            Log::error("Estrutura SalesInvoices não encontrada no XML");
            return 0;
        }

        // Dump da estrutura para debug
        Log::debug("Estrutura do nó SalesInvoices", [
            'children' => json_encode(array_keys(get_object_vars($this->xmlContent->SourceDocuments->SalesInvoices))),
        ]);

        if (! isset($this->xmlContent->SourceDocuments->SalesInvoices->Invoice)) {
            Log::warning("Nenhuma fatura de venda encontrada no arquivo SAFT");
            return 0;
        }

        // Abordagem direta para obter todas as faturas
        // Converte o XML para string e recarrega para garantir acesso a todos os elementos
        $xmlString        = $this->xmlContent->SourceDocuments->SalesInvoices->asXML();
        $salesInvoicesXml = new \SimpleXMLElement($xmlString);

        // Usa xpath para obter todos os elementos Invoice
        $invoices = $salesInvoicesXml->xpath('//Invoice');

        Log::info("Total de faturas encontradas com xpath", [
            'count' => count($invoices),
        ]);

        // Se não encontrou nenhuma fatura com xpath, tenta outra abordagem
        if (empty($invoices)) {
            // Tenta acessar diretamente e converter para array
            $allDocuments = $this->xmlContent->SourceDocuments->SalesInvoices->Invoice;

            // Se for um objeto único, converte para array com um elemento
            if (! is_array($allDocuments)) {
                Log::info("Invoice é um objeto único, convertendo para array");
                $allDocuments = [$allDocuments];
            }
        } else {
            // Usa os resultados do xpath
            $allDocuments = $invoices;
        }

        // Log para debug
        Log::debug("Estrutura final do array de faturas", [
            'tipo'     => gettype($allDocuments),
            'is_array' => is_array($allDocuments) ? 'sim' : 'não',
            'count'    => count($allDocuments),
        ]);

        $total = count($allDocuments);
        Log::info("Total de faturas de venda encontradas: $total", [
            'tipo_dados' => gettype($allDocuments),
        ]);

        // Verificar se há documentos para processar
        if ($total === 0) {
            Log::warning("Nenhuma fatura de venda encontrada no nó Invoice");
            return 0;
        }

        // Se não houver tipos específicos selecionados, importar todos os documentos
        if (empty($invoiceTypes)) {
            Log::info("Nenhum tipo de fatura especificado, importando todas as faturas");
            return $this->importDocuments($allDocuments, 'SalesInvoices');
        }

        // Mapear os tipos de documentos presentes no arquivo
        $tiposPresentes = [];
        foreach ($allDocuments as $document) {
            // Verifica se InvoiceType existe no documento
            if (! isset($document->InvoiceType)) {
                Log::warning("Documento sem InvoiceType encontrado", [
                    'invoice_no' => isset($document->InvoiceNo) ? (string) $document->InvoiceNo : 'desconhecido',
                ]);
                continue;
            }

            $invoiceType = (string) $document->InvoiceType;
            if (! in_array($invoiceType, $tiposPresentes)) {
                $tiposPresentes[] = $invoiceType;
            }
        }

        Log::info("Tipos de faturas presentes no arquivo SAFT:", ['tipos' => $tiposPresentes]);
        Log::info("Tipos de faturas selecionados para importação:", ['tipos' => $invoiceTypes]);

        // Filtrar documentos pelos tipos selecionados
        $filteredDocuments = [];
        $tiposEncontrados  = [];

        foreach ($allDocuments as $document) {
            // Verifica se InvoiceType existe no documento
            if (! isset($document->InvoiceType)) {
                Log::warning("Documento sem InvoiceType encontrado durante filtragem", [
                    'invoice_no' => isset($document->InvoiceNo) ? (string) $document->InvoiceNo : 'desconhecido',
                ]);
                continue;
            }

            $invoiceType = (string) $document->InvoiceType;

            Log::debug("Verificando documento", [
                'invoice_no'   => isset($document->InvoiceNo) ? (string) $document->InvoiceNo : 'desconhecido',
                'invoice_type' => $invoiceType,
            ]);

            // Verifica se o tipo de fatura está na lista de tipos selecionados
            if (in_array($invoiceType, $invoiceTypes)) {
                Log::debug("Documento adicionado à lista filtrada", [
                    'invoice_no'   => isset($document->InvoiceNo) ? (string) $document->InvoiceNo : 'desconhecido',
                    'invoice_type' => $invoiceType,
                ]);

                $filteredDocuments[] = $document;
                if (! in_array($invoiceType, $tiposEncontrados)) {
                    $tiposEncontrados[] = $invoiceType;
                }
            }
        }

        $filteredTotal = count($filteredDocuments);
        Log::info("Faturas de venda filtradas por tipo: $filteredTotal de $total", [
            'tipos_selecionados' => $invoiceTypes,
            'tipos_encontrados'  => $tiposEncontrados,
        ]);

        if ($filteredTotal === 0) {
            Log::warning("Nenhuma fatura encontrada para os tipos selecionados", [
                'tipos_selecionados' => $invoiceTypes,
                'tipos_presentes'    => $tiposPresentes,
            ]);
        }

        // Verificar se há documentos filtrados para importar
        if (empty($filteredDocuments)) {
            Log::warning("Nenhum documento encontrado após a filtragem por tipos", [
                'tipos_selecionados' => $invoiceTypes,
                'tipos_presentes'    => $tiposPresentes,
            ]);
            return 0;
        }

        $result = $this->importDocuments($filteredDocuments, 'SalesInvoices');
        Log::info("Resultado da importação de faturas de venda", ['count' => $result]);
        return $result;
    }

    public function importWorkingDocuments()
    {
        if (! isset($this->xmlContent->SourceDocuments->WorkingDocuments->WorkDocument)) {
            return 0;
        }
        $documents = $this->xmlContent->SourceDocuments->WorkingDocuments->WorkDocument;
        $total     = count($documents);
        Log::info("Total de documentos de trabalho encontrados: $total");
        return $this->importDocuments($documents, 'WorkingDocuments');
    }

    public function importPurchaseInvoices()
    {
        if (! isset($this->xmlContent->SourceDocuments->PurchaseInvoices->Invoice)) {
            return 0;
        }
        $documents = $this->xmlContent->SourceDocuments->PurchaseInvoices->Invoice;
        $total     = count($documents);
        Log::info("Total de faturas de compra encontradas: $total");
        return $this->importDocuments($documents, 'PurchaseInvoices');
    }

    private function createDocumentData($document, $type, $companyId)
    {
        // Extract series from InvoiceNo (e.g., "FT FA.2021/564" -> "FA")
        $series = '';
        if (preg_match('/\s([A-Z]+)\./', (string) $document->InvoiceNo, $matches)) {
            $series = $matches[1];
        }

        return [
            'InvoiceId'                     => $companyId . (string) $document->InvoiceNo,
            'InvoiceNo'                     => (string) $document->InvoiceNo,
            'InvoiceStatus'                 => (string) $document->DocumentStatus->InvoiceStatus,
            'InvoiceStatusDate'             => $this->validateDate((string) $document->DocumentStatus->InvoiceStatusDate),
            'HASH'                          => (string) $document->Hash,
            'Period'                        => null,
            'InvoiceDate'                   => $this->validateDate((string) $document->InvoiceDate),
            'SystemEntryDate'               => $this->validateDate((string) $document->SystemEntryDate) ?? now(),
            'CustomerID'                    => (string) ($document->CustomerID ?? $document->SupplierID),
            'CustomerComID'                 => null,
            'CompanyID'                     => $companyId,
            'AddressDetail'                 => null,
            'City'                          => null,
            'Country'                       => 'AO',
            'TaxPayable'                    => $this->validateAmount((float) $document->DocumentTotals->TaxPayable),
            'NetTotal'                      => $this->validateAmount((float) $document->DocumentTotals->NetTotal),
            'GrossTotal'                    => $this->validateAmount((float) $document->DocumentTotals->GrossTotal),
            'InvoiceType'                   => $type,
            'InvoiceTypeSerie'              => $series ?: 'A',
            'SourceBilling'                 => 'P',
            'SourceDocuments'               => 'N',
            'IRT_WithholdingTaxType'        => null,
            'IRT_WithholdingTaxDescription' => null,
            'IRT_WithholdingTaxAmount'      => 0,
            'IS_WithholdingTaxType'         => null,
            'IS_WithholdingTaxDescription'  => null,
            'IS_WithholdingTaxAmount'       => 0,
            'idVeiculo'                     => null,
            'idVendedor'                    => null,
            'idProjecto'                    => null,
            'idUser'                        => null,
            'idApiUser'                     => null,
            'DocDescription'                => null,
            'DocObs'                        => null,
            'DocRef'                        => null,
            'PontosTotal'                   => 0,
            'Settlement'                    => 0,
            'doc_status'                    => 'active',
            'OnlineStorePO'                 => null,
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ];
    }
}
