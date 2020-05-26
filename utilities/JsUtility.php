<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;


use yii\base\BaseObject;

/**
 * Class JsUtility
 * @package open20\amos\core\utilities
 */
class JsUtility extends BaseObject
{
    /**
     * @param string $gridId
     * @param string $url
     * @param string $searchPostName
     * @return string
     */
    public static function getSearchM2mFirstGridJs($gridId, $url, $searchPostName)
    {

        $js = <<<JS
        
        function searchM2mFirstGrid$searchPostName(){
            var textToSearch = $("#search-$gridId").val();
            $.ajax({
                url: '$url',
                async: true,
                type: 'POST',
                data: {
                    $searchPostName:  textToSearch,
                    searchName : '$searchPostName'
                },
               success: function(response) {
                  $('#$gridId').html(response);
               }
            }); 
        }
        
        $('#$gridId').on("click", "#search-btn-$gridId", function(e) {    
            e.preventDefault(); 
            searchM2mFirstGrid$searchPostName();
            return false;
        });

         $('#$gridId').on("click", "#reset-search-btn-$gridId", function(e) {    
             e.preventDefault(); 
             $("#search-$gridId").val('');
             searchM2mFirstGrid$searchPostName();
             return false;
        });

        $('#$gridId').on("keypress", "#search-$gridId", function(e) {
            if(e.which == 13) {
                e.preventDefault();
                searchM2mFirstGrid$searchPostName();
                return false;
            }
        });
         
         $('body').on("click", "#$gridId-first .pagination li a", function(e) {
            e.preventDefault();
            var textToSearch = $("#search-$gridId").val();
            var data = {
                $searchPostName:  textToSearch,
                searchName: $("#search-$gridId").val()
            };
            var urlPag = $(this).attr('href');
            var pageParams = urlPag.substring(urlPag.indexOf('&'));
            $.pjax({
                type: 'POST',
                url: '$url'+pageParams,
                container: '#$gridId',
                replace: false,
                push: false,
                data: data
            });
            return false;
        });
         
          // used to sort via ajax without loosing the search
         $('body').on("click", "#$gridId-first thead th a", function(e) {
            e.preventDefault();
                        
            var urlOrigin = window.location.origin;
            var urlOrder = new URL(urlOrigin + $(this).attr('href'));
            var sort = urlOrder.searchParams.get("sort");
            var textToSearch = $("#search-$gridId").val();
            var sortParam = '';
            if('$url'.indexOf("&")){
                sortParam = '&sort='+sort
            }
            else {
                 sortParam = '?sort='+sort
            }
            $.ajax({
                url: '$url'+ sortParam,
                async: true,
                type: 'POST',
                data: {
                    $searchPostName:  textToSearch,
                    searchName : '$searchPostName',
                },
               success: function(response) {
                  $('#$gridId').html(response);
               }
            }); 
        });
JS;
        return $js;
    }

    /**
     * @param string $gridId
     * @param string $urlTo
     * @return string
     */
    public static function getM2mAssociateBtnModal($gridId, $urlTo)
    {
        $parts = parse_url($urlTo);
        parse_str($parts['query'], $params);
        $paramsJson = json_encode($params);

        $js = <<<JS
            $('#$gridId').on("click", "#$gridId-btn-associate", function(e) {
                 e.preventDefault(); 
                 $.ajax({
                      url: '$urlTo',
                      async: true,
                      type: 'POST',
                      data: $paramsJson,
                      success: function(response) {
                          $('#$gridId-modal-container').html(response);
                          $('#$gridId-modal').modal('show');
                      }
                 }); 
                 return false;
            });
JS;

        return $js;
    }

