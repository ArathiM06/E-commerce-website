from fastapi import FastAPI, Depends, HTTPException, status, Header, Form
from fastapi.middleware.cors import CORSMiddleware
import hashlib
import datetime
from typing import List, Optional
from pydantic import BaseModel

import database
from database import get_db, get_next_sequence_value

# Initialize Database tables/indexes
database.init_db()

app = FastAPI(title="CUSAT Store API")

# Enable CORS for frontend compatibility
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Helper function to hash passwords
def hash_password(password: str) -> str:
    return hashlib.sha256(password.encode()).hexdigest()

# Pydantic Schemas
class UserRegister(BaseModel):
    name: str
    email: str
    password: str

class UserLogin(BaseModel):
    email: str
    password: str

class ProductCreate(BaseModel):
    name: str
    price: float
    category: str
    description: str
    image_url: Optional[str] = None

class CartItemInput(BaseModel):
    product_id: int
    quantity: int

class OrderCreate(BaseModel):
    user_id: Optional[int] = None
    customer_name: str
    customer_email: str
    customer_phone: str
    department: str
    roll_number: str
    delivery_address: str
    items: List[CartItemInput]

class OrderStatusUpdate(BaseModel):
    status: str

# Initial mock products catalog fallback
INITIAL_CATALOG_PRODUCTS = [
    {
        "id": 1,
        "name": "CUSAT Premium Hoodie",
        "price": 850.00,
        "category": "Apparel",
        "description": "Navy blue hoodie with the official CUSAT crest printed in white and gold. Standard fit.",
        "image_url": "https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 2,
        "name": "CUSAT Eco-Friendly Canvas Tote Bag",
        "price": 220.00,
        "category": "Apparel",
        "description": "Durable natural cotton canvas tote bag with the official CUSAT crest. Perfect for carrying notebooks, laptops, and lab essentials around campus.",
        "image_url": "https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 3,
        "name": "Engineering Physics Textbook",
        "price": 520.00,
        "category": "Textbooks",
        "description": "Prescribed textbook for CUSAT B.Tech first-year syllabus. Fully updated edition.",
        "image_url": "https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 4,
        "name": "CUSAT Official Polo T-Shirt",
        "price": 499.00,
        "category": "Apparel",
        "description": "Premium navy blue polo t-shirt with official CUSAT crest embroidery and collar trim. Made of breathable cotton pique fabric.",
        "image_url": "https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 5,
        "name": "Maker Kit (Arduino Uno & Sensors)",
        "price": 1250.00,
        "category": "Tech",
        "description": "Starter electronics kit containing an Arduino Uno board, breadboard, jumper wires, LEDs, and standard sensors.",
        "image_url": "https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 6,
        "name": "A2 Drawing Board & T-Square",
        "price": 950.00,
        "category": "Stationery",
        "description": "Durable wooden engineering drawing board along with a precise 60cm T-Square rule. Essential for Engineering Graphics.",
        "image_url": "https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 7,
        "name": "CUSAT Insulated Stainless Steel Flask",
        "price": 349.00,
        "category": "Accessories",
        "description": "Double-wall insulated 750ml stainless steel flask with laser-engraved CUSAT logo. Keeps beverages cold or hot for 12 hours.",
        "image_url": "https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 8,
        "name": "CUSAT Official Campus Backpack",
        "price": 899.00,
        "category": "Apparel",
        "description": "Water-resistant navy blue backpack with padded laptop compartment, multiple organizers, and reflective CUSAT crest.",
        "image_url": "https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 9,
        "name": "CUSAT Executive Pen & Notebook Set",
        "price": 299.00,
        "category": "Stationery",
        "description": "Hardbound A5 notebook with gold-embossed CUSAT logo paired with a sleek metallic rollerball pen.",
        "image_url": "https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 10,
        "name": "Casio FX-991CW ClassWiz Scientific Calculator",
        "price": 1295.00,
        "category": "Tech",
        "description": "Advanced non-programmable scientific calculator prescribed for CUSAT B.Tech & Engineering examinations.",
        "image_url": "https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?auto=format&fit=crop&q=80&w=400"
    },
    {
        "id": 11,
        "name": "CUSAT Varsity Baseball Cap",
        "price": 275.00,
        "category": "Apparel",
        "description": "Adjustable cotton twill cap in deep navy blue with 3D embroidered CUSAT lettering.",
        "image_url": "https://images.unsplash.com/photo-1588850561407-ed78c282e89b?auto=format&fit=crop&q=80&w=400"
    }
]

