(function ($) {
  'use strict';

  const API_LISTAR = 'api/listar.php';
  const MSG_CARGANDO = 'Cargando contactos…';
  const MSG_VACIO = 'Todavía no hay contactos.';
  const MSG_ERROR = 'No se pudo cargar el listado. Intentá de nuevo más tarde.';

  const state = { page: 1, totalPages: 1 };
  let requestId = 0;
  let $list;
  let $status;
  let $pagination;
  let $info;
  let $prev;
  let $next;

  // "2026-11-23 14:05:00" -> "23/11/2026 14:05". El valor llega del servidor y se pinta con .text().
  function formatFecha(sqlDate) {
    const m = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/.exec(sqlDate);
    return m ? m[3] + '/' + m[2] + '/' + m[1] + ' ' + m[4] + ':' + m[5] : sqlDate;
  }

  function renderContacto(contacto) {
    const telefono = contacto.telefono !== null ? contacto.telefono : '—';

    return $('<div>', { class: 'contact-item' }).append(
      $('<div>', { class: 'contact-item-nombre fw-semibold' }).text(contacto.nombre),
      $('<div>', { class: 'contact-item-email' }).text(contacto.email),
      $('<div>', { class: 'contact-item-telefono small' }).text(telefono),
      $('<div>', { class: 'contact-item-fecha small text-body-secondary' }).text(formatFecha(contacto.created_at))
    );
  }

  function setStatus(texto) {
    $status.text(texto || '');
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

    $.getJSON(API_LISTAR, { page: page })
      .done(function (res) {
        if (id !== requestId) {
          return;
        }
        state.page = res.page;
        state.totalPages = res.totalPages;

        if (res.data.length === 0) {
          setStatus(MSG_VACIO);
        } else {
          setStatus('');
          res.data.forEach(function (contacto) {
            $list.append(renderContacto(contacto));
          });
        }
        updatePagination();
      })
      .fail(function () {
        if (id !== requestId) {
          return;
        }
        setStatus(MSG_ERROR);
        $pagination.addClass('d-none');
      });
  }

  $(function () {
    $list = $('#contact-list');
    $status = $('#contact-list-status');
    $pagination = $('#contact-pagination');
    $info = $('#contact-page-info');
    $prev = $('#contact-prev');
    $next = $('#contact-next');

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

    load(1);
  });
})(jQuery);
