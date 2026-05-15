import flatpickr from "flatpickr";
import pdfMake from "pdfmake";
import "pdfmake/build/vfs_fonts";

pdfMake.vfs = window.pdfMake.vfs;

$(function () {
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

            flatpickr(container, {
                mode: "range",
                showMonths: 2,
                dateFormat: format,
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
                },
                onChange: function (selectedDates, dateStr, instance) {
                    // 1. Enforce the 3-month limit
                    if (selectedDates.length === 2) {
                        const startDate = selectedDates[0];
                        const endDate = selectedDates[1];
                        const maxEndDate = new Date(startDate);
                        maxEndDate.setMonth(maxEndDate.getMonth() + 3);

                        if (endDate > maxEndDate) {
                            instance.setDate([startDate, maxEndDate]);
                            alert("Maximum range is 3 months.");
                        }
                    }

                    if (selectedDates.length > 0) {
                        fromInput.value = instance.formatDate(
                            selectedDates[0],
                            format,
                        );
                    }
                    if (selectedDates.length > 1) {
                        toInput.value = instance.formatDate(
                            selectedDates[1],
                            format,
                        );
                    } else {
                        toInput.value = "";
                    }
                },
            });
        });
});
