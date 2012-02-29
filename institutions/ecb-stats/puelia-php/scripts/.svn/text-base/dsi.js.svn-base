$(document).ready(function(){

    $('table caption a').after(function(){
        var uri = $(this).attr('href');

        var uri_button ='<span class="uri">URI</span>';
        var html_frag = '<span class="uri-link">' +
//                            '<span class="left">&lt;</span>'+
                            uri_button +
 //                           '<span class="right">&gt;</span>'+
                        '</span>';
        var uri_link = $(html_frag);
        function show_uri(){
          $('.uri',this).html('<input type="url" value="'+uri+'" readonly size=35>').show();
          $(this).hide().show('1500');
          $('input', this).select();
          $(this).unbind('click');
        };
        uri_link.click(show_uri).focusout(function(){
          $('.uri', this).hide(900).html('URI').show(0);
          $(this).click(show_uri);
        });
        return uri_link;
    });

/* Add Search Box */
    
    var entity_type = $('tr.type:first td.value a').html();

    var queryString = {};
    window.location.href.replace(
        new RegExp("([^?=&]+)(=([^&]*))?", "g"),
        function($0, $1, $2, $3) { queryString[$1] = $3; }
    );

    if(queryString['type']){
      entity_type = queryString['type'];
    }
        

    if(entity_type){
      var hidden_type_input = '<input type="hidden" name="type" value="'+entity_type+'">';
      var name_of_thing_to_search = entity_type+'s';
    } else {
      var hidden_type_input = '';
      var name_of_thing_to_search='';
    }

    $('nav.topnav').prepend(
    '<section id="search-box"><form action="/search">'+
      '<fieldset>'+
        '<h1><label for="_search" title="Search">Search '+name_of_thing_to_search+'</label></h1><input id="_search" name="_search" type="text">'+
          hidden_type_input +
        '<input type="submit" value="Search">'+
      '</fieldset>'+
    '</form></section>'
    );

});
