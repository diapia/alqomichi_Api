/*
   PROYECTO: Base de Datos Administrativa - Alqomichi Petshop
   NRC:3657
   AUTOR: Cueva Cerna Diana
   MOTOR: SQL Server
*/
-- 1. CREACIÓN DE LA BASE DE DATOS 
USE master;
GO
IF DB_ID('AlqomichiDB') IS NOT NULL
BEGIN
    ALTER DATABASE AlqomichiDB SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE AlqomichiDB;
END
GO

CREATE DATABASE AlqomichiDB;
GO
USE AlqomichiDB;
GO

-- 2. CREACIÓN DE TABLAS, RESTRICCIONES (CONSTRAINTS) Y CLAVES PRIMARIAS (PK)

CREATE TABLE Categoria (
    CategoriaID INT IDENTITY(1,1) PRIMARY KEY,
    Nombre NVARCHAR(50) NOT NULL UNIQUE,
    Descripcion NVARCHAR(150) NULL
);
GO

CREATE TABLE Marca (
    MarcaID INT IDENTITY(1,1) PRIMARY KEY,
    Nombre NVARCHAR(50) NOT NULL UNIQUE
);
GO

CREATE TABLE Producto (
    ProductoID INT IDENTITY(1,1) PRIMARY KEY,
    Nombre NVARCHAR(100) NOT NULL,
    Especie NVARCHAR(20) NOT NULL CHECK (Especie IN ('Perro', 'Gato', 'Ambos')),
    Precio DECIMAL(10,2) NOT NULL CHECK (Precio > 0),
    PrecioAntiguo DECIMAL(10,2) NULL,
    Stock INT NOT NULL DEFAULT 0 CHECK (Stock >= 0),
    CategoriaID INT NOT NULL,
    MarcaID INT NOT NULL
);
GO

CREATE TABLE Cliente (
    ClienteID INT IDENTITY(1,1) PRIMARY KEY,
    NombreCompleto NVARCHAR(100) NOT NULL,
    Email NVARCHAR(100) NOT NULL UNIQUE,
    Telefono NVARCHAR(15) NULL,
    FechaRegistro DATETIME NOT NULL DEFAULT GETDATE()
);
GO

CREATE TABLE Pedido (
    PedidoID INT IDENTITY(1,1) PRIMARY KEY,
    ClienteID INT NOT NULL,
    FechaPedido DATETIME NOT NULL DEFAULT GETDATE(),
    Total DECIMAL(10,2) NOT NULL CHECK (Total >= 0),
    MetodoPago NVARCHAR(20) NOT NULL CHECK (MetodoPago IN ('Yape', 'Plin', 'Tarjeta')),
    Estado NVARCHAR(20) NOT NULL DEFAULT 'Pendiente'
);
GO

CREATE TABLE DetallePedido (
    DetalleID INT IDENTITY(1,1) PRIMARY KEY,
    PedidoID INT NOT NULL,
    ProductoID INT NOT NULL,
    Cantidad INT NOT NULL CHECK (Cantidad > 0),
    PrecioUnitario DECIMAL(10,2) NOT NULL CHECK (PrecioUnitario > 0)
);
GO
-- 3. CREACIÓN DE RELACIONES (CLAVES FORÁNEAS - FK)

ALTER TABLE Producto
ADD CONSTRAINT FK_Producto_Categoria FOREIGN KEY (CategoriaID) REFERENCES Categoria(CategoriaID),
    CONSTRAINT FK_Producto_Marca FOREIGN KEY (MarcaID) REFERENCES Marca(MarcaID);
GO

ALTER TABLE Pedido
ADD CONSTRAINT FK_Pedido_Cliente FOREIGN KEY (ClienteID) REFERENCES Cliente(ClienteID);
GO

ALTER TABLE DetallePedido
ADD CONSTRAINT FK_Detalle_Pedido FOREIGN KEY (PedidoID) REFERENCES Pedido(PedidoID),
    CONSTRAINT FK_Detalle_Producto FOREIGN KEY (ProductoID) REFERENCES Producto(ProductoID);
GO

-- 4. CREACIÓN DE ÍNDICES PARA OPTIMIZAR BÚSQUEDAS ADMINISTRATIVAS

