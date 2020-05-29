var test = 0;

$(document).ready(function () {

    //attiva l'evento di evidenza delle tab dopo il validate del form
    FormActions.afterValidate();

    /**
     * Remove form and back to last on undo
     */
    $('body').on('click', '.page-content .dynamicUndo', function () {
        //Current Form ID
        var fid = jQuery(this).data('fid');

        //Siblings of this fid
        var siblings = jQuery('#record_form .page-content:gt('+ fid +')');

        //If i have one or more items
        if(siblings.length) {
            //Remove unneeded
            siblings.remove();

            //Show last form
            var selectedForm = jQuery('.page-content[data-fid="' + fid + '"]');

            if(selectedForm.length) {
                selectedForm.show();
            } else {
                jQuery('#record_form :last-child.page-content').show();
            }
        }
    });

    /**
     * Remove form and back to last on undo
     */
    $('body').on('click', 'form.dynamicCreation .undo-edit', function () {
        //Current slide
        var current_slide = jQuery(this).parents('.page-content');

        //Remove current slide
        current_slide.remove();
        jQuery(':last-child.page-content', record_form).show();

        //Block all actions
        return false;
    });

    /**
     * Dynamic creation forms submit
     */
    $('body').on('beforeSubmit', 'form.dynamicCreation', function () {
        //Submitted form
        var form = $(this);

        //Reference entity
        var entity = form.data('entity');

        //Triggered select
        var select = jQuery('select.dynamicCreation[data-entity="'+entity+'"]');

        //All forms container
        var record_form = jQuery('#record_form');

        //Current slide
        var current_slide = form.parents('.page-content');

        // return false if form still have some validation errors
        if (form.find('.has-error').length) {
            return false;
        }

        // submit form
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                jQuery.each(select, function(){
                    var myId = jQuery(this).attr('id');
                    var elName = response[jQuery(this).data('field')];
                    var option = jQuery('<option />').text(elName).val(response.id);

                    //If started option set as selected
                    if(myId == form.data('field')) {
                        option.attr('selected','selected');
                    }

                    jQuery(this).append(option);
                    jQuery(this).trigger('change');
                });

                current_slide.remove();
                jQuery(':last-child.page-content', record_form).show();
            }
        });

        return false;
    });

    //avoid more than one click on submit btn (double creation fix)
    $('body').on('beforeValidate', 'form', function (e) {
        $(':input[type="submit"]', this).attr('disabled', 'disabled');
        $(':input[type="submit"]', this).each(function (i) {
            if ($(this).prop('tagName').toLowerCase() === 'input') {
                $(this).data('enabled-text', $(this).val());
                $(this).val($(this).data('disabled-text'));
            } else {
                $(this).data('enabled-text', $(this).html());
                $(this).html($(this).data('disabled-text'));
            }
        });
    }).on('afterValidate', 'form', function (e) {
        if ($(this).find('.has-error').length > 0) {
            $(':input[type="submit"]', this).removeAttr('disabled');
            $(':input[type="submit"]', this).each(function (i) {
                if ($(this).prop('tagName').toLowerCase() === 'input') {
                    $(this).val($(this).data('enabled-text'));
                } else {
                    $(this).html($(this).data('enabled-text'));
                }
            });
        }
    });

});

/**
 * Function per l'inserimento al volo da collegare alle varie input
 * @param newItemElement is the child of relation "Select2" editor
 */
var attachFastInsert = function(newItemElement) {
    //Firing element
    var that = jQuery(newItemElement);

    //All forms container
    var record_form = jQuery('#record_form');

    //Current form
    var current_form = that.parents('form');

    //Current page-content
    var current_content = current_form.parents('.page-content');

    //Reference editor block
    var refBlock = that.parent();

    //Current Select2 editor
    var current_select = jQuery('select', refBlock);

    //Hide all old forms
    jQuery('.page-content', record_form).hide();

    //New fid
    var new_fid = current_form.data('fid') + 1;

    //Create new form container
    var new_form = jQuery('<div/>').addClass('page-content').html('<div class="cssload-aim"></div>');

    //Insert fid to the slide
    new_form.attr('data-fid', new_fid);

    //Append new form
    record_form.append(new_form);

    //Build url params
    var params = {
        fid: new_fid,
        dataField: current_select.attr('id'),
        dataEntity: current_select.data('entity')
    };

    //Querystring
    var paramString = jQuery.param(params);

    //Module referenced
    var module = that.data('module');

    //Entity
    var entity = that.data('entity');

    /**
     * Load new form via ajax
     * When loaded put new breadcrumb ti new form with back items
     */
    new_form.load('/' + module + '/' + entity + '/create-ajax?' + paramString, {}, function() {
        //Get parent Bread
        var newBreadcrumb = jQuery('.breadcrumb_left', current_content).clone();

        //Get Last Element (description)
        var lastOldChild = jQuery('ul', newBreadcrumb).find('li:last-child');
        var lastOldDesc = lastOldChild.text();

        //Remove last old child
        lastOldChild.remove();

        //New oldStatus link
        var newLink = jQuery('<a/>').attr('class', 'dynamicUndo').attr('href', '#').attr('data-fid', new_fid - 1);

        //Text of the new link
        newLink.text(lastOldDesc);

        //New oldStatus is the back to: edit parent
        var newOldStatus = jQuery('<li/>').append(newLink)

        //Apend new old to breadcrumb
        jQuery('ul', newBreadcrumb).append(newOldStatus);

        //New description od BC  => deprecated on 29/05/2018
        //var newDescription = jQuery('<li/>').text('Crea ' + entity);
        //Append new items
        //jQuery('ul', newBreadcrumb).append(newDescription);

        //Prepend new Breadcrumb
        new_form.prepend(newBreadcrumb);
    });
}

var dynamicInsertOpening = function() {
    var currentInput = jQuery(this);
    var currentVisibleSelect = jQuery('#select2-' + currentInput.attr('id') + '-results').parents('.select2-dropdown');

    //Fid if exists the button
    var exists = jQuery('.addNew', currentVisibleSelect);

    //If exists go out
    if(exists.length || !currentInput.hasClass('canInsert')) {
        return true;
    }

    //Contenitore
    var addContainer = jQuery('<div/>').addClass('addNew').text('Aggiungi un nuovo elemento');

    //Bottone
    var newButton = jQuery('<button/>').addClass('btn btn-primary');

    //Append data to button
    newButton.attr('title','Aggiungi');
    newButton.attr('data-module', currentInput.data('module'));
    newButton.attr('data-entity', currentInput.data('entity'));
    newButton.on('click', function() {
        //Close dropdown
        currentInput.select2('close');

        //Load new form
        attachFastInsert(currentInput);
    });

    //Span inside with icon
    var spanIcon = jQuery('<span/>').text('Aggiungi');

    //Join elements
    newButton.append(spanIcon);
    addContainer.append(newButton);

    currentVisibleSelect.prepend(addContainer);
};