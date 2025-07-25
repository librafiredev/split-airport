import navigation from "../components/navigation";
import myFlights from "../components/myFlights";
import flightPopup from "../../assets/components/flightPopup";
import globalSidebar from "../components/global-sidebar";

$(function () {
    navigation.init();
    myFlights.init();
    flightPopup.init();
    globalSidebar.init();
});
