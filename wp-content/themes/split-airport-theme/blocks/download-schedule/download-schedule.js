import autoComplete from "@tarekraafat/autocomplete.js";
import flatpickr from "flatpickr";
import pdfMake from "pdfmake";
import "pdfmake/build/vfs_fonts";

pdfMake.vfs = window.pdfMake.vfs;

$(function () {
    function markElementWithError(element, message) {
        element.addClass("is-invalid");
        element.attr("data-error", message);
    }

    function clearErrors() {
        $(".js-download-schedule-filters").removeClass("dls-generic-error");

        $(".js-download-schedule-filters")
            .find(".dls-with-error")
            .removeClass("dls-with-error");

        $(".js-download-schedule-filters")
            .find(".is-invalid")
            .removeClass("is-invalid");
    }

    function setupDLSSelect(selector) {
        $(selector).each(function () {
            const $select = $(this);
            const placeholder = $select.attr("data-placeholder");
            const $wrapper = $select.closest(".labeled-field-wrapper");
            const $selectWrap = $select.closest(".dls-select-wrap");

            const data = [];
            $select.find("option").each(function () {
                const val = $(this).val();
                if (val) {
                    data.push({ label: $(this).text().trim(), value: val });
                }
            });

            $select.addClass("dls-hidden");

            const inputId = "dls-ac-" + $select.attr("name");
            const $input = $("<input>", {
                type: "text",
                id: inputId,
                class: "dls-ac-input",
                placeholder,
                autocomplete: "off",
            });
            const $clearBtn = $("<button>", {
                type: "button",
                class: "js-dls-ac-clear",
            })
                .text("×")
                .hide();

            $selectWrap.prepend($input);
            $selectWrap.append($clearBtn);

            const ac = new autoComplete({
                selector: `#${inputId}`,
                threshold: 0,
                data: {
                    src: data,
                    keys: ["label"],
                },
                resultsList: {
                    class: "dls-ac-results",
                    noResults: false,
                },
                resultItem: {
                    class: "dls-ac-result",
                    highlight: true,
                },
                events: {
                    input: {
                        selection(event) {
                            const selected = event.detail.selection.value;
                            $input.val(selected.label);
                            $select.val(selected.value);
                            $clearBtn.show();
                        },
                        focus() {
                            ac.start();
                        },
                    },
                },
            });

            $input.on("input", function () {
                $select.val("");
                $clearBtn.toggle(!!$(this).val());
            });

            $clearBtn.on("click", function () {
                $input.val("");
                $select.val("");
                $clearBtn.hide();
            });

            $wrapper.find(".js-select2-lbl").on("click", function (e) {
                e.preventDefault();
                $input.focus();
            });
        });
    }

    setupDLSSelect(".js-dls-destination-select");
    setupDLSSelect(".js-dls-carrier-select");

    function formatDate(isoString) {
        const d = new Date(isoString);

        if (isNaN(d.getTime())) {
            return "";
        }

        const day = String(d.getDate()).padStart(2, "0");
        const month = String(d.getMonth() + 1).padStart(2, "0"); // Remember: Months are 0-11
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, "0");
        const minutes = String(d.getMinutes()).padStart(2, "0");

        return `${day}.${month}.${year}   ${hours}:${minutes}`;
    }

    function getBase64Image(img) {
        const canvas = document.createElement("canvas");
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0);
        return canvas.toDataURL("image/png");
    }

    $(".js-download-pdf-schedule").on("click", function () {
        $(".download-schedule-wrapper").addClass("is-loading");

        // NOTE: small timeout to allow dom to show loading before browser gets overwhelmened by the export
        setTimeout(() => {
            generateFlightPDF(
                window.splitGlobalDLScheduleData.flights,
                window.splitGlobalDLScheduleData.filters,
            );
        }, 100);
    });

    $(".js-download-csv-schedule").on("click", function () {
        $(".download-schedule-wrapper").addClass("is-loading");

        // NOTE: small timeout to allow dom to show loading before browser gets overwhelmened by the export
        setTimeout(() => {
            exportFlightCSV(
                window.splitGlobalDLScheduleData.flights,
                window.splitGlobalDLScheduleData.filters,
            );
            $(".download-schedule-wrapper").removeClass("is-loading");
        }, 100);
    });

    async function generateFlightPDF(flightData, currentFilters) {
        const config = theme.downloadSchedule;
        const t = config.translations;

        const tableBody = [
            t.headers.map((text) => ({ text: text, style: "tableHeader" })),
        ];

        flightData.forEach((f) => {
            tableBody.push([
                { text: f.destination, style: "tableCell" },
                { text: f.date, style: "tableCell" },
                { text: f.time, style: "tableCell" },
                {
                    stack: [
                        { text: f.number, bold: true },
                        { text: f.carrier, fontSize: 9 },
                    ],
                    style: "tableCell",
                },
                { text: f.code || "-", style: "tableCell" },
            ]);
        });

        const docDefinition = {
            pageSize: "A4",
            pageMargins: [0, 0, 0, 0],
            content: [
                // Header with Logo
                {
                    fillColor: "#dde5ed",
                    table: {
                        widths: ["*"],
                        body: [
                            [
                                {
                                    image: getBase64Image(
                                        document.querySelector(
                                            ".site-branding-main-logo img",
                                        ),
                                    ),
                                    width: 100,
                                    margin: [40, 20, 0, 20],
                                },
                            ],
                        ],
                    },
                    layout: "noBorders",
                },
                // Main Content
                {
                    margin: [40, 20, 40, 0],
                    stack: [
                        { text: t.title, style: "header" },
                        // Filters
                        {
                            margin: [0, 10, 0, 20],
                            table: {
                                widths: ["*", "*", "*"],
                                body: [
                                    [
                                        {
                                            text: [
                                                {
                                                    text: t.from + " ",
                                                    bold: true,
                                                },
                                                currentFilters.from + "\n",
                                                {
                                                    text: t.to + " ",
                                                    bold: true,
                                                },
                                                currentFilters.to,
                                            ],
                                            fontSize: 10,
                                        },
                                        {
                                            text: [
                                                {
                                                    text: t.dest + "\n",
                                                    bold: true,
                                                },
                                                currentFilters.destination,
                                            ],
                                            fontSize: 10,
                                        },
                                        {
                                            text: [
                                                {
                                                    text: t.carrier + "\n",
                                                    bold: true,
                                                },
                                                currentFilters.carrier,
                                            ],
                                            fontSize: 10,
                                        },
                                    ],
                                ],
                            },
                            layout: "noBorders",
                        },
                        {
                            text: currentFilters.searchTime ? t.searchTime : "",
                            fontSize: 9,
                            color: "#666666",
                        },
                        {
                            text: currentFilters.searchTime
                                ? formatDate(currentFilters.searchTime)
                                : "",
                            fontSize: 10,
                            bold: true,
                            margin: [0, 0, 0, 20],
                        },
                        // Main Table
                        {
                            table: {
                                headerRows: 1,
                                widths: [100, 80, 80, "*", 60],
                                body: tableBody,
                            },
                            layout: {
                                hLineWidth: (i) => (i <= 1 ? 0 : 1),
                                vLineWidth: () => 0,
                                hLineColor: () => "#cccccc",
                                hLineStyle: () => ({
                                    dash: { length: 2, space: 2 },
                                }),
                                paddingTop: () => 10,
                                paddingBottom: () => 10,
                                fillColor: (i) => (i === 0 ? "#f6f6f6" : null),
                            },
                        },
                    ],
                },
            ],
            styles: {
                header: { fontSize: 22, bold: true, margin: [0, 0, 0, 10] },
                tableHeader: { bold: true, fontSize: 10, margin: [0, 5, 0, 5] },
                tableCell: { fontSize: 10 },
            },
        };

        try {
            await pdfMake
                .createPdf(docDefinition)
                .download("Flight_Schedule.pdf");
        } catch (error) {
            //
        }
        $(".download-schedule-wrapper").removeClass("is-loading");
    }

    function exportFlightCSV(flightData, currentFilters) {
        const config = theme.downloadSchedule;
        const t = config.translations;

        const metadata = [
            [t.title],
            [""], // Spacer row
            [
                `${t.from} ${currentFilters.from}`,
                "",
                t.dest || "",
                "",
                t.carrier || "",
            ],
            [
                `${t.to} ${currentFilters.to}`,
                "",
                currentFilters.destination || "",
                "",
                currentFilters.carrier || "",
            ],
            [""], // Spacer row
            [
                `${t.searchTime} ${
                    currentFilters.searchTime
                        ? formatDate(currentFilters.searchTime)
                        : ""
                }`,
            ],
            [""], // Spacer row
        ];

        const tableHeaders = t.headers;

        const tableRows = flightData.map((f) => [
            `"${f.destination}"`,
            `"${f.date}"`,
            `"${f.time}"`,
            `"${f.number} / ${f.carrier}"`,
            `"${f.code || "-"}"`,
        ]);

        const allRows = [
            ...metadata.map((row) => row.join(",")),
            tableHeaders.join(","),
            ...tableRows.map((row) => row.join(",")),
        ];

        const csvContent = "\uFEFF" + allRows.join("\n");
        const blob = new Blob([csvContent], {
            type: "text/csv;charset=utf-8;",
        });
        const url = URL.createObjectURL(blob);

        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", "Flight_Schedule.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    const format = "d.m.Y";
    document
        .querySelectorAll(".js-dl-schedule-date-range")
        .forEach((container) => {
            const fromInput = container.querySelector(".date-from");
            const toInput = container.querySelector(".date-to");
            const fromDisplay = container.querySelector(".date-from-display");
            const toDisplay = container.querySelector(".date-to-display");
            const dateWrap = container.querySelectorAll(".js-dls-date-wrap");
            const clearButton = container.querySelectorAll(".js-clear-range");

            function updateValueIndicator() {
                dateWrap.forEach(function (dateWrap) {
                    const clInputField = dateWrap.querySelector(
                        ".date-from, .date-to",
                    );
                    dateWrap.querySelectorAll(".js-clear-range");

                    if (!clInputField.value) {
                        dateWrap.classList.add("is-empty");
                        dateWrap.classList.remove("has-value");
                    } else {
                        dateWrap.classList.add("has-value");
                        dateWrap.classList.remove("is-empty");
                    }
                });
            }

            updateValueIndicator();

            const MAX_RANGE_ADJUSTMENT_DAYS = 7; // NOTE: back end has a weird month calculation
            const MAX_DAYS_FROM_TODAY = 365;

            const absoluteMaxDate = new Date();
            absoluteMaxDate.setDate(
                absoluteMaxDate.getDate() + MAX_DAYS_FROM_TODAY,
            );

            const phpDefaultFrom = new Date(
                window.splitGlobalDLScheduleData.defaultFrom,
            );
            const phpDefaultTo = new Date(
                window.splitGlobalDLScheduleData.defaultTo,
            );

            const defaultFrom = phpDefaultFrom;
            const defaultTo = phpDefaultTo;

            const flatpickrInstance = flatpickr(container, {
                mode: "range",
                showMonths: window.innerWidth < 767 ? 1 : 2,
                dateFormat: "Z",
                minDate: "today",
                maxDate: absoluteMaxDate,
                closeOnSelect: false,
                onOpen: function (selectedDates, dateStr, instance) {
                    if (
                        !instance.calendarContainer.querySelector(
                            ".range-limit-msg",
                        )
                    ) {
                        const msg = document.createElement("div");
                        msg.className = "range-limit-msg";
                        msg.innerText = $(container).attr("data-range-message");

                        instance.calendarContainer.appendChild(msg);
                    }

                    if (selectedDates.length > 0) {
                        instance.jumpToDate(selectedDates[0]);
                    }
                },
                onChange: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length === 1) {
                        const startDate = selectedDates[0];
                        const threeMonthsMax = new Date(startDate);
                        threeMonthsMax.setMonth(threeMonthsMax.getMonth() + 3);
                        threeMonthsMax.setDate(
                            threeMonthsMax.getDate() -
                                MAX_RANGE_ADJUSTMENT_DAYS,
                        );
                        instance.set(
                            "maxDate",
                            new Date(Math.min(threeMonthsMax, absoluteMaxDate)),
                        );
                    }

                    if (selectedDates.length === 2) {
                        const startDate = selectedDates[0];
                        const endDate = selectedDates[1];
                        const maxEndDate = new Date(startDate);
                        maxEndDate.setMonth(maxEndDate.getMonth() + 3);
                        maxEndDate.setDate(
                            maxEndDate.getDate() - MAX_RANGE_ADJUSTMENT_DAYS,
                        );

                        instance.set("maxDate", absoluteMaxDate);

                        if (endDate > maxEndDate) {
                            instance.setDate([startDate, maxEndDate]);
                            const msg =
                                instance.calendarContainer.querySelector(
                                    ".range-limit-msg",
                                );
                            if (msg) {
                                msg.classList.remove("is-pulsing");
                                void msg.offsetWidth;
                                msg.classList.add("is-pulsing");
                                msg.addEventListener(
                                    "animationend",
                                    function handler() {
                                        msg.classList.remove("is-pulsing");
                                        msg.removeEventListener(
                                            "animationend",
                                            handler,
                                        );
                                    },
                                );
                            }
                        }
                    }

                    if (selectedDates.length > 0) {
                        fromInput.value = instance.formatDate(
                            selectedDates[0],
                            "Z",
                        );
                        fromDisplay.value = instance.formatDate(
                            selectedDates[0],
                            format,
                        );
                        clearErrors();
                    }
                    if (selectedDates.length > 1) {
                        toInput.value = instance.formatDate(
                            selectedDates[1],
                            "Z",
                        );
                        toDisplay.value = instance.formatDate(
                            selectedDates[1],
                            format,
                        );
                    } else {
                        toInput.value = "";
                        toDisplay.value = "";
                    }

                    if (selectedDates.length == 0) {
                        fromInput.value = "";
                        toInput.value = "";
                        fromDisplay.value = "";
                        toDisplay.value = "";
                        instance.set("maxDate", absoluteMaxDate);
                    }

                    updateValueIndicator();
                },
                onClose: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length === 1) {
                        const fromDate = selectedDates[0];
                        const toDate = new Date(fromDate);
                        toDate.setMonth(toDate.getMonth() + 1);
                        instance.setDate(
                            [
                                fromDate,
                                new Date(Math.min(toDate, absoluteMaxDate)),
                            ],
                            true,
                        );
                    }
                },
            });

            flatpickrInstance.setDate([defaultFrom, defaultTo], true);

            clearButton.forEach(function (element) {
                $(element).on("click", function (e) {
                    e.stopPropagation();
                    flatpickrInstance.clear();
                });
            });
        });

    $(".js-download-schedule-filters").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();

        const apiUrl = theme.scheduleRestUrl;

        const validationDelay = 300;

        clearErrors();

        let hasInvalidSelect = false;
        form.find(".dls-ac-input").each(function () {
            const $input = $(this);
            const $fieldWrapper = $input.closest(".labeled-field-wrapper");
            const $select = $input.closest(".dls-select-wrap").find("select");
            if ($input.val() && !$select.val()) {
                setTimeout(function () {
                    markElementWithError(
                        $input.closest(".labeled-field-wrapper"),
                        $fieldWrapper.find(".js-select2-lbl").text() +
                            " " +
                            theme.nonExistantErrorMsg,
                    );
                }, validationDelay);
                hasInvalidSelect = true;
            }
        });

        form.find(".js-dl-schedule-date-range").each(function () {
            const fromHidden = $(this).find(".date-from");
            const toHidden = $(this).find(".date-to");
            const toDisplay = $(this).find(".date-to-display");

            if (fromHidden.val() && !toHidden.val()) {
                const fromDate = new Date(fromHidden.val());
                const toDate = new Date(fromDate);
                toDate.setMonth(toDate.getMonth() + 1);
                toHidden.val(toDate.toISOString());
                const d = String(toDate.getDate()).padStart(2, "0");
                const m = String(toDate.getMonth() + 1).padStart(2, "0");
                toDisplay.val(`${d}.${m}.${toDate.getFullYear()}`);
            }
        });

        let isEmpty = false;

        form.find(".js-dls-date-wrap input").each(function () {
            if (!$(this).val()) {
                isEmpty = true;
            }
        });

        if (isEmpty) {
            setTimeout(function () {
                markElementWithError(
                    form
                        .find(".js-dls-date-wrap input")
                        .closest(".js-dl-schedule-date-range"),
                    theme.requiredErrorMsg,
                );
            }, validationDelay);
            return;
        }

        if (hasInvalidSelect) {
            return;
        }

        const submitBtn = form.find('[type="submit"]');
        submitBtn.prop("disabled", true);
        form.closest(".download-schedule-wrapper").addClass("is-loading-dls");

        $.ajax({
            url: apiUrl + "?" + formData,
            method: "GET",
            dataType: "json",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", theme.restNonce);
            },
            success: function (response) {
                clearErrors();

                if (!response.has_results) {
                    form.closest(".download-schedule-wrapper").addClass(
                        "dls-no-results",
                    );
                } else {
                    form.closest(".download-schedule-wrapper").removeClass(
                        "dls-no-results",
                    );
                }

                window.splitGlobalDLScheduleData.flights = response.flights;
                window.splitGlobalDLScheduleData.filters = response.filters;

                form.closest(".download-schedule-wrapper")
                    .find(".download-schedule-content")
                    .html(response.table_html);
            },
            error: function (xhr) {
                try {
                    var errorResponse = JSON.parse(xhr.responseText);

                    if (errorResponse.data && errorResponse.data.params) {
                        var missingParams = errorResponse.data.params;

                        $.each(missingParams, function (fieldName, message) {
                            var inputField = form.find(
                                '[name="' + fieldName + '"]',
                            );

                            if (inputField.length) {
                                const target =
                                    fieldName === "dls_from_date" ||
                                    fieldName === "dls_to_date"
                                        ? inputField.closest(
                                              ".js-dl-schedule-date-range",
                                          )
                                        : inputField.closest(
                                              ".js-dls-date-wrap",
                                          );

                                markElementWithError(target, message);
                            }
                        });
                    } else {
                        $(".js-download-schedule-filters").addClass(
                            "dls-generic-error",
                        );
                        $(".js-download-schedule-filters").attr(
                            "data-generic-error",
                            theme.genericError,
                        );
                    }
                } catch (e) {
                    $(".js-download-schedule-filters")
                        .addClass("dls-generic-error")
                        .addClass("dls-unknown-error");
                    $(".js-download-schedule-filters").attr(
                        "data-generic-error",
                        theme.genericError,
                    );
                }
            },
            complete: function () {
                submitBtn.prop("disabled", false);
                form.closest(".download-schedule-wrapper").removeClass(
                    "is-loading-dls",
                );
                form.closest(".initial-data").removeClass("initial-data");
            },
        });
    });
});
