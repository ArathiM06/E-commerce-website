function Sidebar() {
  return (
    <div
      style={{
        width: "250px",
        height: "100vh",
        backgroundColor: "#1e293b",
        color: "white",
        padding: "20px",
      }}
    >
      <h2>CUSAT Store</h2>

      <ul style={{ listStyle: "none", padding: 0 }}>
        <li>📦 Products</li>
        <li>🛒 Orders</li>
        <li>👥 Users</li>
        <li>⚙️ Settings</li>
      </ul>
    </div>
  );
}

export default Sidebar;