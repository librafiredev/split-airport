import { accordions } from "../../assets/components/accordions";

$(function() {
    const trigger = $('.accordions__item-title');
    const item = $('.accordions__item-text');
    accordions(trigger, item);
});