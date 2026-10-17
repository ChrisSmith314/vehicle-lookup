import {
    VehicleLookup
} from './systems/vehicle-search';

window.onload = () => {
    if ($('#sessionSearchForm').length) {
        let vehicleLookup = new VehicleLookup();
    }
}
