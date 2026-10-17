export class VehicleLookup {
    constructor() {
        console.log("This is working");
        this.init();
    }

    init(): void {
        window.onload = this.onLoad();
    }

    vehicleSelect
    vehicleResults

    onLoad(): void {
        this.vehicleSelect = $('#vehicleSelect');
        this.vehicleResults = $('#vehicleResults');
        this.vehicleSelect.select2({
            placeholder: 'Type VRM, Make, Model, or Colour...',
            allowClear: true,
            ajax: {
                url: 'api/vehicles_lookup.php',
                dataType: 'json',
                delay: 250, // 250ms debounce
                data: function (params) {
                    return {
                        q: params.term // User search term sent to backend
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            return {
                                id: item.vrm,
                                text: `${item.vrm} — ${item.make} ${item.model} (${item.colour})`
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });
    }

    /** Vehicle Lookup */

}
