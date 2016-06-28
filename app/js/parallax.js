$(document).ready(function () {
    $('#layer-1').mousemove(function (e) {
        parallax(e, this, 1);
        parallax(e, document.getElementById('layer-2'), 4);
        parallax(e, document.getElementById('layer-3'), 8);
        parallax(e, document.getElementById('layer-4'), 12);
    });
});

function parallax(e, target, layer) {
    var layer_coeff = 100 / layer;
    var x = ($(window).width() - target.offsetWidth) / 2 - (e.pageX - ($(window).width() / 2)) / layer_coeff;
    var y = ($(window).height() - target.offsetHeight) / 2 - (e.pageY - ($(window).height() / 2)) / layer_coeff;
    $(target).offset({ top: y ,left : x });
};