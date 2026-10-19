
# Vehicle Parking Session REST API

A secure, high-performance, zero-dependency vanilla PHP RESTful API built for managing and querying vehicle parking sessions. Designed with a custom micro-router, secure authentication patterns, and advanced spatial/temporal query handling.

## 🚀 Architectural Highlights

* **Zero-Dependency Core:** Built using pure, modern PHP principles without the overhead of heavy full-stack frameworks.
* **Custom Micro-Router:** Lightweight URI parser mapped dynamically to endpoint handlers via `class-endpoint.php`.
* **Database Abstraction & Auto-Migration:** Native `mysqli` wrapper supporting prepared statements, safe parameter binding, and automatic schema provisioning (`vehicle_sessions` and `api_keys`) on first boot via `class-database.php`.
* **Enterprise Security:** 
  * Stripe-style `key_id:secret` authentication pattern.
  * Secure credential hashing via PHP's `password_verify()` in `class-session-api.php`.
  * Environment variable isolation via `vlucas/phpdotenv` loaded in `index.php`.
* **Advanced Domain Logic:**
  * **Trigram Fuzzy Search:** Algorithmic fuzzy-matching for Vehicle Registration Marks (VRMs) with adaptive character thresholds.
  * **Temporal Window Overlapping:** Complex SQL filtering supporting active, completed, and overstayed sessions across flexible date-time ranges.

---

## 📂 Project Structure

```text
├── api/
│   ├── .htaccess             # Rewrites requests to the entry point
│   ├── index.php             # Bootstrap, CORS headers, environment loading[cite: 2]
│   └── includes/
│       ├── class-database.php    # MySQLi wrapper & auto-migration setup
│       ├── class-endpoint.php    # Custom micro-router & dispatcher[cite: 5]
│       └── class-session-api.php # Core REST endpoints & business logic
├── .env                      # Environment configurations (DB credentials)
├── composer.json             # Dependency definitions (phpdotenv)
└── README.md


# Parking Management API Reference

Welcome to the documentation for the Parking Management API. This service allows you to create, manage, and query vehicle parking sessions.

---

## 🔒 Authentication

All API endpoints require standard Bearer Token authorization. You must include your access token in the `Authorization` header of every request:

```http
Authorization: Bearer your_api_key_id:your_api_secret
```

---

## 🔌 API Endpoints Reference

### 1. Register Session

Creates a new parking session record, validates payload data, and automatically calculates the initial session status (`active`, `overstay`, or `completed`).

* **HTTP Method:** `POST`
* **Endpoint:** `/api/register_session`
* **Content-Type:** `application/x-www-form-urlencoded`

#### Request Parameters

##### Required Fields
| Field | Type | Description |
| :--- | :--- | :--- |
| `vrm` | `string` | Vehicle Registration Mark (automatically converted to uppercase and stripped of spaces). |
| `make` | `string` | Vehicle manufacturer (e.g., `Ford`, `BMW`). |
| `model` | `string` | Vehicle model. |
| `colour` | `string` | Vehicle color. |
| `session_start` | `string` / `timestamp` | Start time when the vehicle entered the session. |
| `session_agreed_end` | `string` / `timestamp` | The agreed departure/expiry time. |
| `session_type` | `string` | Classification type. Must be one of: `permit`, `free`, or `paid`. |
| `location_code` | `string` | Identifier for the car park location. |

##### Optional Fields
| Field | Type | Description |
| :--- | :--- | :--- |
| `session_end` | `string` / `timestamp` | Actual departure time (if concluding immediately upon registration). |

---

### 2. End Session

Concludes an active parking session by recording the actual departure time and updating the status accordingly.

* **HTTP Method:** `POST`
* **Endpoint:** `/api/end_session`
* **Content-Type:** `application/x-www-form-urlencoded`

#### Request Parameters

##### Required Fields
| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | `integer` | The unique database ID of the parking session. |
| `session_end` | `string` / `timestamp` | The time the vehicle exited/ended the session. |

#### Validation Rules & Responses
* **`400 Bad Request`**: Returned if `id` is non-numeric.
* **`409 Conflict`**: Returned if the `session_end` timestamp matches an already registered end time.

---

### 3. Get Single Session

Fetches all details for a single specific parking session by its unique database ID.

* **HTTP Method:** `GET`
* **Endpoint:** `/api/get_session`

#### Query Parameters

| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `id` | `integer` | **Yes** | The unique database ID of the session. |

#### Response Data
Returns the matching session record including VRM, make, model, timestamps, type, and current status.

---

### 4. Session Lookup & Search

Advanced search endpoint designed for monitoring dashboards. Supports strict or fuzzy VRM matching and flexible temporal window overlap filtering.

* **HTTP Method:** `GET`
* **Endpoint:** `/api/session_lookup`

#### Query Parameters

| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `vrm` | `string` | No | The vehicle registration mark to query. |
| `fuzzy` | `string` | No | Set to `true` to enable trigram fuzzy-matching for partial or mistyped VRM lookups. |
| `period_from` | `string` / `timestamp` | No | Start of the search time window. |
| `period_to` | `string` / `timestamp` | No | End of the search time window. |
| `use_actual_end` | `boolean` / `string` | No | Set to switch window boundary checks from `session_agreed_end` to `session_end`. |