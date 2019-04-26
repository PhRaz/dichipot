var $ = require('jquery');

module.exports = function () {

    var addUserForm = function ($collectionHolder, $addUserButton) {
        let prototype = $collectionHolder.data('prototype');
        let index = $collectionHolder.data('index');
        let newForm = prototype;

        newForm = newForm.replace(/__name__/g, index);
        $collectionHolder.data('index', index + 1);

        $newForm = $(newForm);
        $addUserButton.before($newForm);
        addUserFormDeleteButton($newForm, index);
    };

    var addUserFormDeleteButton = function ($form, index) {
        var $removeUserButton = $('#remove' + index);
        $removeUserButton.on('click', function (e) {
            $form.remove();
        });
    };

    let $collectionHolder;
    let $addUserButton = $('<div class="form-group row"><div class="col-12"><button type="button" class="btn btn-block btn-primary shadow-sm">Ajouter un participant</button></div></div>');

    $collectionHolder = $('.userEvents');
    $collectionHolder.append($addUserButton);
    $collectionHolder.data('index', $collectionHolder.find('.user').length);

    $addUserButton.on('click', function (e) {
        addUserForm($collectionHolder, $addUserButton);
    });

    $('.user').each(function () {
        addUserFormDeleteButton($(this));
    })
};
