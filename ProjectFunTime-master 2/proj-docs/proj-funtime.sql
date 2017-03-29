--  Drop any existing tables. Any errors are ignored.
--
DROP TABLE IF EXISTS MadeOf;
DROP TABLE IF EXISTS Contains;
DROP TABLE IF EXISTS Ingredient;
DROP TABLE IF EXISTS Menuitem;
DROP TABLE IF EXISTS Invoice;
DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS Chef;
DROP TABLE IF EXISTS Users;
--
-- Add each table.
--
CREATE TABLE Users
	(userName VARCHAR(20),
	type VARCHAR(8) NOT NULL,
	password VARCHAR(20) NOT NULL,
	name VARCHAR(40) NOT NULL,
	phone CHAR(10) NULL,
	address VARCHAR(40) NULL,
	createDate DATE NOT NULL,
	u_deleted CHAR(1) NOT NULL,
	PRIMARY KEY (userName));

CREATE TABLE Chef
	(chef_userName VARCHAR(20),
	admin_userName VARCHAR(20) NOT NULL,
	employee_id INT AUTO_INCREMENT,	
	ssNum INT(9) NULL,
	UNIQUE (employee_id),
	UNIQUE (ssNum),
	PRIMARY KEY (chef_userName),
	FOREIGN KEY (chef_userName) REFERENCES Users(userName) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (admin_userName) REFERENCES Users(userName) ON UPDATE CASCADE); 

CREATE TABLE Orders
	(order_id INT AUTO_INCREMENT,
	customer_userName VARCHAR(20) NOT NULL,
	chef_userName VARCHAR(20) NULL,
	orderdate DATE NOT NULL,
	cookeddate DATE NULL, 
	paymentStatus VARCHAR(20) NOT NULL,
    cookedStatus VARCHAR(20) NOT NULL,
	PRIMARY KEY (order_id),
	FOREIGN KEY (chef_userName) REFERENCES Chef(chef_userName) ON DELETE SET NULL ON UPDATE CASCADE, 
	FOREIGN KEY (customer_userName) REFERENCES Users(userName) ON UPDATE CASCADE);

CREATE TABLE Invoice
	(order_id INT NOT NULL,
	customer_userName VARCHAR(20) NOT NULL,
	cost DECIMAL(5, 2) NOT NULL,
	createdate DATE NULL,
	paymentType VARCHAR(20) NULL,
	PRIMARY KEY (order_id),
	FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON UPDATE CASCADE,
	FOREIGN KEY (customer_userName) REFERENCES Users(userName) ON UPDATE CASCADE);

CREATE TABLE Menuitem
	(menu_id INT AUTO_INCREMENT,
	name VARCHAR(80),
	price DECIMAL(5, 2) NOT NULL,
	category VARCHAR(30) NOT NULL,
	description VARCHAR(100) NULL,
	quantity INT NOT NULL,
	m_deleted CHAR(1) NOT NULL,
	UNIQUE (menu_id),
	PRIMARY KEY (name));

CREATE TABLE Ingredient
	(ing_id INT AUTO_INCREMENT,
	name VARCHAR(20),
	type VARCHAR(30) NOT NULL,
	i_deleted CHAR(1) NOT NULL,
	UNIQUE (ing_id),
	PRIMARY KEY(name));

CREATE TABLE Contains
	(order_id INT NOT NULL,
	name VARCHAR(80) NOT NULL,
	qty INT NOT NULL,
	PRIMARY KEY (order_id, name),
	FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON UPDATE CASCADE,
	FOREIGN KEY (name) REFERENCES Menuitem(name) ON UPDATE CASCADE);

CREATE TABLE MadeOf
	(menuItem_name VARCHAR(80) NOT NULL,
	ingredient_name VARCHAR(80) NOT NULL,
	PRIMARY KEY (menuItem_name, ingredient_name),
	FOREIGN KEY (ingredient_name) REFERENCES Ingredient(name) ON UPDATE CASCADE,
	FOREIGN KEY (menuItem_name) REFERENCES Menuitem(name) ON UPDATE CASCADE);
