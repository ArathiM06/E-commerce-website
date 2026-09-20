import os
import certifi
from pymongo import MongoClient, ReturnDocument

# MongoDB Connection URL (supports cloud MongoDB Atlas via MONGODB_URL env var)
MONGODB_URL = os.getenv("MONGODB_URL", "mongodb://localhost:27017")

# Create MongoClient with TLS certificate verification
if "mongodb+srv://" in MONGODB_URL:
    client = MongoClient(MONGODB_URL, tlsCAFile=certifi.where())
else:
    client = MongoClient(MONGODB_URL)

# Access database 'cusat_store'
try:
    db = client.get_default_database()
    if db is None:
        db = client["cusat_store"]
except Exception:
    db = client["cusat_store"]

def get_next_sequence_value(sequence_name: str) -> int:
    """
    Simulate auto-incrementing integer IDs in MongoDB using a counters collection.
    """
    result = db.counters.find_one_and_update(
        {"_id": sequence_name},
        {"$inc": {"sequence_value": 1}},
        upsert=True,
        return_document=ReturnDocument.AFTER
    )
    return result["sequence_value"]

def init_db():
    """
    Initialize database indexes safely without crashing the server if DB is connecting.
    """
    try:
        db.users.create_index("email", unique=True)
        print("Database indexes initialized successfully.")
    except Exception as e:
        print(f"Notice: MongoDB index initialization deferred or skipped: {e}")

def get_db():
    """
    FastAPI dependency yielding the MongoDB database instance.
    """
    try:
        yield db
    finally:
        # Pymongo client manages connection pooling automatically,
        # so we do not close the client/connection here.
        pass
