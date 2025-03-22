const express = require('express');
const mysql = require('mysql');
const path = require('path'); // For handling file paths

const app = express();

// Create database connection
const connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '', // Leave blank if there's no password
    database: 'test' // Replace with your database name
});

// Connect to the database
connection.connect((err) => {
    if (err) {
        console.error('Error connecting to the database:', err);
        return;
    }
    console.log('Connected to the MySQL database!');
});

// Serve testing.html
app.get('/testing.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'testing.html'));
});

// Handle data query and display dynamically
app.get('/data', (req, res) => {
    connection.query('SELECT * FROM test', (err, results) => {
        if (err) {
            console.error('Error executing query:', err);
            res.status(500).send('Server error during database query');
            return;
        }

        let html = `<h1>Data from MySQL</h1>`;
        html += `<ul>`;
        results.forEach(row => {
            html += `<li>${row.Name}</li>`;
        });
        html += `</ul>`;

        res.send(html);
    });
});

// Start the server
app.listen(3000, () => {
    console.log('Server is running on http://localhost:3000/testing.html');
});