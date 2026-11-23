<?php

declare(strict_types=1);

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RedEMC · Registro de contactos</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          crossorigin="anonymous">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
    <div class="container py-4 py-lg-5">
        <div class="app-header mb-4">
            <h1 class="h3 mb-1">Registro de contactos</h1>
            <p class="text-body-secondary mb-0">Prueba técnica RedEMC</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="app-card" id="contact-form-section">
                    <h2 class="h5 mb-3">Ingreso de datos</h2>
                    <!-- El formulario se implementa como feature (ver task.md §3). -->
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="app-card" id="contact-list-section">
                    <h2 class="h5 mb-3">Listado de datos</h2>
                    <div id="contact-list-status" class="contact-list-status" role="status" aria-live="polite"></div>
                    <div id="contact-list" class="contact-list"></div>
                    <div class="contact-pagination d-none" id="contact-pagination"
                         role="navigation" aria-label="Paginado del listado">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                id="contact-prev">Anterior</button>
                        <span class="text-body-secondary small" id="contact-page-info"></span>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                id="contact-next">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
    <script src="js/app.js"></script>
</body>
</html>
