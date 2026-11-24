(function ($) {
  'use strict';

  const API_LISTAR = 'api/listar.php';
  const MSG_CARGANDO = 'Cargando contactos…';
  const MSG_VACIO = 'Todavía no hay contactos.';
  const MSG_ERROR = 'No se pudo cargar el listado. Intentá de nuevo más tarde.';

  const API_CREAR = 'api/crear.php';
  const CAMPOS = ['nombre', 'email', 'telefono'];
  const MSG_GUARDADO = 'Contacto guardado.';
  const MSG_REVISAR = 'Revisá los campos marcados.';
  const MSG_ERROR_ALTA = 'No se pudo guardar el contacto. Revisá tu conexión e intentá de nuevo.';

  // Mismas reglas que src/Contacto/ContactoValidator.php (task.md §3.1). El servidor es la autoridad.
  const PATRON_NOMBRE = /^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ]+(?: [A-Za-zÁÉÍÓÚÜáéíóúüÑñ]+)*$/;
  const PATRON_EMAIL = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/;
  const PATRON_TELEFONO = /^09\d{7}$/;
  const MSG_TELEFONO = 'El teléfono debe tener 9 dígitos y empezar con 09, sin espacios ni guiones.';

  // normalizar() recorta el valor; error() devuelve el mensaje del primer fallo o '' si es válido.
  const REGLAS = {
    nombre: {
      normalizar: function (valor) {
        return valor.trim();
      },
      error: function (valor) {
        if (valor === '') {
          return 'El nombre es obligatorio.';
        }
        if (valor.length > 100) {
          return 'El nombre no puede superar los 100 caracteres.';
        }
        if (!PATRON_NOMBRE.test(valor)) {
          return 'El nombre solo puede contener letras y espacios.';
        }
        return '';
      },
    },
    email: {
      normalizar: function (valor) {
        return valor.trim().toLowerCase();
      },
      error: function (valor) {
        if (valor === '') {
          return 'El email es obligatorio.';
        }
        if (valor.length > 255) {
          return 'El email no puede superar los 255 caracteres.';
        }
        if (!PATRON_EMAIL.test(valor)) {
          return 'El email no tiene un formato válido.';
        }
        return '';
      },
    },
    telefono: {
      normalizar: function (valor) {
        return valor.trim();
      },
      error: function (valor) {
        // El teléfono es opcional: vacío es válido.
        return valor === '' || PATRON_TELEFONO.test(valor) ? '' : MSG_TELEFONO;
      },
    },
  };

  let $form;
  let $submit;
  let $submitSpinner;
  let $formStatus;
  let enviando = false;
  let marcarNuevo = false;

  const state = { page: 1, totalPages: 1 };
  let requestId = 0;
  let $list;
  let $status;
  let $pagination;
  let $info;
  let $prev;
  let $next;
  let $count;

  // La base guarda created_at en UTC; se muestra en hora de Uruguay (UTC-3, sin horario de verano).
  const FORMATO_FECHA = new Intl.DateTimeFormat('es-UY', {
    timeZone: 'America/Montevideo',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hourCycle: 'h23',
  });

  // "2026-11-23 19:40:50" (UTC) -> "23/11/2026 16:40". El valor llega del servidor y se pinta con .text().
  function formatFecha(sqlDate) {
    const fecha = new Date(sqlDate.replace(' ', 'T') + 'Z');
    if (Number.isNaN(fecha.getTime())) {
      return sqlDate;
    }
    const p = {};
    FORMATO_FECHA.formatToParts(fecha).forEach(function (parte) {
      p[parte.type] = parte.value;
    });
    return p.day + '/' + p.month + '/' + p.year + ' ' + p.hour + ':' + p.minute;
  }

  const COLORES_AVATAR = 5;
  const SKELETONS = 3;

  // "ana maría pérez" -> "AM". Se pinta con .text().
  function iniciales(nombre) {
    return nombre
      .split(' ')
      .slice(0, 2)
      .map(function (parte) {
        return parte.charAt(0);
      })
      .join('')
      .toUpperCase();
  }

  // El mismo nombre siempre recibe el mismo color.
  function colorAvatar(nombre) {
    let suma = 0;
    for (let i = 0; i < nombre.length; i++) {
      suma += nombre.charCodeAt(i);
    }
    return 'contact-avatar-c' + (suma % COLORES_AVATAR);
  }

  function renderContacto(contacto) {
    const sinTelefono = contacto.telefono === null;

    return $('<div>', { class: 'contact-item' }).append(
      $('<div>', { class: 'contact-avatar ' + colorAvatar(contacto.nombre), 'aria-hidden': 'true' }).text(
        iniciales(contacto.nombre)
      ),
      $('<div>', { class: 'contact-item-nombre' }).text(contacto.nombre),
      $('<div>', { class: 'contact-item-email' }).text(contacto.email),
      $('<div>', { class: 'contact-item-telefono' + (sinTelefono ? ' is-empty' : '') }).text(
        sinTelefono ? 'Sin teléfono' : contacto.telefono
      ),
      $('<div>', { class: 'contact-item-fecha' }).text(formatFecha(contacto.created_at))
    );
  }

  function renderSkeletons() {
    for (let i = 0; i < SKELETONS; i++) {
      $list.append(
        $('<div>', { class: 'contact-item contact-skeleton', 'aria-hidden': 'true' }).append(
          $('<div>', { class: 'contact-avatar' }),
          $('<div>', { class: 'contact-skeleton-line contact-item-nombre' }),
          $('<div>', { class: 'contact-skeleton-line contact-item-email' })
        )
      );
    }
  }

  function setStatus(texto) {
    $status
      .text(texto || '')
      .toggleClass('is-loading', texto === MSG_CARGANDO)
      .toggleClass('is-empty', texto === MSG_VACIO)
      .toggleClass('is-error', texto === MSG_ERROR);
  }

  function updateCount(total) {
    $count.text(total === 1 ? '1 contacto' : total + ' contactos').toggleClass('d-none', total === 0);
  }

  function updatePagination() {
    $info.text('Página ' + state.page + ' de ' + state.totalPages);
    $prev.prop('disabled', state.page <= 1);
    $next.prop('disabled', state.page >= state.totalPages);
    $pagination.toggleClass('d-none', state.totalPages <= 1);
  }

  // Carga una página del listado. Si llega una respuesta vieja después de otra más nueva, se descarta.
  function load(page) {
    const id = ++requestId;

    setStatus(MSG_CARGANDO);
    $list.empty();
    renderSkeletons();

    $.getJSON(API_LISTAR, { page: page })
      .done(function (res) {
        if (id !== requestId) {
          return;
        }
        state.page = res.page;
        state.totalPages = res.totalPages;
        $list.empty();
        updateCount(res.total);

        if (res.data.length === 0) {
          setStatus(MSG_VACIO);
        } else {
          setStatus('');
          res.data.forEach(function (contacto) {
            $list.append(renderContacto(contacto));
          });
          if (marcarNuevo && res.page === 1) {
            $list.children().first().addClass('is-new');
          }
        }
        marcarNuevo = false;
        updatePagination();
      })
      .fail(function () {
        if (id !== requestId) {
          return;
        }
        $list.empty();
        setStatus(MSG_ERROR);
        $pagination.addClass('d-none');
      });
  }

  function $input(campo) {
    return $form.find('[name="' + campo + '"]');
  }

  function mostrarError(campo, mensaje) {
    const $campo = $input(campo);
    $campo.toggleClass('is-invalid', mensaje !== '');
    $campo.attr('aria-invalid', mensaje !== '' ? 'true' : 'false');
    $campo.toggleClass('is-valid', mensaje === '' && ($campo.val() || '').trim() !== '');
    $('#contact-' + campo + '-error').text(mensaje);
  }

  // tipo: 'error', 'ok' o undefined (neutro, por ejemplo mientras se envía).
  function setFormStatus(texto, tipo) {
    $formStatus
      .removeClass('is-error is-ok')
      .addClass(tipo === 'error' ? 'is-error' : tipo === 'ok' ? 'is-ok' : '')
      .text(texto);
  }

  // Valida un campo con sus reglas y muestra u oculta su error. Devuelve true si es válido.
  function validarCampo(campo) {
    const valor = REGLAS[campo].normalizar($input(campo).val() || '');
    const error = REGLAS[campo].error(valor);
    mostrarError(campo, error);
    return error === '';
  }

  // Valida todos los campos (sin cortar al primer error) y devuelve el primero con error, o null.
  function validarFormulario() {
    let primerError = null;
    CAMPOS.forEach(function (campo) {
      if (!validarCampo(campo) && primerError === null) {
        primerError = campo;
      }
    });
    return primerError;
  }

  // Pinta los errores que devuelve el servidor (422/409). No toca los valores de los campos.
  function pintarErrores(errores) {
    CAMPOS.forEach(function (campo) {
      mostrarError(campo, typeof errores[campo] === 'string' ? errores[campo] : '');
    });
  }

  function setEnviando(activo) {
    enviando = activo;
    $submit.prop('disabled', activo);
    $submitSpinner.toggleClass('d-none', !activo);
  }

  function enviar() {
    setEnviando(true);
    setFormStatus('Guardando…');

    $.ajax({ url: API_CREAR, method: 'POST', data: $form.serialize(), dataType: 'json' })
      .done(function () {
        $form[0].reset();
        CAMPOS.forEach(function (campo) {
          mostrarError(campo, '');
        });
        setFormStatus(MSG_GUARDADO, 'ok');
        marcarNuevo = true;
        window.ContactList.load(1);
      })
      .fail(function (xhr) {
        const errores = xhr.responseJSON && xhr.responseJSON.errors;
        if ((xhr.status === 422 || xhr.status === 409) && errores) {
          pintarErrores(errores);
          setFormStatus(MSG_REVISAR, 'error');
        } else {
          setFormStatus(MSG_ERROR_ALTA, 'error');
        }
      })
      .always(function () {
        setEnviando(false);
      });
  }

  function initFormulario() {
    $form = $('#contact-form');
    $submit = $('#contact-submit');
    $submitSpinner = $('#contact-submit-spinner');
    $formStatus = $('#contact-form-status');

    // Valida al salir de cada campo; mientras tiene error, también al corregirlo.
    $form.on('focusout', 'input', function () {
      validarCampo($(this).attr('name'));
    });
    $form.on('input', 'input', function () {
      const campo = $(this).attr('name');
      if ($(this).hasClass('is-invalid')) {
        validarCampo(campo);
      }
    });

    $form.on('submit', function (e) {
      e.preventDefault();
      if (enviando) {
        return;
      }
      const primerError = validarFormulario();
      if (primerError !== null) {
        setFormStatus(MSG_REVISAR, 'error');
        $input(primerError)[0].focus();
        return;
      }
      enviar();
    });
  }

  $(function () {
    $list = $('#contact-list');
    $status = $('#contact-list-status');
    $pagination = $('#contact-pagination');
    $info = $('#contact-page-info');
    $prev = $('#contact-prev');
    $next = $('#contact-next');
    $count = $('#contact-count');

    $prev.on('click', function () {
      load(state.page - 1);
    });
    $next.on('click', function () {
      load(state.page + 1);
    });

    // Expuesto para que el formulario de alta recargue el listado sin refrescar la página.
    window.ContactList = {
      load: load,
      reload: function () {
        load(state.page);
      },
    };

    initFormulario();
    load(1);
  });
})(jQuery);
