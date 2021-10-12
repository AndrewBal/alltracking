$(function() {

  $('button.track_list_item').on('click', function() {
    $('.track_list').toggleClass('hiden');
    $(this).text( $(this).text() == "ПОКАЗАТЬ ВСЕ" ? "Скрыть" : "ПОКАЗАТЬ ВСЕ");
  });


});


function toggleElem() {
   var sections = document.querySelectorAll('.hide-mobile');
   var tabContainer = document.querySelector('.uk-switcher-header');
    if (window.innerWidth < 768) {
        sections.forEach((section) => {
            document.body.append(section);
        })
    } else {

        sections.forEach((section) => {
            tabContainer.append(section)
        })
    }
}



document.addEventListener("DOMContentLoaded",  function () {




    $( ".add-info" ).click(function() {
        var titlePrnt = $(this).closest('.accordion-title')
        var liPrnt = $(this).closest('li')
        if (!$(titlePrnt).children('.track-title-container').hasClass("tracking")) {
            liPrnt.toggleClass('open')
        }
        titlePrnt.next().slideToggle( "fast", function() {
            // Animation complete.
        });
    });
},true);



//window.addEventListener('resize',  true);
