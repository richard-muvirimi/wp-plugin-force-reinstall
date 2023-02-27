(function ($) {
    "use strict";

    $(window).load(function () {
        //$(document).ready(function () {
        //setup

        $(".theme[data-slug]")
            //.filter(":not(:has(div.theme-id-container > h2 > span))") //is active theme
            .filter(":not(:has(div.update-message))") //only when not needing an update
            .each(function (index, target) {
                const element = $(window["force_reinstall"].button);

                //https://stackoverflow.com/a/39620299/5956589
                let url = new URL(element.attr("href"));
                url.searchParams.set("force-reinstall", $(target).data("slug"));

                element.attr("href", url.href);

                $(target).find(".theme-actions").prepend(element);
            });

        let obs = new MutationObserver(function () {
            $(".theme-wrap")
                .filter(":not(:has(a.force-reinstall))") //not already added
                .filter(
                    ":not(:has(div.theme-info div.notice a[href*='action=upgrade-theme']))" //target notice with upgrade theme notice
                )
                .each(function (index, target) {
                    const element = $(window["force_reinstall"].button);

                    const url = new URL(element.attr("href"));

                    //get id
                    //https://stackoverflow.com/a/39620299/5956589
                    let params = new URL(window.location).searchParams;

                    params.forEach(function (key, value) {
                        url.searchParams.set(key, value);
                    });

                    url.searchParams.set("force-reinstall", params.get("theme"));

                    element.attr("href", url.href);

                    $(target).find(".theme-actions > div").prepend(element);
                });
        });
        obs.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: false,
            characterData: false,
        });
    });
})(jQuery);
