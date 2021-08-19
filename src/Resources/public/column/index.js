function fireLog(e) {
  e.preventDefault();
  var url = $(e.currentTarget).data('href');
  $.get(url, function (data) {
    $('#log-modal .description').html($(data).find('#content'));
  }).done(function () {
    $('#log-modal').modal('show');
  })
}

function closeLog(e) {
  e.preventDefault();
  $('#log-modal').modal('hide');
}
