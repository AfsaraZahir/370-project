const express = require("express");
const path = require("path");
const db = require("./db");
const session = require("express-session");

const app = express();
const PORT = 3000;

app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, "public")));
app.set("view engine", "ejs");

app.use(
  session({
    secret: "secret-key",
    resave: false,
    saveUninitialized: true,
  }),
);

// ===== AUTH =====
function requireLogin(req, res, next) {
  if (!req.session.user) return res.redirect("/login");
  next();
}

// ===== BASIC ROUTES =====
app.get("/", (req, res) => res.render("index"));

app.get("/login", (req, res) => res.render("login"));
app.get("/signup", (req, res) => res.render("signup"));

app.post("/login", (req, res) => {
  const { email, password } = req.body;

  db.query("SELECT * FROM Users WHERE email=?", [email], (err, result) => {
    if (!result.length) return res.redirect("/login");

    const user = result[0];
    if (user.password_hash !== password) return res.redirect("/login");

    req.session.user = user;
    res.redirect("/menu");
  });
});

app.post("/signup", (req, res) => {
  const { username, email, password, role } = req.body;

  db.query(
    "INSERT INTO Users (username, email, password_hash, role) VALUES (?, ?, ?, ?)",
    [username, email, password, role],
    (err, result) => {
      if (err) return res.redirect("/signup?error=Signup+failed");

      const user_id = result.insertId;

      if (role === "customer") {
        db.query(
          "INSERT INTO Customer (customer_id, full_name) VALUES (?, ?)",
          [user_id, username],
          (err) => {
            if (err) return res.redirect("/signup?error=Signup+failed");
            res.redirect("/login?success=Account+created");
          },
        );
      } else if (role === "admin") {
        db.query(
          "INSERT INTO Admin (admin_id, full_name) VALUES (?, ?)",
          [user_id, username],
          (err) => {
            if (err) return res.redirect("/signup?error=Signup+failed");
            res.redirect("/login?success=Account+created");
          },
        );
      } else if (role === "driver") {
        db.query(
          "INSERT INTO Driver (driver_id, full_name) VALUES (?, ?)",
          [user_id, username],
          (err) => {
            if (err) return res.redirect("/signup?error=Signup+failed");
            res.redirect("/login?success=Account+created");
          },
        );
      } else {
        res.redirect("/signup?error=Invalid+role");
      }
    },
  );
});

// ===== VIEWS =====
app.get("/menu", requireLogin, (req, res) => res.render("menu"));
app.get("/cart", requireLogin, (req, res) => res.render("cart"));

// ===== DATA APIs =====
app.get("/get-pizzas", requireLogin, (req, res) => {
  db.query("SELECT * FROM Pizza", (err, result) => res.json(result));
});

app.get("/get-crusts", requireLogin, (req, res) => {
  db.query("SELECT * FROM Crust", (err, result) => res.json(result));
});

app.get("/get-toppings", requireLogin, (req, res) => {
  db.query("SELECT * FROM Toppings", (err, result) => res.json(result));
});

// ===== CART =====
function getCart(req) {
  if (!req.session.cart) req.session.cart = [];
  return req.session.cart;
}

app.get("/get-cart", requireLogin, (req, res) => {
  res.json(getCart(req));
});

app.post("/add-to-cart", requireLogin, (req, res) => {
  const cart = getCart(req);
  cart.push(req.body);
  res.json({ success: true });
});

app.post("/remove-from-cart", requireLogin, (req, res) => {
  const { index } = req.body;
  const cart = getCart(req);
  cart.splice(index, 1);
  res.json({ success: true });
});

// ===== PLACE ORDER =====
app.post("/place-order", requireLogin, (req, res) => {
  const user = req.session.user;
  const cart = getCart(req);

  if (!cart.length) return res.redirect("/cart");

  db.query(
    "INSERT INTO Orders (customer_id,total_amount) VALUES (?,?)",
    [user.user_id, 0],
    (err, orderRes) => {
      const order_id = orderRes.insertId;
      let total = 0;

      cart.forEach((item) => {
        total += item.price * item.quantity;

        db.query(
          "INSERT INTO Order_Items (order_id,pizza_id,crust_id,quantity,item_price) VALUES (?,?,?,?,?)",
          [order_id, item.pizza_id, item.crust_id, item.quantity, item.price],
          (err, itemRes) => {
            const item_id = itemRes.insertId;

            item.toppings.forEach((t) => {
              db.query(
                "INSERT INTO Order_Toppings (item_id,topping_id) VALUES (?,?)",
                [item_id, t],
              );
            });
          },
        );
      });

      db.query("UPDATE Orders SET total_amount=? WHERE order_id=?", [
        total,
        order_id,
      ]);

      req.session.cart = [];
      res.redirect("/menu");
    },
  );
});

app.listen(PORT, () => console.log("Server running on port", PORT));
