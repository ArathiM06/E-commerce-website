import React, { useState, useEffect } from "react";
import { useSearchParams, Link } from "react-router-dom";

function Cart() {
  const [searchParams] = useSearchParams();

  const orderPlaced = searchParams.get("success") === "1";

  const [cartItems, setCartItems] = useState([]);
  const [subtotal, setSubtotal] = useState(0);

  const user = JSON.parse(localStorage.getItem("user"));
  const isLoggedIn = !!localStorage.getItem("token");

  const [formData, setFormData] = useState({
    customer_name: user?.name || "",
    customer_email: user?.email || "",
    customer_phone: "",
    department: "",
    roll_number: "",
    delivery_address: ""
  });
useEffect(() => {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];

    setCartItems(cart);

    const total = cart.reduce(
      (sum, item) => sum + item.price * item.quantity,
      0
    );

    setSubtotal(total);
  }, []);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleCheckout = async (e) => {
    e.preventDefault();

    const orderData = {
      ...formData,
      items: cartItems,
      total_amount: subtotal
    };

    try {
      const response = await fetch(
        "http://localhost:8000/api/orders",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization:
              "Bearer " + localStorage.getItem("token")
          },
          body: JSON.stringify(orderData)
        }
      );

      const result = await response.json();

      if (response.ok) {
        localStorage.removeItem("cart");

        window.location.href =
          `/cart?success=1&order_id=${result.id}` +
          `&name=${formData.customer_name}` +
          `&date=${new Date().toLocaleString()}` +
          `&total=${subtotal}`;
      }
    } catch (err) {
      alert("Failed to place order");
    }
  };
if (orderPlaced) {
    return (
      <main className="container page-main">

        <div className="success-box">

          <div className="success-box-header">
            <h1 className="success-alert-title">
              [ SUCCESS ]
            </h1>

            <h2 className="success-main-heading">
              Order Taken Successfully!
            </h2>

            <p className="success-sub-heading">
              Thank you for shopping at CUSAT Store
            </p>
          </div>

          <hr className="receipt-divider" />

          <div className="receipt-body-padding">

            <h3 className="receipt-section-title">
              <u>Order Details</u>
            </h3>

            <table width="100%">
              <tbody>
                <tr>
                  <td><b>Order ID:</b></td>
                  <td>
                    #CUSAT-{searchParams.get("order_id")}
                  </td>
                </tr>

                <tr>
                  <td><b>Customer:</b></td>
                  <td>{searchParams.get("name")}</td>
                </tr>

                <tr>
                  <td><b>Date:</b></td>
                  <td>{searchParams.get("date")}</td>
                </tr>

                <tr>
                  <td><b>Total:</b></td>
                  <td>
                    ₹{searchParams.get("total")}
                  </td>
                </tr>
              </tbody>
            </table>

            <br />

            <div className="next-steps-instruction-box">
              <b>Next Steps:</b>

              <ul>
                <li>
                  Your order has been recorded.
                </li>

                <li>
                  No online payment required.
                </li>

                <li>
                  Pay by Cash/UPI on delivery.
                </li>
              </ul>
            </div>

            <br />

            <Link
              to="/"
              className="btn btn-primary"
            >
              Continue Shopping
            </Link>

            {isLoggedIn && (
              <Link
                to="/orders"
                className="btn btn-secondary"
              >
                View My Orders
              </Link>
            )}

          </div>

        </div>

      </main>
    );
  }
return (
    <main className="container page-main">

      <h2 className="cart-page-main-title">
        Shopping Cart
      </h2>

      <hr className="title-underline-hr" />

      <div className="cart-layout-row-split">

        {/* Cart Items */}
        <div className="cart-items-column-left">

          <h3>Selected Items</h3>

          {cartItems.length === 0 ? (
            <p>Cart is empty</p>
          ) : (
            cartItems.map((item) => (
              <div key={item.id}>
                <h4>{item.name}</h4>

                <p>
                  Qty: {item.quantity}
                </p>

                <p>
                  ₹{item.price}
                </p>
              </div>
            ))
          )}

        </div>

        {/* Checkout */}
        <div className="cart-checkout-column-right">

          {!isLoggedIn && (
            <div className="auth-warning-alert-box">

              <b>
                Authentication Required
              </b>

              <br />

              Please login first.

              <br /><br />

              <Link
                to="/login"
                className="btn btn-primary"
              >
                Login
              </Link>

              {" "}

              <Link
                to="/register"
                className="btn btn-secondary"
              >
                Register
              </Link>

            </div>
          )}

          <form
            onSubmit={handleCheckout}
          >

            <input
              type="text"
              name="customer_name"
              placeholder="Full Name"
              value={formData.customer_name}
              onChange={handleChange}
              required
            />

            <input
              type="email"
              name="customer_email"
              placeholder="Email"
              value={formData.customer_email}
              onChange={handleChange}
              required
            />

            <input
              type="tel"
              name="customer_phone"
              placeholder="Phone Number"
              onChange={handleChange}
              required
            />

            <input
              type="text"
              name="department"
              placeholder="Department"
              onChange={handleChange}
              required
            />

            <input
              type="text"
              name="roll_number"
              placeholder="Roll Number"
              onChange={handleChange}
              required
            />

            <textarea
              name="delivery_address"
              placeholder="Delivery Address"
              onChange={handleChange}
              required
            />

            <div className="checkout-pricing-summary-box">

              <p>
                Cart Subtotal:
                ₹{subtotal.toFixed(2)}
              </p>

              <p>
                Delivery Fee:
                FREE
              </p>

              <h3>
                Total:
                ₹{subtotal.toFixed(2)}
              </h3>

            </div>

            <button
              type="submit"
              disabled={!isLoggedIn}
              className="btn btn-teal full-width-input-btn"
            >
              Place Order
              (Cash on Delivery)
            </button>

          </form>

        </div>

      </div>

    </main>
  );
}

export default Cart;