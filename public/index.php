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
                    <form id="contact-form" novalidate>
                        <div class="mb-3">
                            <label for="contact-nombre" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="contact-nombre" name="nombre"
                                   maxlength="100" autocomplete="name" required
                                   aria-describedby="contact-nombre-error">
                            <div class="invalid-feedback" id="contact-nombre-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="contact-email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="contact-email" name="email"
                                   maxlength="255" autocomplete="email" required
                                   aria-describedby="contact-email-error">
                            <div class="invalid-feedback" id="contact-email-error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="contact-telefono" class="form-label">
                                Teléfono <span class="text-body-secondary small">(opcional)</span>
                            </label>
                            <input type="tel" class="form-control" id="contact-telefono" name="telefono"
                                   inputmode="tel" autocomplete="tel"
                                   placeholder="097680102" aria-describedby="contact-telefono-error">
                            <div class="invalid-feedback" id="contact-telefono-error"></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary" id="contact-submit">
                                <span class="spinner-border spinner-border-sm me-2 d-none"
                                      id="contact-submit-spinner" aria-hidden="true"></span>Guardar
                            </button>
                            <div class="small" id="contact-form-status" role="status" aria-live="polite"></div>
                        </div>
                    </form>
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
