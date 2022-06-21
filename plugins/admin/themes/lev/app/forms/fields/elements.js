import $ from 'jquery';

$(document).on('change', '[data-lev-elements] select', (event) => {
  const target = $(event.currentTarget);
  const value = target.val();
  const id = target.closest('[data-lev-elements]').data('levElements');

  $(`[id^="${id}_"]`).css('display', 'none');
  $(`[id="${id}__${value}"]`).css('display', 'inherit');
});

$('[data-lev-elements] select').trigger('change');
