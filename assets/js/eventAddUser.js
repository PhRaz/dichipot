var $ = require('jquery');

module.exports = function () {

    const $addUserButton = $('<div class="form-group row"><div class="col-12"><button id="add_user" type="button" class="btn btn-block btn-primary shadow-sm"><i class="fas fa-user-plus"></i></button></div></div>');

    /**
     * Add event handler on remove button.
     *
     * @param $form
     * @param index
     */
    const addUserFormDeleteButton = function ($form, index) {
        var $removeUserButton = $('#remove' + index);
        $removeUserButton.on('click', function (e) {
            $form.remove();
            /*
             * enable the 'add participant' button
             */
            $('#add_user')
                .addClass('btn-primary shadow-sm')
                .removeClass('disabled btn-secondary')
                .unbind('click')
                .on('click', function (e) {
                    addParticipant();
                })
                .find('> p')
                .remove();
        });
    };

    /**
     * Display a form to add a new participant
     *
     * @param $collectionHolder
     * @param $addUserButton
     */
    const addUserForm = function ($collectionHolder, $addUserButton) {
        let prototype = $collectionHolder.data('prototype');
        let index = $collectionHolder.data('index');
        let newForm = prototype;
        newForm = newForm.replace(/__name__/g, index);
        $collectionHolder.data('index', index + 1);
        $newForm = $(newForm);
        $addUserButton.before($newForm);
        addUserFormDeleteButton($newForm, index);
    };

    /**
     * Check if max nb user is reached and eventually disable the button.
     */
    const checkMaxNbUser = function () {
        /*
         * check user limit on number of participant
         */
        let nbParticipant = $('.user').length;
        if (nbParticipant >= maxNbParticipant) {
            /*
             * disable the 'add participant' button
             */
            $('#add_user')
                .unbind('click')
                .removeClass('btn-primary shadow-sm')
                .addClass('disabled btn-secondary')
                .append('<p>Vous avez atteint le nombre maximum de participants pour un événement.</p>')
        }
    };

    /**
     * Add form and manage max number of participants.
     */
    const addParticipant = function () {
        addUserForm($collectionHolder, $addUserButton);
        checkMaxNbUser();
    };

    let maxNbParticipant = 5;
    let $collectionHolder = $('.userEvents');

    $collectionHolder.append($addUserButton);
    $collectionHolder.data('index', $collectionHolder.find('.user').length);

    $('#add_user').on('click', function (e) {
        console.log("click ajout participant");
        addParticipant();
    });

    $('.user').each(function () {
        addUserFormDeleteButton($(this));
    });

    checkMaxNbUser();
};