-- Acelera la búsqueda de productos filtrados por categoría en la tienda
CREATE NONCLUSTERED INDEX IX_Producto_Categoria ON Producto(CategoriaID);
GO
-- Acelera la carga del historial de compras de un cliente específico
CREATE NONCLUSTERED INDEX IX_Pedido_Cliente ON Pedido(ClienteID);
GO

-- 5. PROCEDIMIENTO ALMACENADO (SP) CON LÓGICA DE NEGOCIO

-- SP para actualizar el inventario de un producto tras una revisión administrativa

CREATE PROCEDURE sp_ActualizarStockProducto
    @ProductoID INT,
    @NuevoStock INT
AS
BEGIN
    IF EXISTS (SELECT 1 FROM Producto WHERE ProductoID = @ProductoID)
    BEGIN
        IF @NuevoStock >= 0
        BEGIN
            UPDATE Producto SET Stock = @NuevoStock WHERE ProductoID = @ProductoID;
            RETURN 1; -- Éxito: Stock actualizado
        END
        ELSE
        BEGIN
            RETURN -1; -- Falla: El stock no puede ser negativo
        END
    END
    ELSE
    BEGIN
        RETURN 0; -- Falla: Producto no encontrado
    END
END;
GO

-- 6. POBLACIÓN DE DATOS (MÁXIMO 10 REGISTROS POR TABLA)
-- Inserción de Categorías
INSERT INTO Categoria (Nombre, Descripcion) VALUES 
('Alimento', 'Comida seca, húmeda y snacks'),
('Accesorios', 'Correas, arneses, collares y ropa'),
('Juguetes', 'Rascadores, pelotas y mordedores'),
('Higiene', 'Shampoo, arena y cepillos'),
('Camas', 'Colchonetas y casas para interior');
GO

-- Inserción de Marcas
INSERT INTO Marca (Nombre) VALUES 
('MichiFit'), ('Guau&Co'), ('Bigotes'), ('HappyTail'), ('PetVital');
GO

-- Inserción de Productos (Basados en tu frontend HTML)
INSERT INTO Producto (Nombre, Especie, Precio, PrecioAntiguo, Stock, CategoriaID, MarcaID) VALUES 
('Alimento adulto sabor pollo 15 kg', 'Perro', 159.90, 199.90, 25, 1, 2),
('Snack cremoso de atún (pack x12)', 'Gato', 31.40, 34.90, 50, 1, 1),
('Arnés acolchado reflectivo', 'Perro', 49.90, NULL, 15, 2, 4),
('Rascador torre con juguetes', 'Gato', 118.90, 139.90, 10, 3, 3),
('Arena sanitaria aglomerante 10kg', 'Gato', 45.00, NULL, 30, 4, 5);
GO

-- Inserción de Clientes
INSERT INTO Cliente (NombreCompleto, Email, Telefono) VALUES 
('Andrea López', 'andrea@mail.com', '999888777'),
('Diego Ramírez', 'diego@mail.com', '999111222'),
('Valeria Torres', 'valeria@mail.com', '999333444');
GO

-- Inserción de Pedidos
INSERT INTO Pedido (ClienteID, Total, MetodoPago, Estado) VALUES 
(1, 159.90, 'Yape', 'Completado'),
(2, 150.30, 'Plin', 'Pendiente'),
(3, 49.90, 'Tarjeta', 'Completado');
GO

-- Inserción de Detalles de Pedido
INSERT INTO DetallePedido (PedidoID, ProductoID, Cantidad, PrecioUnitario) VALUES 
(1, 1, 1, 159.90), -- Andrea compró 1 alimento de perro
(2, 4, 1, 118.90), -- Diego compró 1 rascador
(2, 2, 1, 31.40),  -- Diego compró 1 snack de gato
(3, 3, 1, 49.90);  -- Valeria compró 1 arnés
GO

-- 7. PRUEBA DEL PROCEDIMIENTO ALMACENADO (OPCIONAL PARA VERIFICACIÓN)

DECLARE @Resultado INT;
-- Intentamos actualizar el stock del producto 2 (Snack cremoso) a 100 unidades
EXEC @Resultado = sp_ActualizarStockProducto @ProductoID = 2, @NuevoStock = 100;
SELECT @Resultado AS EstadoActualizacion;
GO
