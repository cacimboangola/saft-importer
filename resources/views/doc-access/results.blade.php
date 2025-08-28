@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-file-invoice me-2"></i>Documentos Encontrados
                    </span>
                    <div>
                        <button id="generateBatchSql" class="btn btn-sm btn-success me-2" style="display: none;">
                            <i class="fas fa-code me-1"></i>Gerar SQL em Lote
                        </button>
                        <a href="{{ route('doc-access.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Nova Busca
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Empresa:</strong> {{ $empresa->CompanyName ?? 'N/A' }} |
                        <strong>Período:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </div>

                    @if($docs->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Nenhum documento encontrado para os critérios selecionados.
                        </div>
                    @else
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">
                                    <strong>Selecionar todos os documentos</strong>
                                </label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllTable" class="form-check-input">
                                        </th>
                                        <th>Nº Documento</th>
                                        <th>Tipo</th>
                                        <th>Data</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($docs as $doc)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input doc-checkbox" 
                                                   value="{{ $doc->InvoiceId }}" 
                                                   data-invoice-no="{{ $doc->InvoiceNo }}">
                                        </td>
                                        <td>{{ $doc->InvoiceNo }}</td>
                                        <td>{{ $doc->InvoiceType }}</td>
                                        <td>{{ $doc->InvoiceDate->format('d/m/Y') }}</td>
                                        <td>{{ $doc->CustomerID }}</td>
                                        <td>{{ number_format($doc->GrossTotal, 2, ',', '.') }} AOA</td>
                                        <td>
                                            <span class="badge bg-{{ $doc->InvoiceStatus == 'N' ? 'success' : 'warning' }}">
                                                {{ $doc->InvoiceStatus == 'N' ? 'Normal' : $doc->InvoiceStatus }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('doc-access.show-document', str_replace('/', '@', $doc->InvoiceId)) }}" 
                                               class="btn btn-sm btn-primary" 
                                               title="Ver linhas e SQL">
                                                <i class="fas fa-code me-1"></i>Ver SQL
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <p><strong>Total de documentos:</strong> {{ $docs->count() }}</p>
                                <p><span id="selectedCount">0</span> documento(s) selecionado(s)</p>
                            </div>
                            <div>
                                <button id="generateSelectedSql" class="btn btn-success" style="display: none;">
                                    <i class="fas fa-code me-1"></i>Gerar SQL dos Selecionados
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.85rem;
    }
    .sql-modal .modal-dialog {
        max-width: 90%;
    }
    .sql-container {
        max-height: 400px;
        overflow-y: auto;
    }
    pre {
        white-space: pre-wrap;
        word-break: break-word;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const selectAllTableCheckbox = document.getElementById('selectAllTable');
    const docCheckboxes = document.querySelectorAll('.doc-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    const generateBatchSqlBtn = document.getElementById('generateBatchSql');
    const generateSelectedSqlBtn = document.getElementById('generateSelectedSql');
    
    // Dados para requisição
    const companyId = '{{ $companyId }}';
    const startDate = '{{ $startDate }}';
    const endDate = '{{ $endDate }}';
    
    function updateSelectedCount() {
        const selectedCheckboxes = document.querySelectorAll('.doc-checkbox:checked');
        const count = selectedCheckboxes.length;
        selectedCountSpan.textContent = count;
        
        // Mostrar/ocultar botões baseado na seleção
        if (count > 0) {
            generateSelectedSqlBtn.style.display = 'inline-block';
            generateBatchSqlBtn.style.display = 'inline-block';
        } else {
            generateSelectedSqlBtn.style.display = 'none';
            generateBatchSqlBtn.style.display = 'none';
        }
        
        // Atualizar estado do checkbox "selecionar todos"
        const totalCheckboxes = docCheckboxes.length;
        selectAllCheckbox.checked = count === totalCheckboxes;
        selectAllTableCheckbox.checked = count === totalCheckboxes;
        selectAllCheckbox.indeterminate = count > 0 && count < totalCheckboxes;
        selectAllTableCheckbox.indeterminate = count > 0 && count < totalCheckboxes;
    }
    
    // Event listeners para checkboxes individuais
    docCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Event listener para "selecionar todos" (fora da tabela)
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        docCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        selectAllTableCheckbox.checked = isChecked;
        updateSelectedCount();
    });
    
    // Event listener para "selecionar todos" (na tabela)
    selectAllTableCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        docCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        selectAllCheckbox.checked = isChecked;
        updateSelectedCount();
    });
    
    // Gerar SQL para todos os documentos filtrados
    generateBatchSqlBtn.addEventListener('click', function() {
        generateBatchSql([]);
    });
    
    // Gerar SQL apenas para documentos selecionados
    generateSelectedSqlBtn.addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.doc-checkbox:checked'))
            .map(checkbox => checkbox.value);
        generateBatchSql(selectedIds);
    });
    
    function generateBatchSql(documentIds = []) {
        const btn = documentIds.length > 0 ? generateSelectedSqlBtn : generateBatchSqlBtn;
        const originalText = btn.innerHTML;
        
        // Mostrar loading
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Gerando SQL...';
        btn.disabled = true;
        
        // Fazer requisição
        fetch('{{ route("doc-access.batch-sql") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                company_id: companyId,
                start_date: startDate,
                end_date: endDate,
                document_ids: documentIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSqlModal(data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Erro ao gerar SQL'
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro na comunicação com o servidor'
            });
        })
        .finally(() => {
            // Restaurar botão
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
    
    function showSqlModal(data) {
        const facturasSql = data.facturas_sql.join('\n\n');
        const linhasSql = data.linhas_sql.join('\n\n');
        const allSql = facturasSql + '\n\n-- LINHAS DAS FATURAS\n\n' + linhasSql;
        
        // Criar modal
        const modalHtml = `
            <div class="modal fade sql-modal" id="sqlModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-code me-2"></i>SQL Gerado em Lote
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Processados:</strong> ${data.processed_count} documentos |
                                <strong>Ignorados:</strong> ${data.skipped_count} documentos |
                                <strong>Empresa:</strong> ${data.empresa?.CompanyName || 'N/A'} |
                                <strong>IDs Access:</strong> ${data.id_range?.start_id || 1} - ${data.id_range?.end_id || 0}
                            </div>
                            
                            <ul class="nav nav-tabs" id="sqlTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-sql" type="button">
                                        SQL Completo
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="facturas-tab" data-bs-toggle="tab" data-bs-target="#facturas-sql" type="button">
                                        Faturas (${data.facturas_sql.length})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="linhas-tab" data-bs-toggle="tab" data-bs-target="#linhas-sql" type="button">
                                        Linhas (${data.linhas_sql.length})
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-3">
                                <div class="tab-pane fade show active" id="all-sql">
                                    <div class="d-flex justify-content-end mb-2">
                                        <button class="btn btn-sm btn-success me-2" onclick="copySql('all')">
                                            <i class="fas fa-copy me-1"></i>Copiar
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="downloadSql('all', 'sql_completo_lote.sql')">
                                            <i class="fas fa-download me-1"></i>Download
                                        </button>
                                    </div>
                                    <div class="sql-container">
                                        <pre id="allSqlContent" class="bg-light p-3 rounded">${allSql}</pre>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="facturas-sql">
                                    <div class="d-flex justify-content-end mb-2">
                                        <button class="btn btn-sm btn-success me-2" onclick="copySql('facturas')">
                                            <i class="fas fa-copy me-1"></i>Copiar
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="downloadSql('facturas', 'faturas_lote.sql')">
                                            <i class="fas fa-download me-1"></i>Download
                                        </button>
                                    </div>
                                    <div class="sql-container">
                                        <pre id="facturasSqlContent" class="bg-light p-3 rounded">${facturasSql}</pre>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="linhas-sql">
                                    <div class="d-flex justify-content-end mb-2">
                                        <button class="btn btn-sm btn-success me-2" onclick="copySql('linhas')">
                                            <i class="fas fa-copy me-1"></i>Copiar
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="downloadSql('linhas', 'linhas_lote.sql')">
                                            <i class="fas fa-download me-1"></i>Download
                                        </button>
                                    </div>
                                    <div class="sql-container">
                                        <pre id="linhasSqlContent" class="bg-light p-3 rounded">${linhasSql}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remover modal existente se houver
        const existingModal = document.getElementById('sqlModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Adicionar modal ao DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('sqlModal'));
        modal.show();
    }
    
    // Funções globais para o modal
    window.copySql = function(type) {
        let content = '';
        switch(type) {
            case 'all':
                content = document.getElementById('allSqlContent').textContent;
                break;
            case 'facturas':
                content = document.getElementById('facturasSqlContent').textContent;
                break;
            case 'linhas':
                content = document.getElementById('linhasSqlContent').textContent;
                break;
        }
        
        navigator.clipboard.writeText(content).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copiado!',
                text: 'SQL copiado para a área de transferência',
                timer: 1500,
                showConfirmButton: false
            });
        });
    };
    
    window.downloadSql = function(type, filename) {
        let content = '';
        switch(type) {
            case 'all':
                content = document.getElementById('allSqlContent').textContent;
                break;
            case 'facturas':
                content = document.getElementById('facturasSqlContent').textContent;
                break;
            case 'linhas':
                content = document.getElementById('linhasSqlContent').textContent;
                break;
        }
        
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };
});
</script>
@endpush