--
-- Add in tuples
--
insert into Users values ('omgitzme', 'admin', 'hihi', 'Harry Zhi', '6042312412', '6223 Bateman St.', DATE '1961-01-01', 'F');

insert into Users values ('Wewic', 'chef', 'password', 'Eric Wu', '6041232040', '604 Vancouver Ave.', DATE '1995-04-18', 'F');

insert into Users values ('ptlin', 'customer', 'greetings', 'Patrick Lin', '6041235235', '6224 Bateman St.', DATE '1961-01-01', 'F');

insert into Users values ('quanbao', 'customer', 'ptran', 'Paul Tran', '6041252341', '6225 Bateman St.', DATE '1961-01-02', 'F');

insert into Users values ('newuser', 'customer', 'random', 'John Smith', '8329103291', '1513 Las Vegas St.', DATE '2016-11-01', 'F');

insert into Users values ('newuser2', 'customer', 'random', 'John Doe', '8329123291', '1515 Las Vegas St.', DATE '2016-11-01', 'F');

insert into Users values ('chef1', 'chef', 'cooking', 'Rordon Gamsey', '8329192919', '1514 Las Vegas St.', DATE '2016-11-02', 'F');

insert into Users values ('chef2', 'chef', 'cooking', 'Jamie Oliver', '8329192919', '1514 Las Vegas St.', DATE '2016-11-02', 'F');


insert into Chef (chef_userName, admin_userName, ssNum) values ('chef1', 'omgitzme', 234213205);

insert into Chef (chef_userName, admin_userName, ssNum) values ('Wewic', 'omgitzme', 234013005);

insert into Chef (chef_userName, admin_userName, ssNum) values ('chef2', 'omgitzme', 234013035);


insert into Orders (customer_userName, chef_userName, orderdate, cookeddate, paymentStatus, cookedStatus) values ('newuser', NULL, DATE '2016-11-01', DATE '2016-11-01', 'paid', 'open');

insert into Orders (customer_userName, chef_userName, orderdate, cookeddate, paymentStatus, cookedStatus) values ('ptlin', 'Wewic', DATE '2016-11-02', DATE '2016-11-01', 'paid', 'in progress');

insert into Orders (customer_userName, chef_userName, orderdate, cookeddate, paymentStatus, cookedStatus) values ('quanbao', 'Wewic', DATE '2016-11-01', DATE '2016-11-01', 'paid', 'cooked');

insert into Orders (customer_userName, chef_userName, orderdate, cookeddate, paymentStatus, cookedStatus) values ('newuser2', 'chef2', DATE '2016-11-01', DATE '2016-11-01', 'paid', 'cooked');


insert into Invoice values (1, 'newuser', 48.85, DATE '2016-11-01', 'credit');

insert into Invoice values (2, 'ptlin', 27.00, DATE '2016-11-02', 'cash');

insert into Invoice values (3, 'quanbao', 77.50, DATE '2016-11-02', 'cash');


insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Shrimp Fried Rice', 11.00, 'Entree', 'Egg fried rice with shrimps, what else could it be...', 50, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Deluxe Burger', 8.95, 'Entree', 'Hamburger with lettuce, tomato, pickles, and a beef patty', 55, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Ice cream Ramen', 4.00, 'Dessert', 'Noodles in a cone!', 50, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Beef Smoothie', 6.50, 'Drink', 'A healthy blend of greens and beef to ensure you hit your protein macros.', 66, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Deep-fried Protein Powder', 5.95, 'Entree', 'Anything is better deep fried!', 72, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Sushi Surprise', 9.00, 'Entree', 'Surprise! Sushi again...', 53, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Frozen Mango', 2.50, 'Dessert', 'People always order mango flavoured drinks, why not just have a mango?', 36, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Dog Chow', 6.45, 'Entree', 'Ever wondered what man''s best friend eats?', 65, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Tropical Delight', 4.50, 'Drink', 'A mixture of tropical fruits that will send you to a tropical paradise!', 55, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Mushroom Forest', 10.50, 'Entree', 'An assortment of mushrooms seasoned with herbs.', 73, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Frozen Fire Ball', 13.99, 'Other', 'Ice cream made from Sichuan pepper corn soup wrapped in mochi skin.', 50, 'F');

