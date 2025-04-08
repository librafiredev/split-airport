import search from "../../assets/components/search";

$(function () {
    const supportedAirlinesWrapper = $(".airlines__supported");
    const unsupportedAirlinesWrapper = $(".airlines__unsupported");
    const loader = $(".loader");

    const request = async (term) => {
        loader.show();

        try {
            const formData = new FormData();
            formData.append("term", term);
            formData.append("nonce", theme.nonce);
            formData.append("action", "airlines_search");
            const request = await fetch(theme.ajaxUrl, {
                method: "POST",
                body: formData,
            });

            const response = await request.json();

            loader.hide();

            if (response?.success === true) {
                supportedAirlinesWrapper.html(
                    response?.data?.supported_airlines
                );
                unsupportedAirlinesWrapper.html(
                    response?.data?.unsupported_airlines
                );
            }
        } catch (e) {
            console.error(e.message);
        }
    };

    search(request);


    $('.airlines-mobile-sidebar-btn').on('click', function () {
        $('.airlines__sidebar').toggleClass('open');
    });

    $('.airlines-mobile-sidebar-close-btn').on('click', function () {
        $('.airlines__sidebar').removeClass('open');
    });
});
