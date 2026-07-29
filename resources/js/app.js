import './bootstrap';

// Global application utilities
window.GempaApp = {
    formatDecimal: (value, places = 4) => parseFloat(value).toFixed(places),
    formatCoordinate: (lat, lng) => `${Math.abs(lat).toFixed(6)}° ${lat >= 0 ? 'N' : 'S'}, ${Math.abs(lng).toFixed(6)}° ${lng >= 0 ? 'E' : 'W'}`
};