# Seed dynamic initial mock products and keep DB strictly in sync
def seed_products(db, force=False):
    try:
        valid_ids = [item["id"] for item in INITIAL_CATALOG_PRODUCTS]
        # Clean up any removed items (e.g. Mug) from MongoDB database
        db.products.delete_many({"_id": {"$nin": valid_ids}})

        for item in INITIAL_CATALOG_PRODUCTS:
            prod_doc = {
                "_id": item["id"],
                "name": item["name"],
                "price": item["price"],
                "category": item["category"],
                "description": item["description"],
                "image_url": item["image_url"]
            }
            db.products.replace_one({"_id": item["id"]}, prod_doc, upsert=True)

        db.counters.replace_one(
            {"_id": "product_id"},
            {"_id": "product_id", "sequence_value": max(valid_ids)},
            upsert=True
        )
        print(f"Database sync completed with {len(INITIAL_CATALOG_PRODUCTS)} catalog products.")
    except Exception as e:
        print(f"Notice: Product seeding skipped or deferred: {e}")

@app.on_event("startup")
def on_startup():
    try:
        db = next(get_db())
        seed_products(db, force=True)
    except Exception as e:
        print(f"Notice: Startup DB check skipped: {e}")

@app.post("/api/register")
def register(user: UserRegister, db = Depends(get_db)):
    # Check if user already exists
    db_user = db.users.find_one({"email": user.email})
    if db_user:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    # Check if this email should automatically be an admin
    is_admin = False
    if user.email.lower() == "admin@cusat.ac.in":
        is_admin = True
        
    new_user_id = get_next_sequence_value("user_id")
    new_user = {
        "_id": new_user_id,
        "name": user.name,
        "email": user.email,
        "password_hash": hash_password(user.password),
        "is_admin": is_admin
    }
    
    try:
        db.users.insert_one(new_user)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Email already registered")
        
    return {
        "id": new_user_id,
        "name": user.name,
        "email": user.email,
        "is_admin": is_admin
    }

@app.post("/api/login")
def login(user: UserLogin, db = Depends(get_db)):
    db_user = db.users.find_one({"email": user.email})
    if not db_user or db_user["password_hash"] != hash_password(user.password):
        raise HTTPException(status_code=400, detail="Invalid email or password")
    
    return {
        "id": db_user["_id"],
        "name": db_user["name"],
        "email": db_user["email"],
        "is_admin": db_user["is_admin"]
    }

@app.get("/api/products")
def get_products(category: Optional[str] = None, db = Depends(get_db)):
    try:
        # Auto sync catalog on fetch
        try:
            seed_products(db, force=True)
        except Exception:
            pass

        query = {}
        if category and category != "All":
            query["category"] = category
            
        products = list(db.products.find(query))
        if products and len(products) > 0:
            for p in products:
                p["id"] = p.pop("_id")
            return products
    except Exception as e:
        print(f"Notice: Falling back to default products catalog: {e}")

    # Static fallback products if database is unreachable or empty
    results = INITIAL_CATALOG_PRODUCTS
    if category and category != "All":
        results = [p for p in results if p.get("category", "").lower() == category.lower()]
    return results

@app.post("/api/seed")
def trigger_seed(db = Depends(get_db)):
    """Manual endpoint to seed/sync products anytime."""
    seed_products(db, force=True)
    try:
        count = db.products.count_documents({})
    except Exception:
        count = len(INITIAL_CATALOG_PRODUCTS)
    return {"status": "ok", "message": f"Database seeded with {count} products"}


