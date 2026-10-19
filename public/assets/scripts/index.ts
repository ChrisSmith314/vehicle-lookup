import {
    VehicleLookup
} from './systems/vehicle-search';

window.onload = () => {
    if (document.getElementById('sessionSearchForm').length) {
        let vehicleLookup = new VehicleLookup();
    }
}
