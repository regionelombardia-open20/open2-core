/** Javascript for global actions **/

var FormActions = {};

FormActions.commonValidateForm = function (form_obj, messages) {
    //remove existing alert tab icons
    if (form_obj.find(".errore-alert").length > 0) {
        form_obj.find(".errore-alert").remove();
    }

    //cycling the elements
    $.each(messages, function (element, message) {
        //retrieves the element: it search by id (or name if id not present)
        var element_obj = form_obj.find("#" + element.replace(/(:|\.|\[|\]|,|=|@)/g, "\\$1"));
        if (!element_obj || element_obj.length == 0) {
            element_obj = form_obj.find('[name="' + element + '"]');
        }

        //if it found the element
        if (element_obj && element_obj.length > 0) {
            //get the tab father
            var tab_obj = element_obj.parents(".tab-pane").first();
            if (tab_obj && tab_obj.length > 0) {
                //tab father id
                var id_tab = tab_obj.attr("id");

                //retrieves the tab "button"
                var tab_a = $(".nav-tabs").find('li a[href="#' + id_tab + '"]');
                if (tab_a && tab_a.length > 0) {
                    //check if the error icon is already present
                    var hasError = (tab_a.find(".errore-alert").length > 0 ? true : false);
                    if (!hasError && message.length > 0) {
                        //get the the prototype icon
                        var icon_common = $("#errore-alert-common");
                        if (icon_common && icon_common.length > 0) {
                            var icon_clone = icon_common.clone();
                            icon_clone.removeClass("hidden");
                            icon_clone.removeAttr("id");

                            //insert the error icon
                            tab_a.append(icon_clone);
                        }
                    }
                }
            }
        }
    });
};
FormActions.afterValidate = function () {
    var form_obj = $("form");

    //afterValidate of ActiveForm
    form_obj.on('afterValidate', function (event, messages, errorAttributes) {
        FormActions.commonMessagesSubmit(messages);
    });

    //afterValidate of a field
    form_obj.on('afterValidateAttribute', function (event, attribute, messages) {
        //get the element
        var attributeObj = $("#" + attribute.id);

        //get the field container
        var field_container = attributeObj.parents(".required").first();

        //get the error container
        var error_container = field_container.find(".tooltip-error-field").first();

        //if contains the tooltip
        if (error_container.find('[data-toggle="tooltip"]').length > 0) {
            error_container.html("<span class='help-block help-block-error'></span>");
        }
    });

    //init for the submit errors
    FormActions.commonMessagesSubmit({});
};

FormActions.commonMessagesSubmit = function (messages) {
    var form_obj = $("form");
    var messages_submit = FormActions.getMessagesSubmit(messages);
    FormActions.commonValidateForm(form_obj, messages_submit);
};

FormActions.getMessagesSubmit = function (messages) {
    if (!messages) {
        messages = {};
    }
    //gets the fields with error from the validation
    $.each($(".has-error"), function (index, row) {
        row = $(row);
        var element = row.find('[name]');
        if (element) {
            //compile the message log
            var alert = row.find(".tooltip-error-field").find("span").first();
            var element_index = (element.attr("id") ? element.attr("id") : element.attr("name"));

            if (alert.data("original-title") || alert.attr('title')) {
                messages[element_index] = [alert.data("original-title") || alert.attr('title')];
            }
        }
    });

    return messages;
};

var Introduzione = {};
var IntroduzioneDashboard = {};
var IntroSlideshow = {};

Introduzione.tour_var = null;
Introduzione.cookie_name = "introduzioni";
Introduzione.init = function (steps) {
    Introduzione.tour_var = new Tour({
        orphan: true,
        onShown: function (tour) {
            var tourObj = $(".tour-tour");
            var navigation = tourObj.find(".popover-navigation");

            navigation.find("[data-role='prev']").html("&laquo; Indietro");
            navigation.find("[data-role='next']").html("Avanti &raquo;");
            navigation.find("[data-role='end']").html("Termina");
        },
        steps: steps
    }).init();
};
Introduzione.show = function () {
    Introduzione.tour_var.goTo(0);
    Introduzione.tour_var.restart();
};
Introduzione.setIntroShow = function (tipo_introduzione) {
    var cookie_val = Cookie.getCookie(Introduzione.cookie_name);
    if (!cookie_val) {
        cookie_val = {};
    }
    else {
        cookie_val = JSON.parse(cookie_val);
    }

    cookie_val[tipo_introduzione] = true;

    Cookie.setCookie(Introduzione.cookie_name, JSON.stringify(cookie_val), 365 * 10);
};
Introduzione.isIntroShow = function (tipo_introduzione) {
    var cookie_val = Cookie.getCookie(Introduzione.cookie_name);

    if (!cookie_val) {
        return false;
    }
    else {
        cookie_val = JSON.parse(cookie_val);

        return (cookie_val[tipo_introduzione] && (cookie_val[tipo_introduzione] == true || cookie_val[tipo_introduzione] == "true") ? true : false);
    }
};

var Cookie = {};
Cookie.setCookie = function (cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
};
Cookie.getCookie = function (cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1);
        if (c.indexOf(name) == 0)
            return c.substring(name.length, c.length);
    }
    return "";
};

//SMOOTH SCROLL TO ANCHOR
var smoothScrollTo = function (anchor) {
    var duration = 1000;                     //time (milliseconds) it takes to reach anchor point
    var targetY = $(anchor).length ? $(anchor).offset().top : 0;
    $("html, body").animate({
        "scrollTop": targetY - 50
    }, duration, 'easeInOutCubic');
}