    /**
     * @param string $gridId
     * @param string $postName
     * @param string $postKey
     * @return string
     */
    public static function getM2mModalSave($gridId, $postName, $postKey)
    {
        $js = <<<JS
                $('#$gridId-modal').on("click", ".save-modal", function(e) {
                    e.preventDefault(); 
                    // var inputName = '$postName'+'['+'$postKey'+'][]';
                    var inputName = 'selected[]';
                    var selected = $("[name='"+inputName+"']"); 
                    var selection = []; 
                    $.each (selected, function(key, value){
                        selection.push(value.value);
                    });
                    var hrefValue = $(this).attr('href');
                    $.ajax({
                        url: hrefValue,
                        async: true,
                        type: 'POST',
                        data: {
                            save: 1,
                            selected: selection
                        },
                        success: function(response) {
                            $('#$gridId-modal').modal('hide');
                            $('.modal-backdrop').remove();
                            $("#reset-search-btn-$gridId").click();
                        }
                    });
                    return false;
                });
                
                $('#$gridId-modal').on("click", ".pagination li a", function(e) {
                    e.preventDefault();
                    // var inputName = '$postName'+'['+'$postKey'+'][]';
                    var inputName = 'selected[]';
                    var selected = $("[name='"+inputName+"']"); 
                    var selection = []; 
                    var searchValue = $('#community-members-grid-association-search-field').val();
                    $.each (selected, function(key, value){
                        selection.push(value.value);
                    });
                    $.ajax({
                        url: $(this).attr('href'),
                        async: true,
                        type: 'POST',
                        data: {
                            selected: selection,
                            genericSearch : searchValue,
                            save: 0
                        },
                        success: function(response) {
                            $('#$gridId-modal-container').html(response);
                            //restore checked values from post
                            var inputs = $('.hiddenInputContainer').find('input');
                            var inputArray = [];
                            if(inputs.length){
                                $.each(inputs, function(index, input){
                                    inputArray.push(input.value);
                                });
                            }
                            $.each (selection, function(key, id) {
                                if($.inArray(id, inputArray) == -1) {
                                    $('.hiddenInputContainer').append('<input type="hidden" name="'+inputName+'"value="'+id+'">');
                                }
                            });
                            
                            $('#$gridId-modal').css("display", "block");
                            $('#$gridId-modal').addClass('in');
                            $('.modal-backdrop').remove();
                            $('#$gridId-modal').modal('show');
                        }
                    }); 
                    return false;
                });
JS;
        return $js;
    }

    /**
     * @param string $gridId
     * @param string $postName
     * @param string $postKey
     * @param bool $useCheckbox
     * @return string
     */
    public static function getM2mSecondGridPagination($gridId, $postName, $postKey, $useCheckbox = true)
    {
        if ($useCheckbox) {
            $js = <<<JS
                $('body').on("click", ".pagination li a", function(e) {
                    e.preventDefault();
                    // var inputName = '$postName'+'['+'$postKey'+'][]';
                    var inputName = 'selected[]';
                    var selected = $("[name='"+inputName+"']"); 
                    var genericSearch = $("[name='genericSearch']").val();
                    var selection = []; 
                    $.each (selected, function(key, value){
                        selection.push(value.value);
                    });
                    $.ajax({
                        url: $(this).attr('href'),
                        async: true,
                        type: 'POST',
                         data: {
                           selected: selection,
                           genericSearch: genericSearch,
                           save: 0
                        },
                       success: function(response) {
                            $('.form-container').html(response);
                            // $('m2mwidget-from-generic-search-hiddeninput').val(1);
                            //restore checked values from post
                            var inputs = $('.hiddenInputContainer').find('input');
                            var inputArray = [];
                            if(inputs.length){
                                $.each(inputs, function(index, input){
                                    inputArray.push(input.value);
                                } );
                            }
                            $.each (selection, function(key, id){
                               if($.inArray(id, inputArray) == -1){
                                  $('.hiddenInputContainer').append('<input type="hidden" name="'+inputName+'"value="'+id+'">');
                               }
                            });
                       }
                    }); 
                    return false;
                    
                });
JS;
        } else {
            $js = <<<JS
                $('body').on("click", ".pagination li a", function(e) {
                    e.preventDefault();
                    var data = {
                        genericSearch: $("#$gridId-search-field").val()
                    };
                    $.ajax({
                        url: $(this).attr('href'),
                        async: true,
                        type: 'POST',
                        data: data,
                        success: function(response) {
                            $('.form-container').html(response);
                            $('m2mwidget-from-generic-search-hiddeninput').val(1);
                        }
                    });
                    return false;
                });
JS;
        }

        return $js;
    }

