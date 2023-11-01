$(function () {
    $("table").wrap(function () {
        var ctab_obj = $(this);
        if (ctab_obj.parent('div').hasClass('no-overflow')) {
            // Todo
        } else {
            return "<div class='no-overflow'></div>";
        }

    });

    /* Lea 2016/03 - in student mode, the main course page doesn't have a sidebar,
     * so stretch the main part all across */
    $(".role-student.path-course #region-bs-main-and-post").removeClass('span9').addClass('span12');
    $(".role-student.path-course #region-main").removeClass('span8').addClass('span12');

});