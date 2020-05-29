var Correlazioni = {};
Correlazioni.gestisciSelezione = function (element, postName, postKey, singleSelection) {
    if ((singleSelection === undefined) || (singleSelection === null)) {
        singleSelection = false;
    }

    //recupera il contenitore degli input hidden
    var hiddenInputContainer = $(".hiddenInputContainer");

    // Trigger event before manage selection
    hiddenInputContainer.trigger('before_manage_selection');

    //recupera l'oggetto selezionato
    var obj = $(element);

    //id dell'elemento selezionato
    var id = obj.val();

    //cerca l'input hidden con stesso valore e, se lo trova, lo elimina a prescindere
    var hiddenInput = hiddenInputContainer.find('[value="' + id + '"]');
    if (hiddenInput.length > 0) {
        hiddenInput.remove();
    }

    //se l'oggetto è selezionato, lo aggiunge
    if (obj.is(":checked")) {
        //crea l'input hidden
        var newHiddenInput = "<input type='hidden' name='selected[]' value='" + id + "'/>";

        if (singleSelection) {
            obj.parents('tr').siblings().find('input.m2m-target-checkbox:checked').trigger('click');
            hiddenInputContainer.empty();
        }

        //lo inserisce nel contenitore
        hiddenInputContainer.append(newHiddenInput);
    }

    // Trigger event after manage selection
    hiddenInputContainer.trigger('after_manage_selection');
};
