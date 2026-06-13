import { useState } from "react";
import Sidebar from "./components/Sidebar";
import Products from "./pages/Products";
import Orders from "./pages/Orders";
import Users from "./pages/Users";

function App() {
  const [currentPage, setCurrentPage] = useState("products");

  return (
    <div style={{ display: "flex" }}>
      <Sidebar setCurrentPage={setCurrentPage} />

      <div style={{ padding: "20px" }}>
        {currentPage === "products" && <Products />}
        {currentPage === "orders" && <Orders />}
        {currentPage === "users" && <Users />}
      </div>
    </div>
  );
}

export default App;