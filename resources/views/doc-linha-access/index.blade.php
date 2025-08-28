@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-search me-2"></i>Buscar Linhas de Documento para Importar no Cacimbo Erp</span>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="searchForm" action="{{ route('doc-linha-access.search') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="invoice_number" class="form-label">ID da Fatura na Cloud</label>
                            <input type="text" class="form-control" id="invoice_number" name="invoice_number" required placeholder="Digite o ID da fatura no Laravel">
                            <div class="form-text">Insira o ID da fatura na Cloud para buscar suas linhas.</div>
                        </div>

                        <div class="mb-3">
                            <label for="access_factura_id" class="form-label">ID da Fatura no Cacimbo Erp</label>
                            <input type="number" class="form-control" id="access_factura_id" name="access_factura_id" placeholder="Digite o ID da fatura no Cacimbo Erp (opcional)">
                            <div class="form-text">Se souber o ID exato da fatura no Cacimbo Erp informe-o aqui para garantir o relacionamento correto.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </form>
                </div>
            </div>

            <div id="results" class="mt-4" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list me-2"></i>Resultados da Busca</span>
                        <button id="copyAllSql" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-copy me-1"></i>Copiar Todo SQL
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="docInfo" class="mb-4">
                            <h5>Informações do Documento</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>ID da Fatura</th>
                                        <td id="invoiceId"></td>
                                    </tr>
                                    <tr>
                                        <th>Número da Fatura</th>
                                        <td id="invoiceNo"></td>
                                    </tr>
                                    <tr>
                                        <th>Data da Fatura</th>
                                        <td id="invoiceDate"></td>
                                    </tr>
                                    <tr>
                                        <th>Cliente</th>
                                        <td id="customerID"></td>
                                    </tr>
                                    <tr>
                                        <th>Total Líquido</th>
                                        <td id="netTotal"></td>
                                    </tr>
                                    <tr>
                                        <th>Total Bruto</th>
                                        <td id="grossTotal"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h5>Linhas do Documento</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="docLinhasTable">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descrição</th>
                                        <th>Quantidade</th>
                                        <th>Preço Unit.</th>
                                        <th>IVA %</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Preenchido via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <h5 class="mt-4">SQL para Inserir no Cacimbo Erp</h5>
                        <div class="sql-container">
                            <pre id="sqlStatements" class="bg-light p-3 rounded"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sql-container {
        max-height: 400px;
        overflow-y: auto;
    }
    pre {
        white-space: pre-wrap;
        word-break: break-word;
    }
    .copy-btn {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const resultsDiv = document.getElementById('results');
        const copyAllSqlBtn = document.getElementById('copyAllSql');

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(searchForm);

            // Mostrar indicador de carregamento
            Swal.fire({
                title: 'Buscando...',
                text: 'Aguarde enquanto buscamos os dados',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("{{ route('doc-linha-access.search') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    invoice_number: formData.get('invoice_number'),
                    access_factura_id: formData.get('access_factura_id')
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();

                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.message
                    });
                    return;
                }

                // Preencher informações do documento
                document.getElementById('invoiceId').textContent = data.doc.InvoiceId;
                document.getElementById('invoiceNo').textContent = data.doc.InvoiceNo;
                document.getElementById('invoiceDate').textContent = new Date(data.doc.InvoiceDate).toLocaleDateString();
                document.getElementById('customerID').textContent = data.doc.CustomerID;
                document.getElementById('netTotal').textContent = parseFloat(data.doc.NetTotal).toLocaleString('pt-AO', {
                    style: 'currency',
                    currency: 'AOA'
                });
                document.getElementById('grossTotal').textContent = parseFloat(data.doc.GrossTotal).toLocaleString('pt-AO', {
                    style: 'currency',
                    currency: 'AOA'
                });

                // Limpar e preencher a tabela de linhas
                const tbody = document.querySelector('#docLinhasTable tbody');
                tbody.innerHTML = '';

                data.doc_linhas.forEach(linha => {
                    const row = document.createElement('tr');

                    const total = parseFloat(linha.CreditAmount) > 0
                        ? parseFloat(linha.CreditAmount)
                        : parseFloat(linha.DebitAmount);

                    row.innerHTML = `
                        <td>${linha.ProductCode}</td>
                        <td>${linha.Description}</td>
                        <td>${parseFloat(linha.Quantity).toLocaleString('pt-AO')}</td>
                        <td>${parseFloat(linha.UnitPrice).toLocaleString('pt-AO', {
                            style: 'currency',
                            currency: 'AOA'
                        })}</td>
                        <td>${parseFloat(linha.TaxPercentage).toFixed(2)}%</td>
                        <td>${total.toLocaleString('pt-AO', {
                            style: 'currency',
                            currency: 'AOA'
                        })}</td>
                    `;

                    tbody.appendChild(row);
                });

                // Preencher SQL statements
                const sqlStatementsContainer = document.getElementById('sqlStatements');
                sqlStatementsContainer.textContent = data.sql_statements.join('\n\n');

                // Mostrar resultados
                resultsDiv.style.display = 'block';

                // Rolar para os resultados
                resultsDiv.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.'
                });
                console.error('Error:', error);
            });
        });

        // Copiar todo o SQL
        copyAllSqlBtn.addEventListener('click', function() {
            const sqlText = document.getElementById('sqlStatements').textContent;
            navigator.clipboard.writeText(sqlText).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copiado!',
                    text: 'SQL copiado para a área de transferência',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });
    });
</script>
@endpush
