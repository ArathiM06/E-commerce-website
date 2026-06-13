function Products() {
  const products = [
    { id: 1, name: "Laptop", price: 50000, stock: 10 },
    { id: 2, name: "Mouse", price: 500, stock: 50 },
    { id: 3, name: "Keyboard", price: 1500, stock: 20 }
  ];

  return (
    <div>
      <h1>Products Management</h1>

      <button>Add Product</button>

      <table border="1" cellPadding="10" style={{ marginTop: "20px" }}>
        <thead>
          <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Price</th>
            <th>Stock</th>
          </tr>
        </thead>

        <tbody>
          {products.map((product) => (
            <tr key={product.id}>
              <td>{product.id}</td>
              <td>{product.name}</td>
              <td>₹{product.price}</td>
              <td>{product.stock}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default Products;