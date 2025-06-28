# Import/Export Functionality Documentation

## Overview

The admin page now includes comprehensive import/export functionality that allows administrators to manage data using open formats - CSV (Comma Separated Values) and JSON (JavaScript Object Notation).

## Features

### Export Functionality
- **Data Types**: Users, Events, Sports Fields, or All Data
- **Formats**: CSV and JSON
- **Authentication**: Requires admin privileges
- **File Naming**: Automatic timestamp-based naming (e.g., `users_export_2024-12-19_15-30-45.csv`)

### Import Functionality
- **Data Types**: Users, Events, Sports Fields
- **Formats**: CSV and JSON
- **Validation**: Duplicate checking and error handling
- **Results**: Detailed import results with success/error counts

### Template Downloads
- Pre-formatted templates for each data type and format
- Helps users understand the required data structure
- Available for immediate download

## Usage

### Accessing the Feature
1. Log in as an administrator
2. Navigate to the Admin Dashboard
3. Click on the "Data Management" tab

### Exporting Data
1. Select the data type (Users, Events, Fields, or All)
2. Choose the format (JSON or CSV)
3. Click "Export Data"
4. The file will automatically download

### Importing Data
1. Select the data type to import
2. Choose the format of your file
3. Click "Choose File" and select your CSV or JSON file
4. Click "Import Data"
5. Review the import results

### Downloading Templates
1. Click any template button (e.g., "Users CSV Template")
2. The template file will download automatically
3. Use the template as a starting point for your data

## Data Formats

### Users CSV Format
```csv
username,email,is_admin
john_doe,john@example.com,0
jane_admin,jane@example.com,1
```

### Users JSON Format
```json
[
  {
    "username": "john_doe",
    "email": "john@example.com",
    "is_admin": 0
  },
  {
    "username": "jane_admin",
    "email": "jane@example.com",
    "is_admin": 1
  }
]
```

### Events CSV Format
```csv
title,description,sport_type,start_time,end_time,max_participants,field_name,creator_name
Basketball Game,Weekly basketball game,Basketball,2024-01-15 18:00:00,2024-01-15 20:00:00,10,Main Court,john_doe
```

### Events JSON Format
```json
[
  {
    "title": "Basketball Game",
    "description": "Weekly basketball game",
    "sport_type": "Basketball",
    "start_time": "2024-01-15 18:00:00",
    "end_time": "2024-01-15 20:00:00",
    "max_participants": 10,
    "field_name": "Main Court",
    "creator_name": "john_doe"
  }
]
```

### Fields CSV Format
```csv
name,sport_type,location,capacity,description
Main Court,Basketball,Central Park,20,Indoor basketball court
Tennis Court,Tennis,Sports Complex,4,Outdoor tennis court
```

### Fields JSON Format
```json
[
  {
    "name": "Main Court",
    "sport_type": "Basketball",
    "location": "Central Park",
    "capacity": 20,
    "description": "Indoor basketball court"
  }
]
```

## Import Behavior

### Users Import
- **Duplicate Check**: Based on email address
- **Password**: Default password `default_password_123` is set for imported users
- **Admin Status**: Preserved from import data

### Events Import
- **Duplicate Check**: Based on title and start time
- **Field Reference**: Uses field name to find existing field_id
- **Creator Reference**: Uses creator name to find existing user_id
- **Missing References**: Events with non-existent fields/creators will still be imported with null references

### Fields Import
- **Duplicate Check**: Based on field name
- **Capacity**: Defaults to 10 if not specified
- **Location/Description**: Defaults to empty string if not specified

## Error Handling

### Import Errors
- **Validation**: File format validation
- **Duplicate Handling**: Skips existing records
- **Error Reporting**: Detailed error messages for failed imports
- **Partial Success**: Continues importing even if some records fail

### Export Errors
- **Authentication**: Redirects to login if not authenticated
- **Permission**: Requires admin privileges
- **File Generation**: Handles database connection errors

## Security Features

- **Admin Authentication**: All operations require admin privileges
- **JWT Validation**: Secure token-based authentication
- **Input Validation**: File format and content validation
- **SQL Injection Protection**: Prepared statements for all database operations
- **File Type Restrictions**: Only accepts .csv and .json files

## API Endpoints

### Export
```
GET /api/index.php?action=adminImportExport&operation=export&type={dataType}&format={format}
```

### Import
```
POST /api/index.php?action=adminImportExport&operation=import&type={dataType}&format={format}
```

## Testing

Sample files are provided for testing:
- `test_import_users.csv` - Sample users data
- `test_import_events.json` - Sample events data

## Browser Compatibility

- **Modern Browsers**: Chrome, Firefox, Safari, Edge
- **File API**: Uses HTML5 File API for file reading
- **Download**: Uses Blob API for file downloads
- **Fallbacks**: Graceful degradation for older browsers

## Performance Considerations

- **Large Files**: Handles files up to server upload limits
- **Memory Usage**: Streams file processing for large datasets
- **Database**: Uses transactions for data consistency
- **Caching**: No caching to ensure fresh data

## Troubleshooting

### Common Issues
1. **File not uploading**: Check file size and format
2. **Import errors**: Verify data format matches template
3. **Authentication errors**: Ensure you're logged in as admin
4. **Download issues**: Check browser download settings

### Support
For issues with import/export functionality, check:
1. Browser console for JavaScript errors
2. Server logs for PHP errors
3. Database connection status
4. File permissions and upload limits 