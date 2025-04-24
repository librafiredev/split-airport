const tenderSingleModal = {
    init: function () {
        if (typeof window.populateAndOpenModal != 'undefined') {
            return;
        }



        window.populateAndOpenModal = function (data) {
            var modalWrap = $('.tenders-modal-wrapper').eq(0);
            var modal = modalWrap.find('.tenders-modal').eq(0);
            modal.empty();

            var closeArea = $('<div class="custom-modal-close-area"></div>');

            var closeBtn = $('<div class="custom-modal-close-btn-wrap"><button type="button" class="custom-modal-close-btn"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.75 5.25L5.25 18.75" stroke="#505050" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.75 18.75L5.25 5.25" stroke="#505050" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>');
            modal.append(closeBtn);

            var dates = $('<div class="tender-modal-dates"></div>');
            dates.text(data.acf.full_date || '');
            modal.append(dates);

            var title = $('<h3 class="tender-modal-title"></h3>');
            title.text(data.post.title || '');
            modal.append(title);

            var content = $('<div class="tender-modal-content"></div>');
            content.html((data.post.content || '').replace(/\r?\n/g, "<br />"));
            modal.append(content);

            var documents = $('<div class="tender-modal-documents"></div>');
            documents.html((data.acf.documents || ''));
            modal.append(documents);

            closeArea.on('click', function () {
                modalWrap.removeClass('open');
                closeArea.remove();
            });
            closeBtn.on('click', function () {
                modalWrap.removeClass('open');
                closeArea.remove();
            });

            modalWrap.prepend(closeArea);

            modalWrap.addClass('open');
        }
    }
}

export default tenderSingleModal;