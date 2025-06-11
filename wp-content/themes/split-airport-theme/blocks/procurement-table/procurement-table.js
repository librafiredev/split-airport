$(function () {
    $(".request-doc-modal-btn").on("click", function () {
        const modalId = $(this).data("modal-id");
        $("#" + modalId).addClass("open");
    });

    $(".custom-modal-close-btn, .custom-modal-close-area").on(
        "click",
        function () {
            $(this).closest(".custom-modal-wrapper").removeClass("open");
        }
    );
});
