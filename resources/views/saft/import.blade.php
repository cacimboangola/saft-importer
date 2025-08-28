@extends('layouts.app')

@push('styles')
<style>
    /* Estilos existentes... */
    .wizard-container {
        max-width: 1000px;
        margin: 2rem auto;
    }
    .wizard-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .wizard-header {
        background: linear-gradient(135deg, #b71c50, #c30510);
        color: white;
        padding: 2rem;
        border-radius: 1.5rem 1.5rem 0 0;
        position: relative;
        overflow: hidden;
    }
    .wizard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
        opacity: 0.1;
    }
    .wizard-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .wizard-subtitle {
        color: rgba(255, 255, 255, 0.9);
        margin-top: 0.5rem;
    }
    .wizard-body {
        padding: 2rem;
    }
    .upload-zone {
        background: #f8fafc;
        border: 2px dashed #e2e8f0;
        border-radius: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: #b71c50;
        background: #fff5f5;
    }
    .upload-zone.has-file {
        background: #f0fdf4;
        border-color: #22c55e;
    }
    .upload-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        background: #b71c50;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }
    .upload-zone:hover .upload-icon {
        transform: scale(1.1);
    }
    .upload-text {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    .upload-hint {
        color: #64748b;
    }
    .import-options {
        margin-top: 2rem;
    }
    .option-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .option-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .option-card:hover {
        border-color: #b71c50;
        transform: translateY(-2px);
    }
    .option-card.selected {
        background: #fff5f5;
        border-color: #b71c50;
    }
    .option-card.selected::before {
        content: '✓';
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 1.5rem;
        height: 1.5rem;
        background: #b71c50;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
    }
    .option-card .option-icon {
        width: 3rem;
        height: 3rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: #b71c50;
        font-size: 1.25rem;
        transition: all 0.3s ease;
    }
    .option-card.selected .option-icon {
        background: #b71c50;
        color: white;
    }
    .option-card .option-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    .option-card .option-description {
        font-size: 0.875rem;
        color: #64748b;
        line-height: 1.4;
    }
    .import-button {
        background: linear-gradient(135deg, #b71c50, #c30510);
        color: white;
        border: none;
        border-radius: 0.75rem;
        padding: 1rem 2rem;
        font-size: 1.125rem;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .import-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(227, 6, 19, 0.3);
    }
    .import-button:active {
        transform: translateY(0);
    }
    .import-button i {
        margin-right: 0.5rem;
    }
    .import-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: 0.5s;
    }
    .import-button:hover::before {
        left: 100%;
    }
    .results-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
    }
    .results-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    .result-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .result-icon {
        width: 48px;
        height: 48px;
        background: #eff6ff;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #b71c50;
        font-size: 1.25rem;
    }
    .result-info {
        flex: 1;
    }
    .result-label {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    .result-count {
        font-size: 0.875rem;
        color: #64748b;
    }
    .alert {
        border: none;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .alert-success {
        background: #f0fdf4;
        color: #166534;
    }
    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
    }
    .alert-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .alert-success .alert-icon {
        background: #dcfce7;
        color: #16a34a;
    }
    .alert-danger .alert-icon {
        background: #fee2e2;
        color: #b71c50;
    }
    .alert-content {
        flex: 1;
    }
    .progress-indicator {
        display: none;
        margin-top: 1rem;
    }
    .progress-indicator.active {
        display: block;
    }
    .progress-bar {
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(135deg, #b71c50, #c30510);
        width: 0;
        transition: width 0.3s ease;
    }
    .progress-status {
        display: flex;
        justify-content: space-between;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }
    .progress-section {
        display: none;
        margin-top: 2rem;
        padding: 2rem;
        background: #f8fafc;
        border-radius: 1rem;
    }
    .progress-section.active {
        display: block;
    }
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .progress-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }
    .progress-status {
        font-size: 0.875rem;
        color: #64748b;
    }
    .progress-bar-container {
        background: #e2e8f0;
        border-radius: 0.5rem;
        height: 1rem;
        overflow: hidden;
    }
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #b71c50, #c30510);
        transition: width 0.3s ease;
        border-radius: 0.5rem;
        position: relative;
    }
    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            45deg,
            rgba(255,255,255,0.2) 25%,
            transparent 25%,
            transparent 50%,
            rgba(255,255,255,0.2) 50%,
            rgba(255,255,255,0.2) 75%,
            transparent 75%,
            transparent
        );
        background-size: 1rem 1rem;
        animation: progress-animation 1s linear infinite;
    }
    @keyframes progress-animation {
        from { background-position: 1rem 0; }
        to { background-position: 0 0; }
    }
    .document-list {
        margin-top: 1rem;
        max-height: 200px;
        overflow-y: auto;
    }
    .document-item {
        padding: 0.5rem;
        border-radius: 0.5rem;
        background: white;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #1e293b;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .document-item:last-child {
        margin-bottom: 0;
    }
    .document-item .status {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        background: #e2e8f0;
        color: #64748b;
    }
    .document-item .status.success {
        background: #f0fdf4;
        color: #16a34a;
    }
