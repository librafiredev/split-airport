import tenderSingleModal from "../components/tenderSingleModal";

$(function () {

    $('[name="tender_year"]').on('change', function () {
        $('.tenders-year-from').submit();
    });

    tenderSingleModal.init();

});