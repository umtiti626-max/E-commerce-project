-- db.sql
CREATE TABLE IF NOT EXISTS products (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  brand TEXT,
  category TEXT,
  description TEXT,
  specs JSONB,
  price NUMERIC(10,2),
  stock INT DEFAULT 0,
  image_url TEXT,
  popularity INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT now()
);

CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  name TEXT,
  email TEXT UNIQUE,
  password_hash TEXT,
  role TEXT DEFAULT 'customer',
  profile_image TEXT
);

CREATE TABLE IF NOT EXISTS orders (
  id SERIAL PRIMARY KEY,
  items JSONB,
  shipping JSONB,
  created_at TIMESTAMP DEFAULT now()
);

-- sample seed
INSERT INTO products (name, brand, category, description, specs, price, stock, image_url, popularity)
VALUES
('iPhone 15 Pro','Apple','Phones','Flagship Apple phone', '{"ram":"8GB","storage":"256GB","processor":"A17"}', 1199.00, 10, '', 100),
('Galaxy S24 Ultra','Samsung','Phones','Latest Galaxy flagship', '{"ram":"12GB","storage":"512GB","processor":"Snapdragon 8 Gen 3"}', 1299.00, 15, '', 95),
('Xiaomi 13 Pro','Xiaomi','Phones','High value flagship', '{"ram":"12GB","storage":"256GB","processor":"Snapdragon 8 Gen 2"}', 899.00, 8, '', 75);
