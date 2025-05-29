import { accordions } from "../../assets/components/accordions";
import stickyImages from "../../assets/components/stickyImages";

$(function () {
    const trigger = $('.accordions__item-title');
    const item = $('.accordions__item-text');
    accordions(trigger, item);

    stickyImages.init();
});