@app.post("/api/products")
def create_product(product: ProductCreate, x_admin_token: Optional[str] = Header(None), db = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
         
    new_id = get_next_sequence_value("product_id")
    new_product = {
        "_id": new_id,
        "name": product.name,
        "price": product.price,
        "category": product.category,
        "description": product.description,
        "image_url": product.image_url or "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=400"
    }
    
    db.products.insert_one(new_product)
    
    # Map _id to id for response
    new_product["id"] = new_product.pop("_id")
    return new_product

@app.delete("/api/products/{product_id}")
def delete_product(product_id: int, x_admin_token: Optional[str] = Header(None), db = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
         
    result = db.products.delete_one({"_id": product_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Product not found")
        
    return {"message": "Product deleted successfully"}

@app.post("/api/orders")
def create_order(order_data: OrderCreate, db = Depends(get_db)):
    if not order_data.items:
        raise HTTPException(status_code=400, detail="Cart is empty")
        
    total_amount = 0.0
    order_items_to_create = []
    
    # Validate items and calculate total amount
    for item in order_data.items:
        product = db.products.find_one({"_id": item.product_id})
        if not product:
            raise HTTPException(status_code=400, detail=f"Product with ID {item.product_id} not found")
        
        item_total = product["price"] * item.quantity
        total_amount += item_total
        
        order_items_to_create.append({
            "product_id": product["_id"],
            "product_name": product["name"],
            "quantity": item.quantity,
            "price": product["price"]
        })
        
    new_order_id = get_next_sequence_value("order_id")
    created_at = datetime.datetime.utcnow()
    
    order_doc = {
        "_id": new_order_id,
        "user_id": order_data.user_id,
        "customer_name": order_data.customer_name,
        "customer_email": order_data.customer_email,
        "customer_phone": order_data.customer_phone,
        "department": order_data.department,
        "roll_number": order_data.roll_number,
        "delivery_address": order_data.delivery_address,
        "items": order_items_to_create,
        "total_amount": total_amount,
        "status": "Pending",
        "created_at": created_at.strftime("%Y-%m-%d %H:%M:%S")
    }
    
    db.orders.insert_one(order_doc)
    order_doc["id"] = order_doc.pop("_id")
    return order_doc

@app.get("/api/orders")
def get_all_orders(x_admin_token: Optional[str] = Header(None), db = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
    orders = list(db.orders.find())
    for o in orders:
        o["id"] = o.pop("_id")
    return orders

@app.get("/api/orders/user/{user_id}")
def get_user_orders(user_id: int, db = Depends(get_db)):
    orders = list(db.orders.find({"user_id": user_id}))
    for o in orders:
        o["id"] = o.pop("_id")
    return orders

@app.post("/api/auth/login")
def auth_login(username: str = Form(...), password: str = Form(...), db = Depends(get_db)):
    db_user = db.users.find_one({"email": username})
    if not db_user or db_user["password_hash"] != hash_password(password):
        raise HTTPException(status_code=400, detail="Invalid email or password")
    
    is_admin = db_user.get("is_admin", False)
    token = "admin_secret_token_cusat" if is_admin else f"user_secret_token_{db_user['_id']}"
    
    return {
        "access_token": token,
        "token_type": "bearer",
        "user": {
            "id": db_user["_id"],
            "name": db_user["name"],
            "email": db_user["email"],
            "is_admin": is_admin
        }
    }

@app.post("/api/auth/register", status_code=status.HTTP_201_CREATED)
def auth_register(user: UserRegister, db = Depends(get_db)):
    db_user = db.users.find_one({"email": user.email})
    if db_user:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    is_admin = False
    if user.email.lower() == "admin@cusat.ac.in":
        is_admin = True
        
    new_user_id = get_next_sequence_value("user_id")
    new_user = {
        "_id": new_user_id,
        "name": user.name,
        "email": user.email,
        "password_hash": hash_password(user.password),
        "is_admin": is_admin
    }
    
    try:
        db.users.insert_one(new_user)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Email already registered")
        
    return {
        "id": new_user_id,
        "name": user.name,
        "email": user.email,
        "is_admin": is_admin
    }