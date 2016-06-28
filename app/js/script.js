$(function() {
  $( ".slider-range" ).slider({
    range: "min",
    value: 1,
    min: 1,
    max: 3,
    slide: function( event, ui ) {
      $( ".state" ).val( ui.value );
    }
  });

});

$(".phone").mask("8 (999) 999-9999");

$(".modal").on("show.bs.modal", function(){
    var $bodyWidth = $("body").width();
    $("body").css({'overflow-y': "hidden"}).css({'padding-right': ($("body").width()-$bodyWidth)});
});

$(".modal").on("hidden.bs.modal", function(){
    $("body").css({'padding-right': "0", 'overflow-y': "auto"});
});
