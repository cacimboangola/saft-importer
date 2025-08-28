@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-invoice me-2"></i>Detalhes do Documento #{{ $doc->InvoiceNo }}</span>
                    <div>
                        <a href="{{ route('doc-linha-access.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>Voltar
                        </a>
                        <button id="copyAllSql" class="btn btn-sm btn-primary">
                            <i class="fas fa-copy me-1"></i>Copiar Todo SQL (Fatura + Linhas)
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Informações do Documento</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>ID da Fatura</th>
                                        <td>{{ $doc->InvoiceId }}</td>
                                    </tr>
                                    <tr>
                                        <th>Número da Fatura</th>
                                        <td>{{ $doc->InvoiceNo }}</td>
                                    </tr>
                                    <tr>
                                        <th>Data da Fatura</th>
                                        <td>{{ $doc->InvoiceDate->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cliente</th>
                                        <td>{{ $doc->CustomerID }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Totais</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Total Líquido</th>
                                        <td>{{ number_format($doc->NetTotal, 2, ',', '.') }} AOA</td>
                                    </tr>
                                    <tr>
                                        <th>IVA</th>
                                        <td>{{ number_format($doc->TaxPayable, 2, ',', '.') }} AOA</td>
                                    </tr>
                                    <tr>
                                        <th>Total Bruto</th>
                                        <td>{{ number_format($doc->GrossTotal, 2, ',', '.') }} AOA</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-{{ $doc->InvoiceStatus == 'N' ? 'success' : 'warning' }}">
                                                {{ $doc->InvoiceStatus == 'N' ? 'Normal' : $doc->InvoiceStatus }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3">Linhas do Documento ({{ $docLinhas->count() }})</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
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
                                @foreach($docLinhas as $linha)
                                <tr>
                                    <td>{{ $linha->ProductCode }}</td>
                                    <td>{{ $linha->Description }}</td>
                                    <td>{{ number_format($linha->Quantity, 2, ',', '.') }}</td>
                                    <td>{{ number_format($linha->UnitPrice, 2, ',', '.') }} AOA</td>
                                    <td>{{ number_format($linha->TaxPercentage, 2, ',', '.') }}%</td>
                                    <td>
                                        @php
                                            $total = $linha->CreditAmount > 0 ? $linha->CreditAmount : $linha->DebitAmount;
                                        @endphp
                                        {{ number_format($total, 2, ',', '.') }} AOA
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- SQL para Fatura -->
                    <h5 class="mt-4 mb-3">SQL para Fatura (FACTURAS)</h5>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Este SQL insere a fatura na tabela <strong>FACTURAS</strong> do Access. Execute primeiro antes das linhas.
                    </div>
                    <div class="sql-container">
                        <div class="d-flex justify-content-end mb-2">
                            <button id="copyFacturaSql" class="btn btn-sm btn-success me-2">
                                <i class="fas fa-copy me-1"></i>Copiar SQL Fatura
                            </button>
                            <button id="downloadFacturaSql" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-download me-1"></i>Download SQL Fatura
                            </button>
                        </div>
                        <pre id="facturaSqlStatement" class="bg-light p-3 rounded">{{ $facturaSql }}</pre>
                    </div>

                    <!-- SQL para Linhas -->
                    <h5 class="mt-4 mb-3">SQL para Linhas (FACTURAS_ITENS)</h5>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Este SQL pode ser usado para inserir os dados na tabela <strong>Facturas_itens</strong> do Access.
                    </div>
                    <div class="sql-container">
                        <div class="d-flex justify-content-end mb-2">
                            <button id="copyLinhasSql" class="btn btn-sm btn-success me-2">
                                <i class="fas fa-copy me-1"></i>Copiar SQL Linhas
                            </button>
                            <button id="downloadSql" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-download me-1"></i>Download SQL Linhas
                            </button>
                        </div>
                        <pre id="sqlStatements" class="bg-light p-3 rounded">@foreach($sqlStatements as $sql){{ $sql }}

@endforeach</pre>
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
    .badge {
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyAllSqlBtn = document.getElementById('copyAllSql');
        const copyFacturaSqlBtn = document.getElementById('copyFacturaSql');
        const copyLinhasSqlBtn = document.getElementById('copyLinhasSql');
        const downloadSqlBtn = document.getElementById('downloadSql');
        const downloadFacturaSqlBtn = document.getElementById('downloadFacturaSql');
        
        const sqlStatements = document.getElementById('sqlStatements').textContent;
        const facturaSql = document.getElementById('facturaSqlStatement').textContent;
        const allSql = facturaSql + '\n\n-- LINHAS DA FATURA\n' + sqlStatements;
        
        // Copiar todo o SQL (fatura + linhas)
        copyAllSqlBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(allSql).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copiado!',
                    text: 'SQL completo (fatura + linhas) copiado para a área de transferência',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });
        
        // Copiar SQL da fatura
        copyFacturaSqlBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(facturaSql).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copiado!',
                    text: 'SQL da fatura copiado para a área de transferência',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });
        
        // Copiar SQL das linhas
        copyLinhasSqlBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(sqlStatements).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copiado!',
                    text: 'SQL das linhas copiado para a área de transferência',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });
        
        // Download SQL das linhas como arquivo .sql
        downloadSqlBtn.addEventListener('click', function() {
            const blob = new Blob([sqlStatements], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'facturas_itens_{{ $doc->InvoiceNo }}.sql';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
        
        // Download SQL da fatura como arquivo .sql
        downloadFacturaSqlBtn.addEventListener('click', function() {
            const blob = new Blob([facturaSql], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'fatura_{{ $doc->InvoiceNo }}.sql';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    });
</script>
@endpush