</style>
@endpush

@section('content')
<div class="wizard-container">
    <div class="wizard-card">
        <div class="wizard-header">
            <h1 class="wizard-title">
                <i class="fas fa-file-import me-2"></i>Importador SAFT
            </h1>
            <p class="wizard-subtitle">Importe seus documentos SAFT de forma rápida e segura</p>
        </div>

        <div class="wizard-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('saft.import.post') }}" method="POST" enctype="multipart/form-data" id="saftForm">
                @csrf

                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('saftFile').click();">
                    <input type="file" name="saft_file" id="saftFile" style="display: none;" accept=".xml" required>
                    <div class="upload-icon">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <h3 class="upload-text" id="uploadText">Arraste seu arquivo SAFT ou clique para selecionar</h3>
                    <p class="upload-hint">Arquivos .xml (sem limite de tamanho)</p>
                </div>

                <div class="import-options">
                    <h3 class="mb-3">Selecione os tipos de documentos para importar:</h3>
                    <div class="option-grid">
                        <label class="option-card" for="company">
                            <input type="checkbox" name="import_types[]" value="company" id="company" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <h4 class="option-title">Empresa</h4>
                            <p class="option-description">Dados da empresa do arquivo SAFT</p>
                        </label>

                        <label class="option-card" for="customers">
                            <input type="checkbox" name="import_types[]" value="customers" id="customers" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="option-title">Clientes</h4>
                            <p class="option-description">Informações dos clientes</p>
                        </label>

                        <div class="option-card" id="sales-card">
                            <input type="checkbox" name="import_types[]" value="sales" id="sales" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h4 class="option-title">Vendas</h4>
                            <p class="option-description">Faturas e documentos de venda</p>
                            <div class="sales-indicator mt-2" style="display: none; background-color: #b71c50; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; text-align: center;">
                                <i class="fas fa-check-circle me-1"></i> Selecione os tipos abaixo
                            </div>
                        </div>

                        <label class="option-card" for="purchases">
                            <input type="checkbox" name="import_types[]" value="purchases" id="purchases" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h4 class="option-title">Compras</h4>
                            <p class="option-description">Faturas de fornecedores</p>
                        </label>

                        <label class="option-card" for="working_docs">
                            <input type="checkbox" name="import_types[]" value="working_docs" id="working_docs" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h4 class="option-title">Documentos de Trabalho</h4>
                            <p class="option-description">Outros documentos comerciais</p>
                        </label>

                        <label class="option-card" for="payments">
                            <input type="checkbox" name="import_types[]" value="payments" id="payments" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h4 class="option-title">Pagamentos</h4>
                            <p class="option-description">Registros de pagamentos</p>
                        </label>
                    </div>

                </div>

                <!-- Tipos de Faturas de Venda -->
                <div class="invoice-types-section mt-4" id="invoiceTypesSection" style="border: 2px solid #f63b79; padding: 20px; border-radius: 10px; background-color: #eff6ff; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);">
                    <div class="alert alert-info mb-3" style="background-color: #fedbe8; border-color: #fd93aa; color: #c30510;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Selecione os tipos de faturas que deseja importar.</strong> Se nenhum tipo for selecionado, todos serão importados.
                    </div>
                    <h3 class="mb-3">Selecione os tipos de faturas de venda a importar:</h3>
                    <div class="option-grid">
                        <label class="option-card" for="invoice_type_FT">
                            <input type="checkbox" name="invoice_types[]" value="FT" id="invoice_type_FT" class="d-none" checked>
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">FT</h4>
                            <p class="option-description">Factura</p>
                        </label>

                        <label class="option-card" for="invoice_type_FR">
                            <input type="checkbox" name="invoice_types[]" value="FR" id="invoice_type_FR" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">FR</h4>
                            <p class="option-description">Factura/recibo</p>
                        </label>

                        <label class="option-card" for="invoice_type_GF">
                            <input type="checkbox" name="invoice_types[]" value="GF" id="invoice_type_GF" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">GF</h4>
                            <p class="option-description">Factura genérica</p>
                        </label>

                        <label class="option-card" for="invoice_type_FG">
                            <input type="checkbox" name="invoice_types[]" value="FG" id="invoice_type_FG" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">FG</h4>
                            <p class="option-description">Factura global</p>
                        </label>

                        <label class="option-card" for="invoice_type_AC">
                            <input type="checkbox" name="invoice_types[]" value="AC" id="invoice_type_AC" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">AC</h4>
                            <p class="option-description">Aviso de cobrança</p>
                        </label>

                        <label class="option-card" for="invoice_type_AR">
                            <input type="checkbox" name="invoice_types[]" value="AR" id="invoice_type_AR" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">AR</h4>
                            <p class="option-description">Aviso de cobrança/recibo</p>
                        </label>

                        <label class="option-card" for="invoice_type_ND">
                            <input type="checkbox" name="invoice_types[]" value="ND" id="invoice_type_ND" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">ND</h4>
                            <p class="option-description">Nota de débito</p>
                        </label>

                        <label class="option-card" for="invoice_type_NC">
                            <input type="checkbox" name="invoice_types[]" value="NC" id="invoice_type_NC" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">NC</h4>
                            <p class="option-description">Nota de crédito</p>
                        </label>

                        <label class="option-card" for="invoice_type_AF">
                            <input type="checkbox" name="invoice_types[]" value="AF" id="invoice_type_AF" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">AF</h4>
                            <p class="option-description">Factura/recibo (autofacturação)</p>
                        </label>

                        <label class="option-card" for="invoice_type_TV">
                            <input type="checkbox" name="invoice_types[]" value="TV" id="invoice_type_TV" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">TV</h4>
                            <p class="option-description">Talão de venda</p>
                        </label>

                        <!-- Sector Segurador -->
                        <label class="option-card" for="invoice_type_RP">
                            <input type="checkbox" name="invoice_types[]" value="RP" id="invoice_type_RP" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">RP</h4>
                            <p class="option-description">Prémio ou recibo de prémio</p>
                        </label>

                        <label class="option-card" for="invoice_type_RE">
                            <input type="checkbox" name="invoice_types[]" value="RE" id="invoice_type_RE" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">RE</h4>
                            <p class="option-description">Estorno ou recibo de estorno</p>
                        </label>

                        <label class="option-card" for="invoice_type_CS">
                            <input type="checkbox" name="invoice_types[]" value="CS" id="invoice_type_CS" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">CS</h4>
                            <p class="option-description">Imputação a co-seguradoras</p>
                        </label>

                        <label class="option-card" for="invoice_type_LD">
                            <input type="checkbox" name="invoice_types[]" value="LD" id="invoice_type_LD" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">LD</h4>
                            <p class="option-description">Imputação a co-seguradora líder</p>
                        </label>

                        <label class="option-card" for="invoice_type_RA">
                            <input type="checkbox" name="invoice_types[]" value="RA" id="invoice_type_RA" class="d-none">
                            <div class="option-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <h4 class="option-title">RA</h4>
                            <p class="option-description">Resseguro aceite</p>
                        </label>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-danger btn-lg" id="importButton">
                            <i class="fas fa-file-import me-2"></i>Iniciar Importação
                        </button>
                    </div>
                </div>
            </form>

            <div class="progress-section" id="progressSection">
                <div class="progress-header">
                    <h3 class="progress-title">Importando documentos...</h3>
                    <span class="progress-status" id="progressStatus">0%</span>
                </div>

                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                </div>

                <div class="document-list" id="documentList"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    console.log('Script carregado');

    // Elementos principais
    const form = $('#saftForm');
    const uploadZone = $('#uploadZone');
    const fileInput = $('#saftFile');
    const uploadText = $('#uploadText');
    const progressSection = $('#progressSection');
    const progressBar = $('#progressBar');
    const progressStatus = $('#progressStatus');
    const documentList = $('#documentList');

    // Esconde a seção de tipos de faturas inicialmente
    $('#invoiceTypesSection').hide();

    // Evento de clique no card de vendas
    $('#sales-card').on('click', function() {
        const checkbox = $('#sales');
        checkbox.prop('checked', !checkbox.prop('checked'));

        if (checkbox.prop('checked')) {
            $(this).addClass('selected');
            $('#invoiceTypesSection').slideDown(300);
            $('.sales-indicator').show();
            console.log('Vendas selecionado, mostrando tipos de faturas');
        } else {
            $(this).removeClass('selected');
            $('#invoiceTypesSection').slideUp(300);
            $('.sales-indicator').hide();
            console.log('Vendas desmarcado, escondendo tipos de faturas');
        }
    });

    // Verifica se vendas já está selecionado
    if ($('#sales').prop('checked')) {
        $('#sales-card').addClass('selected');
        $('#invoiceTypesSection').show();
        $('.sales-indicator').show();
        console.log('Vendas já está selecionado inicialmente');
    }

    // Manipulação dos outros cards de opção
    $('.option-card:not(#sales-card)').on('click', function() {
        const checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked'));

        if (checkbox.prop('checked')) {
            $(this).addClass('selected');
        } else {
            $(this).removeClass('selected');
        }
    });

    // Manipulação dos cards de tipos de faturas
    $('.invoice-types-section .option-card').on('click', function() {
        const checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked'));

        if (checkbox.prop('checked')) {
            $(this).addClass('selected');
        } else {
            $(this).removeClass('selected');
        }
    });

    // Inicializa o estado visual dos cards
    $('.option-card').each(function() {
        const checkbox = $(this).find('input[type="checkbox"]');
        if (checkbox.prop('checked')) {
            $(this).addClass('selected');
        }
    });

    // Botão para selecionar todos os tipos de faturas
    $('<button>', {
        type: 'button',
        class: 'btn btn-sm btn-outline-danger mb-3',
        text: 'Selecionar Todos',
        click: function() {
            $('.invoice-types-section .option-card').each(function() {
                $(this).addClass('selected');
                $(this).find('input[type="checkbox"]').prop('checked', true);
            });
        }
    }).prependTo('#invoiceTypesSection');

    // Botão para limpar seleção de tipos de faturas
    $('<button>', {
        type: 'button',
        class: 'btn btn-sm btn-outline-secondary mb-3 ms-2',
        text: 'Limpar Seleção',
        click: function() {
            $('.invoice-types-section .option-card').each(function() {
                $(this).removeClass('selected');
                $(this).find('input[type="checkbox"]').prop('checked', false);
            });
        }
    }).prependTo('#invoiceTypesSection');

    // Upload de arquivo - Eventos de arrastar e soltar
    $(document).on('dragenter dragover', '#uploadZone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });

    $(document).on('dragleave drop', '#uploadZone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });

    $(document).on('drop', '#uploadZone', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const dt = e.originalEvent.dataTransfer;
        const files = dt.files;

        if (files && files[0]) {
            $('#saftFile').prop('files', files);
            $('#uploadText').text(files[0].name);
            $(this).addClass('has-file');
        }
    });

    // Evento de mudança no input de arquivo
    $(document).on('change', '#saftFile', function() {
        if (this.files && this.files[0]) {
            $('#uploadText').text(this.files[0].name);
            $('#uploadZone').addClass('has-file');
        }
    });





    // Submissão do formulário
    form.on('submit', async function(e) {
        e.preventDefault();

        const checkedOptions = $(this).find('input[name="import_types[]"]:checked');
        if (checkedOptions.length === 0) {
            alert('Por favor, selecione pelo menos um tipo de documento para importar.');
            return;
        }

        // Verifica se a opção de vendas está selecionada e se pelo menos um tipo de fatura está selecionado
        if ($('#sales').prop('checked')) {
            const checkedInvoiceTypes = $(this).find('input[name="invoice_types[]"]:checked');
            if (checkedInvoiceTypes.length === 0) {
                alert('Por favor, selecione pelo menos um tipo de fatura para importar.');
                return;
            }
            console.log('Tipos de faturas selecionados:', checkedInvoiceTypes.map(function() { return $(this).val(); }).get());
        }

        // Mostra a seção de progresso
        progressSection.addClass('active');
        $(this).find('button[type="submit"]').prop('disabled', true);

        try {
            const formData = new FormData(this);
            const response = await fetch($(this).attr('action'), {
                method: 'POST',
                body: formData
            });

            // Verifica o status da resposta
            if (!response.ok) {
                // Se o status não for 2xx, verifica se é um erro de validação (422)
                if (response.status === 422) {
                    const validationResult = await response.json();
                    console.error('Erro de validação:', validationResult);

                    // Formata as mensagens de erro de validação
                    let errorMessage = 'Erro de validação:<br>';
                    if (validationResult.errors) {
                        for (const field in validationResult.errors) {
                            errorMessage += `<strong>${field}</strong>: ${validationResult.errors[field].join(', ')}<br>`;
                        }
                    } else {
                        errorMessage += validationResult.message || 'Dados inválidos';
                    }

                    throw new Error(errorMessage);
                } else {
                    // Para outros erros, tenta obter o texto da resposta
                    const responseText = await response.text();
                    console.error(`Erro ${response.status}:`, responseText);

                    // Tenta analisar como JSON, se possível
                    try {
                        const errorJson = JSON.parse(responseText);
                        throw new Error(errorJson.message || `Erro ${response.status}: ${response.statusText}`);
                    } catch (e) {
                        // Se não for JSON, exibe o status HTTP
                        throw new Error(`Erro ${response.status}: ${response.statusText}`);
                    }
                }
            }

            // Se chegou aqui, a resposta está OK
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Se não for JSON, exibe o texto da resposta como erro
                const responseText = await response.text();
                console.error('Resposta não é JSON:', responseText);
                throw new Error('O servidor retornou uma resposta inválida. Verifique os logs para mais detalhes.');
            }

            const result = await response.json();
            console.log('Resposta do servidor:', result);

            if (result.success) {
                updateProgress(100);
                showSuccess('Importação concluída com sucesso!');

                // Atualiza o resumo dos documentos importados
                if (result.results) {
                    // Limpa a lista de documentos antes de adicionar novos
                    documentList.empty();

                    // Adiciona cada tipo de documento importado à lista
                    Object.entries(result.results).forEach(([type, value]) => {
                        // Garantir que o valor seja um número válido
                        let count = 0;
                        if (typeof value === 'number') {
                            count = value;
                        } else if (typeof value === 'object' && value !== null && 'count' in value) {
                            count = value.count;
                        } else if (typeof value === 'string' && !isNaN(value)) {
                            count = parseInt(value);
                        }

                        // Mapeia os tipos para nomes mais amigáveis
                        const typeNames = {
                            'company': 'Empresa',
                            'customers': 'Clientes',
                            'sales': 'Vendas',
                            'purchases': 'Compras',
                            'working_docs': 'Documentos de Trabalho',
                            'payments': 'Pagamentos'
                        };

                        const typeName = typeNames[type] || type;

                        // Adiciona o item à lista
                        const item = $('<div>', {
                            class: 'document-item',
                            html: `
                                <span>${typeName}: ${count} registo(s)</span>
                                <span class="status success">Concluído</span>
                            `
                        });
                        documentList.append(item);
                    });
                }

                // Adiciona botão para nova importação
                const resetButton = $('<button>', {
                    class: 'btn btn-outline-danger mt-4 w-100',
                    html: '<i class="fas fa-sync-alt me-2"></i>Iniciar Nova Importação',
                    click: function() {
                        resetForm();
                    }
                });
                progressSection.append(resetButton);
            } else {
                throw new Error(result.message || 'Erro desconhecido na importação');
            }
        } catch (error) {
            console.error('Erro completo:', error);
            showError('Erro na importação: ' + error.message);
            progressSection.removeClass('active');
            $(this).find('button[type="submit"]').prop('disabled', false);
        }
    });

    function updateProgress(percentage) {
        progressBar.css('width', `${percentage}%`);
        progressStatus.text(`${percentage}%`);
    }

    function showSuccess(message) {
        const alert = $('<div>', {
            class: 'alert alert-success mt-3',
            html: `<i class="fas fa-check-circle me-2"></i>${message}`
        });
        progressSection.append(alert);
    }

    function resetForm() {
        // Resetar o formulário
        form[0].reset();

        // Limpar o nome do arquivo
        $('#uploadText').text('Arraste e solte seu arquivo SAFT aqui ou clique para selecionar');
        $('#uploadZone').removeClass('has-file');

        // Esconder a seção de tipos de faturas
        $('#invoiceTypesSection').hide();

        // Limpar as seleções
        $('.option-card').removeClass('selected');

        // Resetar a barra de progresso
        updateProgress(0);

        // Remover alertas e botão de reset
        progressSection.find('.alert, button').remove();

        // Esconder a seção de progresso
        progressSection.removeClass('active');

        // Habilitar o botão de envio
        form.find('button[type="submit"]').prop('disabled', false);

        // Rolar para o topo do formulário
        $('html, body').animate({
            scrollTop: form.offset().top - 100
        }, 500);
    }

    function showError(message) {
        const alert = $('<div>', {
            class: 'alert alert-danger mt-3',
            html: `<i class="fas fa-exclamation-circle me-2"></i>${message}`
        });
        progressSection.append(alert);

        // Rola a página para o erro
        $('html, body').animate({
            scrollTop: alert.offset().top - 100
        }, 500);
    }
});
</script>
@endpush
