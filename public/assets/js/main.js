var click = true
$(document).ready(function () {
    var tooltipCus = $('[data-bs-toggle="tooltip"]');
    tooltipCus.tooltip('disable');
});

window.onload = () => {
    var x = window.matchMedia("(max-width: 1024px)")
    if (x.matches) {
        localStorage.removeItem('sidebarMenu', 'false');
        $('.menuicn').click(function () {
            $('aside').toggleClass('sidebarClose');
            $('.d-none-add').addClass('displayNone');
            $('.sidebarOverlay').removeClass('d-none');
            $(".menuList li").removeAttr("aria-expanded");
            $(".collapseWeb").toggleClass("d-none");
        });
        $('.asideLeft').removeClass('asideLeft');
        $('.collapseMenu').removeClass('collapseWeb');

        $('.sidebarOverlay').click(function () {
            $('.sidebarOverlay').addClass('d-none');
            $('aside').removeClass('sidebarClose');
        })
    } else {

        $('.menuicn').click(function () {
            if (!click) {
                $("aside").removeClass("sidebarClose");
                $('body').removeClass("asideOpen");
                $('.sidebarOverlay').removeClass('d-none');
                $(".collapseWeb").removeClass("d-none");
                $("aside").removeClass("removeTransition");
                $('.pl284').css("transition", "all .3s")
                localStorage.removeItem('sidebarMenu', 'false');
            } else {
                $("aside").addClass("sidebarClose");
                $('body').addClass("asideOpen");
                $('.d-none-add').addClass('displayNone');
                $(".collapseWeb").addClass("d-none");
                $("aside").removeClass("removeTransition");
                $('.pl284').css("transition", "all .3s")
                localStorage.setItem('sidebarMenu', 'false')
            }
            click = !click;
        });

        if (localStorage.getItem('sidebarMenu') == 'false') {
            $("aside").addClass("sidebarClose");
            $("aside").addClass("removeTransition");
            $('.d-none-add').addClass('displayNone');
            $('body').addClass("asideOpen");
            click = false;
        }

        $('.asideLeft').mouseover(function () {
            $('.asideLeft').removeClass('sidebarClose');
            $("aside").removeClass("removeTransition");
            $(".collapseWeb").removeClass("d-none");
        })

        $('.asideLeft').mouseout(function () {
            if ($('body').hasClass('asideOpen')) {
                $('body').addClass("asideOpen");
                $('.asideLeft').addClass('sidebarClose');
            }
        })
    }
}