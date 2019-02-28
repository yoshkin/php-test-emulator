$(function() {
    $('#myTab').on('click', function (e) {
       if ($(this).attr('id', 'history-tab')) {
           getResultsHistory()
       }
    });

    $('form').submit(function() {
        var form = $(this);
        var button = form.find('[type="submit"]'),
            range_min = form.find('input[name="min"]'),
            range_max = form.find('input[name="max"]'),
            notification = form.parent().find('.alert');

        button.addClass('disabled');

        var params = {
            min: range_min.val(),
            max: range_max.val()
        };
        $.post(form.attr('action'), params)
            .done(function(data) {
            var response = JSON.parse(data);
            if (response.success === true) {
                range_min.val(response.data.range.min);
                range_max.val(response.data.range.max);
                if (response.data.questions) {
                    notification.removeClass('alert-danger').hide();
                    questionsTable(response.data.questions);
                } else {
                    notification.removeClass('alert-danger')
                        .addClass('alert-success')
                        .show()
                        .text(response.data.message);
                }
            } else {
                notification.removeClass('alert-success')
                    .addClass('alert-danger')
                    .show()
                    .text(response.data);
            }
        }).fail(function() {
            alert('Ошибка: сервер недоступен!');
        }).always(function() {
            button.removeClass('disabled');
            return false;
        });
        return false;
    });
});

function questionsTable(res) {
    var resultTable = $('#result-table');
    resultTable.html(res)
}

function getResultsHistory() {
    var tableTab = $('#history');
    $.get('/ajax/results-history', function(res) {
        tableTab.html(res);
    });
}