<?php

declare(strict_types=1);

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">
    <title>RedEMC · Registro de contactos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          crossorigin="anonymous">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
    <div class="app-backdrop" aria-hidden="true"></div>

    <div class="container app-container py-4 py-lg-5">
        <div class="app-header mb-4 mb-lg-5">
            <div class="app-brand" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M19 8v6M22 11h-6"/>
                </svg>
            </div>
            <div>
                <p class="app-eyebrow mb-1">Prueba técnica RedEMC</p>
                <h1 class="app-title mb-1">Registro de contactos</h1>
                <p class="app-subtitle mb-0">Cargá un contacto y aparece al instante en el listado.</p>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-5">
                <div class="app-card app-card-form" id="contact-form-section">
                    <div class="app-card-header">
                        <div class="app-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="app-card-title">Ingreso de datos</h2>
                            <p class="app-card-subtitle">
                                Los campos con <span class="app-required">*</span> son obligatorios.
                            </p>
                        </div>
                    </div>

                    <form id="contact-form" novalidate>
                        <div class="app-field">
                            <label for="contact-nombre" class="form-label">
                                Nombre <span class="app-required" aria-hidden="true">*</span>
                            </label>
                            <div class="app-field-control">
                                <svg class="app-field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <input type="text" class="form-control" id="contact-nombre" name="nombre"
                                       maxlength="100" autocomplete="name" required
                                       placeholder="Ana Pérez" aria-describedby="contact-nombre-error">
                                <div class="invalid-feedback" id="contact-nombre-error"></div>
                            </div>
                        </div>
                        <div class="app-field">
                            <label for="contact-email" class="form-label">
                                Email <span class="app-required" aria-hidden="true">*</span>
                            </label>
                            <div class="app-field-control">
                                <svg class="app-field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="m22 7-10 6L2 7"/>
                                </svg>
                                <input type="email" class="form-control" id="contact-email" name="email"
                                       maxlength="255" autocomplete="email" required
                                       placeholder="ana@ejemplo.com" aria-describedby="contact-email-error">
                                <div class="invalid-feedback" id="contact-email-error"></div>
                            </div>
                        </div>
                        <div class="app-field">
                            <label for="contact-telefono" class="form-label">
                                Teléfono <span class="app-optional">(opcional)</span>
                            </label>
                            <div class="app-field-control">
                                <svg class="app-field-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" aria-hidden="true">
                                    <rect x="6" y="2" width="12" height="20" rx="2"/>
                                    <path d="M11 18h2"/>
                                </svg>
                                <input type="tel" class="form-control" id="contact-telefono" name="telefono"
                                       inputmode="tel" autocomplete="tel"
                                       placeholder="097680102" aria-describedby="contact-telefono-error">
                                <div class="invalid-feedback" id="contact-telefono-error"></div>
                            </div>
                        </div>
                        <div class="app-form-actions">
                            <button type="submit" class="btn btn-primary" id="contact-submit">
                                <span class="spinner-border spinner-border-sm d-none"
                                      id="contact-submit-spinner" aria-hidden="true"></span>
                                <span>Guardar contacto</span>
                            </button>
                            <div class="app-form-status" id="contact-form-status"
                                 role="status" aria-live="polite"></div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="app-card" id="contact-list-section">
                    <div class="app-card-header">
                        <div class="app-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                            </svg>
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="app-card-title">Listado de datos</h2>
                            <p class="app-card-subtitle">Los más recientes primero.</p>
                        </div>
                        <div class="app-count d-none" id="contact-count"></div>
                    </div>
                    <div id="contact-list-status" class="contact-list-status"
                         role="status" aria-live="polite"></div>
                    <div id="contact-list" class="contact-list"></div>
                    <div class="contact-pagination d-none" id="contact-pagination"
                         role="navigation" aria-label="Paginado del listado">
                        <button type="button" class="btn" id="contact-prev">
                            <span aria-hidden="true">‹</span> Anterior
                        </button>
                        <span id="contact-page-info"></span>
                        <button type="button" class="btn" id="contact-next">
                            Siguiente <span aria-hidden="true">›</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-footer">Hecho con PHP, MySQL, jQuery y Bootstrap.</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
    <script src="js/app.js"></script>
</body>
</html>
