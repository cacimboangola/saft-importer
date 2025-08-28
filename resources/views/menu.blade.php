<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .filter-button {
            background-color: #2d2d2d;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .filter-button:hover {
            background-color: #3d3d3d;
        }
        
        .filter-button.active {
            background-color: #dc3545;
        }
        
        .search-input {
            background-color: #2d2d2d;
            border: 1px solid #3d3d3d;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            width: 100%;
        }
        
        .search-input::placeholder {
            color: #808080;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #4d4d4d;
            background-color: #2d2d2d;
            color: #fff;
            box-shadow: none;
        }

        .menu-card {
            background-color: #2d2d2d;
            border: none;
            transition: transform 0.3s ease;
            color: #fff;
        }

        .menu-card:hover {
            transform: translateY(-2px);
            background-color: #333333;
        }

        .badge-featured {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-favorite {
            background-color: #ffc107;
            color: #000;
        }

        .price-tag {
            color: #dc3545;
            font-weight: bold;
        }

        .card-subtitle {
            color: #808080;
        }

        .filter-container {
            background-color: #2d2d2d;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .dropdown-menu {
            background-color: #2d2d2d;
            border: 1px solid #3d3d3d;
        }

        .dropdown-item {
            color: #fff;
        }

        .dropdown-item:hover {
            background-color: #3d3d3d;
            color: #fff;
        }

        .pagination .page-link {
            background-color: #2d2d2d;
            border-color: #3d3d3d;
            color: #fff;
        }

        .pagination .page-link:hover {
            background-color: #3d3d3d;
            border-color: #4d4d4d;
            color: #fff;
        }

        .pagination .active .page-link {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Filtros -->
        <div class="filter-container">
            <!-- Primeiro grupo de filtros -->
            <div class="filter-group">
                <button class="filter-button active">Todos</button>
                <button class="filter-button">Parceiro</button>
                <div class="dropdown">
                    <button class="filter-button dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Mais opções
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Opção 1</a></li>
                        <li><a class="dropdown-item" href="#">Opção 2</a></li>
                        <li><a class="dropdown-item" href="#">Opção 3</a></li>
                    </ul>
                </div>
            </div>

            <!-- Segundo grupo de filtros -->
            <div class="filter-group">
                <button class="filter-button active">Todas</button>
                <button class="filter-button">Activas</button>
                <button class="filter-button">Por renovar</button>
            </div>

            <!-- Barra de pesquisa -->
            <div class="mt-3">
                <input type="text" class="search-input form-control" placeholder="Pesquise por nome, data ou nif...">
            </div>
        </div>

        <!-- Grid do Menu -->
        <div class="row g-4">

            <!-- Item do Menu 3 -->
            @foreach ($collection as $item)
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card menu-card h-100">
                        <div class="card-body">
                            <div class="d-flex gap-3">
                                <img src="/api/placeholder/100/100" class="rounded" alt="Hamburger">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="card-title mb-1">Hamburger</h5>
                                            <p class="card-subtitle mb-2">Simples</p>
                                        </div>
                                        <span class="price-tag">4000.00 ks</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-5">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Anterior</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Próxima</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