insert into Menuitem (name, price, category, description, quantity, m_deleted) values ('Zesty Nachos', 9.99, 'Appetizer', 'Nacho chips drizzled with chilli cheese, olives, sour cream, and fresh guacamole.', 50, 'F');


insert into Ingredient (name, type, i_deleted) values ('Lettuce', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Tomato', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Burger Buns', 'Grain','F');

insert into Ingredient (name, type, i_deleted) values ('Beef Patty', 'Meat','F');

insert into Ingredient (name, type, i_deleted) values ('Rice', 'Grain', 'F');

insert into Ingredient (name, type, i_deleted) values ('Egg', 'Meat', 'F');

insert into Ingredient (name, type, i_deleted) values ('Shrimp', 'Meat', 'F');

insert into Ingredient (name, type, i_deleted) values ('Ramen', 'Grain', 'F');

insert into Ingredient (name, type, i_deleted) values ('Ice cream', 'Dairy', 'F');

insert into Ingredient (name, type, i_deleted) values ('Protein', 'Meat', 'F');

insert into Ingredient (name, type, i_deleted) values ('Salmon', 'Meat', 'F');

insert into Ingredient (name, type, i_deleted) values ('Seaweed', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Mango', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Ground Beef', 'Meat', 'F');

insert into Ingredient (name, type, i_deleted) values ('Bell Peppers', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Pineapple', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Coconut', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Shiitake Mushroom', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('White Mushroom', 'Vegetable', 'F');

insert into Ingredient (name, type, i_deleted) values ('Portabello Mushroom', 'Vegetable', 'F');


insert into Contains values (1, 'Shrimp Fried Rice', 2);

insert into Contains values (1, 'Deluxe Burger', 3);

insert into Contains values (2, 'Ice cream Ramen', 3);

insert into Contains values (2, 'Tropical Delight', 1);

insert into Contains values (2, 'Mushroom Forest', 1);

insert into Contains values (3, 'Deep-fried Protein Powder', 10);

insert into Contains values (3, 'Sushi Surprise', 2);

insert into Contains values (4, 'Deluxe Burger', 2);


insert into MadeOf values ('Shrimp Fried Rice', 'Rice');

insert into MadeOf values ('Shrimp Fried Rice', 'Egg');

insert into MadeOf values ('Shrimp Fried Rice', 'Shrimp');

insert into MadeOf values ('Deluxe Burger', 'Burger Buns');

insert into MadeOf values ('Deluxe Burger', 'Lettuce');

insert into MadeOf values ('Deluxe Burger', 'Tomato');

insert into MadeOf values ('Deluxe Burger', 'Beef Patty');

insert into MadeOf values ('Ice cream Ramen', 'Ramen');

insert into MadeOf values ('Ice cream Ramen', 'Ice cream');

insert into MadeOf values ('Beef Smoothie', 'Beef Patty');

insert into MadeOf values ('Deep-fried Protein Powder', 'Protein');

insert into MadeOf values ('Sushi Surprise', 'Salmon');

insert into MadeOf values ('Sushi Surprise', 'Rice');

insert into MadeOf values ('Sushi Surprise', 'Seaweed');

insert into MadeOf values ('Frozen Mango', 'Mango');

insert into MadeOf values ('Dog Chow', 'Ground Beef');

insert into MadeOf values ('Dog Chow', 'Bell Peppers');

insert into MadeOf values ('Tropical Delight', 'Mango');

insert into MadeOf values ('Tropical Delight', 'Coconut');

insert into MadeOf values ('Tropical Delight', 'Pineapple');

insert into MadeOf values ('Mushroom Forest', 'Shiitake Mushroom');

insert into MadeOf values ('Mushroom Forest', 'White Mushroom');

insert into MadeOf values ('Mushroom Forest', 'Portabello Mushroom');

insert into MadeOf values ('Frozen Fire Ball', 'Ice cream');
	
insert into MadeOf values ('Zesty Nachos', 'Ground Beef')	
