# Location CRUD Implementation

## Overview
Complete CRUD implementation for Locations module following the same fetch API architecture as Employees and Brands for smooth user experience without page reloads.

## Implementation Details

### 1. API Layer (utils/api.js)
Added comprehensive `locationApi` functions:
- `getLocations()` - Fetch all locations with pagination
- `getLocation(id)` - Fetch single location
- `createLocation(data)` - Create new location
- `updateLocation(id, data)` - Update existing location
- `deleteLocation(id)` - Delete location
- `searchLocations(query)` - Search locations for autocomplete

### 2. Backend Controller (LocationController.php)
Enhanced with dual JSON/Inertia response support:
- **Dual Response Logic**: Detects fetch API vs Inertia navigation
- **Comprehensive Validation**: Location-specific rules with coordinate validation
- **Search Functionality**: Full-text search across name, code, address, description
- **Filter Support**: Status filtering for active/inactive locations
- **Relationship Counts**: Includes employees, packs, and kemas counts
- **Error Handling**: Proper error responses for both JSON and Inertia requests

### 3. Resource Layer (LocationResource.php)
Enhanced resource with complete field mapping:
- All location fields (name, code, latitude, longitude, radius, address, description, is_active)
- Related counts (employees_count, packs_count, kemas_count)
- Formatted timestamps for display
- Proper type casting (boolean for is_active)

### 4. Vue Pages

#### Index Page (`Locations/Index.vue`)
- **DataTable**: Comprehensive location listing with pagination
- **Client-side Search**: Real-time filtering without API calls
- **Status Filter**: Filter by active/inactive status
- **Coordinate Display**: Formatted latitude/longitude display
- **Usage Statistics**: Shows related counts (employees, packs, kemas)
- **Action Buttons**: View, Edit, Delete with proper permissions
- **Delete Confirmation**: Modal dialog with usage validation
- **Loading States**: Proper loading indicators
- **No Data State**: Informative empty state with call-to-action

#### Create Page (`Locations/Create.vue`)
- **Form Validation**: Client-side and server-side validation
- **Coordinate Input**: Separate lat/lng fields with validation
- **Map Integration**: Google Maps preview link for coordinates
- **Auto-code Generation**: Generates code from name automatically
- **Status Toggle**: Active/inactive checkbox
- **Error Handling**: Field-specific error display
- **User Experience**: Smooth form submission with loading states

#### Edit Page (`Locations/Edit.vue`)
- **Pre-populated Form**: Loads existing location data
- **Usage Information**: Shows current usage statistics
- **Map Integration**: Google Maps preview for existing coordinates
- **Validation**: Same validation as create with unique code handling
- **Status Management**: Toggle active/inactive status
- **Navigation**: Easy access to view and index pages

#### Show Page (`Locations/Show.vue`)
- **Comprehensive Display**: All location information organized in cards
- **Map Integration**: Links to Google Maps and OpenStreetMap
- **Usage Statistics**: Visual cards showing related counts
- **Action Buttons**: Edit, Delete (with usage validation), Create new
- **Coordinate Display**: Formatted coordinates with map links
- **Metadata**: Creation and update timestamps
- **Delete Protection**: Prevents deletion if location is in use

### 5. Routes (web.php)
Added complete resourceful routes:
```php
// Location routes
Route::resource('locations', LocationController::class);
Route::get('locations-search', [LocationController::class, 'search'])->name('locations.search');
```

## Key Features

### Geographic Functionality
- **Coordinate Management**: Latitude/longitude with validation (-90 to 90 for lat, -180 to 180 for lng)
- **Radius Support**: Configurable radius in meters for geofencing
- **Map Integration**: Direct links to Google Maps and OpenStreetMap
- **Coordinate Display**: Formatted to 6 decimal places for precision

### Data Validation
- **Required Fields**: Name and code are mandatory
- **Unique Constraints**: Location code must be unique
- **Coordinate Validation**: Proper range validation for geographic coordinates
- **Radius Validation**: Must be positive number if provided

### User Experience
- **Smooth Navigation**: No page reloads for CRUD operations
- **Real-time Search**: Client-side filtering for instant results
- **Loading States**: Proper feedback during operations
- **Error Handling**: Clear error messages for validation failures
- **Responsive Design**: Works on desktop and mobile devices

### Business Logic
- **Usage Tracking**: Counts related employees, packs, and kemas
- **Delete Protection**: Prevents deletion of locations in use
- **Status Management**: Active/inactive status for location management
- **Auto-code Generation**: Generates URL-friendly codes from names

## Database Schema
Locations table includes:
- `id` - Primary key
- `name` - Location name (required)
- `code` - Unique location code (required)
- `latitude` - Geographic latitude (optional)
- `longitude` - Geographic longitude (optional)
- `radius` - Geofence radius in meters (optional)
- `address` - Full address (optional)
- `description` - Location description (optional)
- `is_active` - Status flag (boolean)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Relationships
- **Employees**: One-to-many (location has many employees)
- **Packs**: One-to-many (location has many packs)
- **Kemas**: One-to-many (location has many kemas)

## Usage
1. Navigate to `/locations` to view all locations
2. Use search and filters for finding specific locations
3. Create new locations with `/locations/create`
4. Edit existing locations with `/locations/{id}/edit`
5. View detailed information with `/locations/{id}`
6. Delete locations (if not in use) with confirmation dialog

## Technical Notes
- Uses PrimeVue components for consistent UI
- Follows Laravel best practices for controllers and resources
- Implements client-side filtering for better performance
- Uses fetch API for smooth user experience
- Proper error handling and validation
- Responsive design with Tailwind CSS
- Map integration for geographic visualization

## Architecture Consistency
This implementation follows the exact same patterns as the Employee and Brand modules:
- Dual JSON/Inertia response controllers
- Client-side filtering and search
- Comprehensive form validation
- Consistent UI components and styling
- Same error handling patterns
- Identical user experience patterns

This ensures maintainability and consistency across the entire application.
