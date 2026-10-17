<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chris Smith - Vehicle Lookup</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="src/global.css" />

    <!-- jQuery & Select2 (Required dependencies) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="src/global.js"></script>
</head>

<body class="container_wrap">
    <section class="container">
        <h1>Vehicle Lookup</h1>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="mb-6 border-b border-slate-100 pb-4">
                <h2 class="text-xl font-bold text-slate-800">Query Vehicle Sessions</h2>
                <p class="text-sm text-slate-500">Filter sessions by vehicle details and specific date/time windows.</p>
            </div>

            <form id="sessionSearchForm" action="api/sessions.php" method="GET" class="space-y-6 flex gap-2 justify-center max-md:flex-wrap">

                <!-- Vehicle Search (Select2 AJAX Dropdown) -->
                <div class="flex-1 max-md:flex-auto max-md:w-full">
                    <label for="vehicleSelect" class="block text-sm font-semibold text-slate-700 mb-1">
                        Vehicle Search (VRM, Make, Model, or Colour)
                    </label>
                    <select id="vehicleSelect" name="vrm" class="w-full">
                        <option value="">Type to search vehicle (e.g. AB12 CDE, Ford, Blue)...</option>
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Start typing to perform a live fuzzy lookup against registered vehicles.</p>
                </div>

                <!-- Date & Time Range Pickers -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 gap-y-0 flex-2 max-md:flex-auto max-md:w-full">
                    <!-- Session Start -->
                    <div>
                        <label for="sessionStart" class="block text-sm font-semibold text-slate-700 mb-1">
                            Session Start From
                        </label>
                        <input type="datetime-local" id="sessionStart" name="from" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white" required>
                    </div>

                    <!-- Session End -->
                    <div>
                        <label for="sessionEnd" class="block text-sm font-semibold text-slate-700 mb-1">
                            Session End To
                        </label>
                        <input type="datetime-local" id="sessionEnd" name="to" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white" required>
                    </div>

                    <!-- Checkbox: Actual End Time vs Agreed End Time -->
                    <div class="flex items-center gap-2 pt-1 col-span-2">
                        <input type="checkbox" id="useActualEnd" name="use_actual_end" value="1" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500">
                        <label for="useActualEnd" class="text-sm font-medium text-slate-700 cursor-pointer">
                            Match using <span class="font-semibold text-slate-900">Actual End Time</span> (Default uses Agreed End Time)
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        &nbsp;
                    </label>
                    <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Search Vehicle Sessions
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 my-4" id="vehicleResults">
            <table class="w-full text-left text-sm text-slate-600">
                <!-- Table Head -->
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">VRM</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Vehicle Info</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Type</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Session Start</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Agreed End</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Actual End</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="divide-y divide-slate-200 bg-white">

                    <!-- Row 1: Active Paid Session -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                            <span class="px-2 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded font-mono text-xs">
                                AB12 CDE
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-800">Ford Focus</div>
                            <div class="text-xs text-slate-400">Silver</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                Paid Session
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-17 10:00
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-17 14:00
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-400 italic">
                            -- Active --
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Active
                            </span>
                        </td>
                    </tr>

                    <!-- Row 2: Overstay Session -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                            <span class="px-2 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded font-mono text-xs">
                                XY55 ZAA
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-800">BMW 3 Series</div>
                            <div class="text-xs text-slate-400">Black</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                Free Session
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-17 08:30
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-17 10:30
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-17 11:45
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                Overstay
                            </span>
                        </td>
                    </tr>

                    <!-- Row 3: Completed Permit Session -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                            <span class="px-2 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded font-mono text-xs">
                                KL68 MNO
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-800">Volkswagen Golf</div>
                            <div class="text-xs text-slate-400">Blue</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Permit
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-16 09:00
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-16 17:00
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            2026-10-16 16:50
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                Completed
                            </span>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </section>
</body>
<script id="template-vehicle-results" type="x-tmpl-mustache">
    {{#sessions}}
        <tr class="hover:bg-slate-50 transition-colors">
            <!-- VRM -->
            <td class="px-4 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                <span class="px-2 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded font-mono text-xs">
                    {{vrm}}
                </span>
            </td>

            <!-- Vehicle Info (Make, Model, Colour) -->
            <td class="px-4 py-3.5 whitespace-nowrap">
                <div class="font-medium text-slate-800">{{make}} {{model}}</div>
                <div class="text-xs text-slate-400">{{colour}}</div>
            </td>

            <!-- Session Type (Conditional Badge Styling) -->
            <td class="px-4 py-3.5 whitespace-nowrap">
                {{#is_paid}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Paid Session</span>
                {{/is_paid}}
                {{#is_free}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">Free Session</span>
                {{/is_free}}
                {{#is_permit}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">Permit</span>
                {{/is_permit}}
            </td>

            <!-- Timings -->
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                {{session_start}}
            </td>
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                {{session_agreed_end}}
            </td>
            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                {{#session_end}}{{session_end}}{{/session_end}}
                {{^session_end}}<span class="text-slate-400 italic">-- Active --</span>{{/session_end}}
            </td>

            <!-- Status (Conditional Badge & Pulse Animation) -->
            <td class="px-4 py-3.5 whitespace-nowrap">
                {{#is_active}}
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active
                    </span>
                {{/is_active}}
                {{#is_overstay}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        Overstay
                    </span>
                {{/is_overstay}}
                {{#is_completed}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        Completed
                    </span>
                {{/is_completed}}
            </td>
        </tr>
    {{/sessions}}

    {{^sessions}}
        <tr>
            <td colspan="7" class="px-4 py-8 text-center text-slate-400 italic">
                No vehicle sessions found matching your criteria.
            </td>
        </tr>
    {{/sessions}}
</script>

</html>
