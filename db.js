const mysql = require("mysql2");

// Create connection pool (better than single connection)
const db = mysql.createPool({
  host: "localhost",
  user: "root",
  password: "", // change if you have password
  database: "pizzapp", // IMPORTANT: your new DB
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

// Test connection
db.getConnection((err, connection) => {
  if (err) {
    console.error("Database connection failed:", err);
  } else {
    console.log("✅ MySQL connected to pizzapp");
    connection.release();
  }
});

module.exports = db;
