import Sidebar from "./components/Sidebar";

function App() {
  return (
    <div style={{ display: "flex" }}>
      <Sidebar />

      <div style={{ padding: "20px" }}>
        <h1>CUSAT Store Admin Dashboard</h1>
        <p>Manage products and orders</p>
      </div>
    </div>
  );
}

export default App;