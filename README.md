# Local Greeting - Sports Events Platform

A modern web platform for discovering and participating in sports events in Iași.

## Features

- Interactive map showing sports fields in Iași
- Browse and search for sports events
- Create and manage your own events
- Join events and connect with other sports enthusiasts
- Filter events by sport type
- Responsive design for all devices

## Frontend Structure

```
public/
├── css/
│   ├── style.css        # Main styles
│   ├── home.css         # Home page styles
│   ├── events.css       # Events page styles
│   ├── auth.css         # Authentication pages styles
│   └── map.css          # Map styles
├── js/
│   ├── main.js          # Main JavaScript file
│   ├── events.js        # Events page functionality
│   └── map.js           # Map functionality
├── images/              # Image assets
├── index.html          # Home page
├── events.html         # Events listing page
├── create-event.html   # Event creation page
├── login.html          # Login page
└── register.html       # Registration page
```

## Technologies Used

- HTML5
- CSS3
- JavaScript (ES6+)
- Leaflet.js for interactive maps
- OpenStreetMap for map data
- Overpass API for sports field data

## Getting Started

1. Clone the repository
2. Open `index.html` in your web browser
3. No server setup required - this is a static frontend project

## Map Features

The interactive map shows all sports fields in Iași using data from OpenStreetMap. Features include:
- Centered on Iași
- Shows all sports fields (pitches)
- Click markers to see field information
- Responsive design
- Custom styling to match the site theme

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 