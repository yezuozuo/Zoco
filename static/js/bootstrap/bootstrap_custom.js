$("document").ready(function () {
    $(".dropdown-menu li a").mousedown(function () {
        var dropdown = $(this).parents('.dropdown');
        var link = dropdown.children(':first-child');
        link.css('background-color', "#2E3436");
        link.css('color', 'white');
    });
    $('.carousel').carousel({
        interval: 4000
    });
});
