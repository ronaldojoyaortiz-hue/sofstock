create database sofstock;
use sofstock;

create table roles (
  id_rol int auto_increment primary key,
  nombre_rol varchar(10),
  descripcion text
);

create table permisos (
  id_permiso int auto_increment primary key,
  codigo varchar(10),
  descripcion text
);

create table usuarios (
  id_usuario int auto_increment primary key,
  nombre varchar(150),
  correo varchar(30),
  contraseña varchar(20),
  telefono varchar(10),
  direccion text,
  id_rol int,
  activo boolean,
  fecha_registro timestamp default current_timestamp,
  fecha_actualizacion timestamp null default null,
  foreign key (id_rol) references roles(id_rol)
);


create table categorias (
  id_categoria int auto_increment primary key,
  nombre varchar(100),
  descripcion text,
  activa boolean,
  fecha_registro timestamp
);

create table proveedores (
  id_proveedor int auto_increment primary key,
  razon_social varchar(20) not null,
  nit varchar(20),
  correo varchar(30),
  telefono varchar(10),
  direccion text,
  activo boolean,
  fecha_registro timestamp
);

create table productos (
  id_producto int auto_increment primary key,
  nombre varchar(15),
  descripcion text,
  id_categoria int,
  id_proveedor int,
  stock_actual decimal(10,2),
  stock_minimo decimal(10,2),
  unidad_base varchar(10),
  precio_base decimal(10,2),
  activo boolean,
  fecha_registro timestamp default current_timestamp,
  fecha_actualizacion timestamp null default null,
  foreign key (id_categoria) references categorias(id_categoria),
  foreign key (id_proveedor) references proveedores(id_proveedor)
);

create table unidades_conversion (
  id_conversion int auto_increment primary key,
  id_producto int,
  unidad varchar(20),
  factor_conversion decimal(10,4),
  precio_unitario decimal(10,2),
  foreign key (id_producto) references productos(id_producto)
);

create table pedidos (
  id_pedido int auto_increment primary key,
  id_cliente int not null,
  id_vendedor int,
  tipo varchar(20),
  estado varchar(20),
  fecha_pedido timestamp default current_timestamp,
  fecha_entrega timestamp null default null,
  total decimal(12,2),
  notas text,
  foreign key (id_cliente) references usuarios(id_usuario),
  foreign key (id_vendedor) references usuarios(id_usuario)
);


create table detalle_pedido (
  id_detalle int auto_increment primary key,
  id_pedido int,
  cantidad decimal(10,2),
  unidad_usada varchar(20),
  precio_unitario decimal(10,2),
  subtotal decimal(12,2),
  foreign key (id_pedido) references pedidos(id_pedido)
);

create table movimientos_inventario (
  id_movimiento int auto_increment primary key,
  id_producto int,
  tipo_movimiento varchar(20),
  cantidad decimal(10,2),
  stock_resultante decimal(10,2),
  id_usuario int,
  id_pedido int,
  motivo text,
  fecha_movimiento timestamp,
  foreign key (id_producto) references productos(id_producto),
  foreign key (id_usuario) references usuarios(id_usuario),
  foreign key (id_pedido) references pedidos(id_pedido)
);

create table promociones (
  id_promocion int auto_increment primary key,
  nombre varchar(150),
  descripcion text,
  tipo_descuento varchar(50),
  valor_descuento decimal(10,2),
  fecha_inicio date,
  fecha_fin date,
  id_producto int,
  id_categoria int,
  activa boolean,
  foreign key (id_producto) references productos(id_producto),
  foreign key (id_categoria) references categorias(id_categoria)
);

create table alertas_stock (
  id_alerta int auto_increment primary key,
  id_producto int,
  nombre_alerta varchar(20),
  mensaje text,
  leida boolean,
  fecha_creacion timestamp,
  foreign key (id_producto) references productos(id_producto)
);
