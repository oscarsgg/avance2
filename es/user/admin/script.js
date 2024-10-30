$(".menu > ul > li").click(function(e) {
    // Remove active class from already active siblings
    $(this).siblings().removeClass("active");
    // Add active class to the clicked item
    $(this).toggleClass("active");
    // Open submenu
    $(this).find("ul").slideToggle();
    // Open menu and slide up
    $(this).siblings().find("ul").slideUp();
    // remove active class of submenu

});

$(".menu-btn").click(function(){
    $(".sidebar").toggleClass("active");
})

$(document).click(function(e) {
    // Verifica si el clic fue fuera del sidebar y el menú
    if (!$(e.target).closest('.sidebar, .menu > ul > li').length) {
        // Si es fuera, desactiva los elementos del menú
        $(".menu > ul > li").removeClass("active");
        $(".menu > ul > li ul").slideUp(); // Cierra cualquier submenu
        
    }
});