    /**
     * @param string $gridId
     * @param string $postName
     * @param string $postKey
     * @param bool $isModal
     * @param bool $useCheckbox
     * @return string
     */
    public static function getM2mSecondGridSearch($gridId, $postName, $postKey, $isModal = true, $useCheckbox = true)
    {

        if (!$isModal) {

            $js = <<<JS
                $('body').on("click", "#$gridId-search-btn", function(e) {    
                    e.preventDefault();
                    searchM2m();
                    return false;
                 
                });

                $('body').on("click", "#$gridId-reset-search-btn", function(e) {    
                    e.preventDefault();
                    $("#$gridId-search-field").val('');
                    searchM2m();
                    return false;
                 
                });
                $('body').on("keypress", "#$gridId-search-field", function(e) {
                    if(e.which == 13) {
                        e.preventDefault();
                        searchM2m();
                        return false;
                    }
                });
JS;
            if ($useCheckbox) {
                $jsSearch = <<<JS
                function searchM2m(){
                    // var inputName = '$postName'+'['+'$postKey'+'][]';
                    var inputName = 'selected[]';
                    var selected = $("[name='"+inputName+"']"); 
                    var selection = []; 
                    $.each (selected, function(key, value){
                        selection.push(value.value);
                    });
                    var data = {
                        genericSearch: $("#$gridId-search-field").val(),
                        selected: selection,
                        save: 0
                    };
                    var urlTo = window.location.href;
                    $.ajax({
                        url: urlTo,
                        async: true,
                        type: 'POST',
                        data: data,
                       success: function(response) {
                            $('.form-container').html(response);
                            $('m2mwidget-from-generic-search-hiddeninput').val(1);
                            //restore checked values from post
                            var inputs = $('.hiddenInputContainer').find('input');
                            var inputArray = [];
                            if(inputs.length){
                                $.each(inputs, function(index, input){
                                    inputArray.push(input.value);
                                } );
                            }
                            $.each (selection, function(key, id){
                               if($.inArray(id, inputArray) == -1){
                                  $('.hiddenInputContainer').append('<input type="hidden" name="'+inputName+'"value="'+id+'">');
                               }
                            });
                       }
                    }); 
                }
JS;
            } else {
                $jsSearch = <<<JS
                function searchM2m(){
                    var data = {
                        genericSearch: $("#$gridId-search-field").val()
                    };
                    var urlTo = window.location.href;
                    $.ajax({
                        url: urlTo,
                        async: true,
                        type: 'POST',
                        data: data,
                       success: function(response) {
                            $('.form-container').html(response);
                            $('m2mwidget-from-generic-search-hiddeninput').val(1);
                       }
                    }); 
                }
JS;
            }
            $js .= $jsSearch;

        } else {

            $modalId = str_replace('association', 'modal', $gridId);
            $js = <<<JS
                $('#$modalId').on("click", "#$gridId-search-btn", function(e) {    
                    e.preventDefault();
                    searchM2mModal();
                    return false;
                 
                });

                $('#$modalId').on("click", "#$gridId-reset-search-btn", function(e) {    
                    e.preventDefault();
                    $("#$gridId-search-field").val('');
                    searchM2mModal();
                    return false;
                 
                });
               
                $('#$modalId').on("keypress", "#$gridId-search-field", function(e) {
                    if(e.which == 13) {
                         e.preventDefault();
                        searchM2mModal();
                        return false;
                    }
                });
                function searchM2mModal(){
                    // var inputName = '$postName'+'['+'$postKey'+'][]';
                    var inputName = 'selected[]';
                    var selected = $("[name='"+inputName+"']"); 
                    var selection = []; 
                    $.each (selected, function(key, value){
                        selection.push(value.value);
                    });
                    var data = {
                        genericSearch: $("#$gridId-search-field").val(),
                        selected: selection,
                        save: 0
                    };
                    var urlTo = $("#$modalId").find('.save-modal').attr('href');
                    $.ajax({
                        url: urlTo+'&viewM2MWidgetGenericSearch=1',
                        async: true,
                        type: 'POST',
                        data: data,
                       success: function(response) {
                            $('#$modalId-container').html(response);
                            //restore checked values from post
                            var inputs = $('.hiddenInputContainer').find('input');
                            var inputArray = [];
                            if(inputs.length){
                                $.each(inputs, function(index, input){
                                    inputArray.push(input.value);
                                } );
                            }
                            $.each (selection, function(key, id){
                               if($.inArray(id, inputArray) == -1){
                                  $('.hiddenInputContainer').append('<input type="hidden" name="'+inputName+'"value="'+id+'">');
                               }
                            });
                            $('#$modalId').css("display", "block");
                            $('#$modalId').addClass('in');
                            $('.modal-backdrop').remove();
                            $('#$modalId').modal('show');
                       }
                    }); 
                }
JS;
        }
        return $js;
    }
}