$(document).ready(function () {
    //==for smoothScroll
    var hashURL = location.hash;
    var tab_element = $('.tab-content');

    if ((hashURL != "" && hashURL.length > 1) || (tab_element.length > 1)) {
        $(window).scrollTop(0);
        $('html').css({display: 'block'});
        smoothScrollTo(hashURL);
    } else {
        $('html').css({display: 'block'});
    }
    //== ==//

    if (self == top && top.location != location) {
        top.location.href = document.location.href;
    }
    $(function () {
        window.prettyPrint && prettyPrint();
        $('.nav-tabs:first').tabdrop();
        $('.nav-tabs:last').tabdrop({text: 'More options'});
        $('.nav-pills').tabdrop({text: 'With pills'});
    });

    //SHOW HIDE ELEMENT BY BTN -- if already active
    if ($('.show-hide-element').hasClass('active')) {
        var elementToToggle = $('.show-hide-element.active').data('toggle-element');

        $('.element-to-toggle').each(function () {
            $(this).removeClass("toggleIn");
            if ($(this).data('toggle-element') == elementToToggle) {
                $(this).addClass("toggleIn");
            }
        });
    }

    //SHOW HIDE ELEMENT BY BTN
    $('.show-hide-element').click(function () {
        var $elementToToggle = $(this).data('toggle-element');
        var $notThisBtn = $('.show-hide-element').not($(this));
        // var $notThisElement = $('.element-to-toggle').not($elementToToggle);

        $notThisBtn.each(function () {
            $(this).removeClass("active");
        });
        $(this).toggleClass('active');

        $('.element-to-toggle').each(function () {
            if ($(this).data('toggle-element') == $elementToToggle) {
                $(this).toggleClass("toggleIn");
            } else {
                $(this).removeClass("toggleIn");
            }
        });

    });

    $(".graphic-widget-refresh-btn").click(function (event) {
        $.pjax.defaults.timeout = 5000;
        var toRefreshElement = $(this).data('btnrefresh');
        $.pjax.reload({container: '#' + toRefreshElement});
    });

    //MODAL - focus on first input
    $('.modal').on('shown.bs.modal', function () {
        if ($(this).find('input')) {
            $(this).find('input:text:visible:first').focus();
        }
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {

        localStorage.setItem('lastTab', $(this).attr('href'));
    });

    // go to the latest tab, if it exists:
    if ($('.nav.nav-tabs').length) {
        var lastTab = localStorage.getItem('lastTab');
        if (lastTab) {
            var currentUrl = window.location.href;
            var prevUrl = document.referrer;
            if (currentUrl.split(/[?#]/)[0] == prevUrl.split(/[?#]/)[0]) {
                var issetTabActive = yii.getQueryParams(window.location.href).hasOwnProperty("tabActive");
                var issetAnchor = window.location.hash.substr(1) != '';
                if (!issetTabActive && !issetAnchor) {
                    $('[href="' + lastTab + '"]').tab('show');
                }
            }
        }
    }

    /* To initialize BS3 tooltips set this below */
    $(function () {
        $.widget.bridge('uitooltip', $.ui.tooltip);
        $("[data-toggle='tooltip']").tooltip({
            content: function () {
                return $(this).prop('title');
            }
        });
    });
    /* To initialize BS3 popovers set this below */
    $(function () {
        $("[data-toggle='popover']").popover();
    });
    $("[data-toggle='tooltip']").on('show.bs.tooltip', function () {
        // Only one tooltip should ever be open at a time
        $('.tooltip').not(this).hide();
    });

    FormActions.commonMessagesSubmit({});
});

//OPEN SLIDESHOW-MODAL
$('.open-slideshow-modal').on('click', function () {
    $("#amos-slideshow").modal("show");
});

//check if the select in a modal is initialized, to avoid keep-loading bug
function checkSelect2Init(modalId, selectId) {
    if ($("#" + modalId).find(".select2").length == 0) {
        var $el = $("#" + selectId);
        if ($el.length) {
            var settings = $el.attr('data-krajee-select2');
            settings = window[settings];
            $el.select2(settings);

            $("#" + modalId).find(".kv-plugin-loading").remove();
        }
    }
}

//on dropdown selection disables the text field and viceversa.
//If it is required to entry one of the two, given a hidden input field, it will be evaluated with 1 in case requirement is satisfied
// (in order to put hidden field in model 'require' rules to resolve the exclusive dependency)
function enableDisableSelectAndTextFields(select, textField, requiredField) {
    if (typeof requiredField == "undefined") {
        requiredField = '';
    }
    if (select.length && select.val() && select.val().length != 0) {
        textField.prop('disabled', true);
        select.prop('disabled', false);
        if (requiredField.length != 0) {
            requiredField.val('1');
        }
    } else if (textField.val().length != 0) {
        select.prop('disabled', true);
        textField.prop('disabled', false);
        if (requiredField.length != 0) {
            requiredField.val('1');
        }
    } else {
        select.prop('disabled', false);
        textField.prop('disabled', false);
        if (requiredField.length != 0) {
            requiredField.val('');
        }
    }
}

//in a form if a flag value is true, a dependent section is shown
//give the section to display/hide the same html selector of flag input field followed by '-section' suffix.
function showHideSectionFlag(flagSelector) {
    var sectionFlag = $(flagSelector + '-section');
    if ($(flagSelector).val() === '1') {
        if (sectionFlag.hasClass('hidden')) {
            sectionFlag.removeClass('hidden');
        }
    } else {
        if (!sectionFlag.hasClass('hidden')) {
            sectionFlag.addClass('hidden');
        }
    }
}
