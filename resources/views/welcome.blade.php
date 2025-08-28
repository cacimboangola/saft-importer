<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Submissao</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">



    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(to right, #8E24AA, #b06ab3);
            color: #D7D7EF;
            font-family: 'Lato', sans-serif;
        }

        h2 {
            margin: 50px 0;
        }



        .file-drop-area {
            position: relative;
            display: flex;
            align-items: center;
            width: 450px;
            max-width: 100%;
            padding: 25px;
            border: 1px dashed rgba(255, 255, 255, 0.4);
            border-radius: 3px;
            transition: 0.2s;

        }

        .choose-file-button {
            flex-shrink: 0;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            padding: 8px 15px;
            margin-right: 10px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .file-message {
            font-size: small;
            font-weight: 300;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            cursor: pointer;
            opacity: 0;

        }

        .mt-100 {
            margin-top: 100px;
        }

    </style>
</head>

<body class="antialiased">
    <div class="container d-flex justify-content-center mt-100">
        <div class="row">
            <div class="col-md-12">

                <h2>Submeta o ficheiro SAFT</h2>
                <p class="lead">Ao submeter o ficheiro, estará disponibilizando os <br> documentos emitidos, na CLOUD</p>
                <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="file-drop-area">
                        <span class="choose-file-button">Selecionar o ficheiro</span>
                        <span class="file-message">ou arraste e largue os ficheiros aqui</span>
                        <input class="file-input" type="file" name="file" multiple>
                    </div>
                    <div class="d-flex flex-row-reverse">
                        <input type="submit" class="btn-lg btn-primary mt-3" value="Upload">
                    </div>
                </form>
                @if (Session::has('success'))
                    <div class="alert alert-success">
                        <strong>Sucesso!</strong> {{ Session::get('success') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).on('change', '.file-input', function() {


        var filesCount = $(this)[0].files.length;


        var textbox = $(this).prev();

        if (filesCount === 1) {
            var fileName = $(this).val().split('\\').pop();
            textbox.text(fileName);
        } else {
            textbox.text(filesCount + ' files selected');
        }
    });
</script>

</html>
