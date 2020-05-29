


var TabsActions = {};
TabsActions.launchListenTab = function () {
    //open the last tab (if present)
    TabsActions.openTab();

    $("ul.nav-tabs > li > a").on("shown.bs.tab", function (e) {
        var tabid = $(e.target).attr("href");
        TabsActions.setOpenTab(tabid);
    });
};
TabsActions.openTab = function () {
    var tabid = window.location.hash;
    if (tabid) {
        $('a[href="' + tabid + '"]').tab('show');
    }
};
TabsActions.setOpenTab = function (tabid) {
    if( $("form").length) {
        //form action
        var actionForm = $("form").attr("action");

        //split form action
        var actionFormTemp = actionForm.split("#");

        //update the action form with the new tab
        var newAction = actionFormTemp[0] + tabid;
        $("form").attr("action", newAction);
    }
};

$(document).ready(function () {

    //lancia la funzione di ascolto per memorizzare l'ultima tab aperta
    TabsActions.launchListenTab();
});