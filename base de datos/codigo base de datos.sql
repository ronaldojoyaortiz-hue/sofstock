-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema sofstock
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `categorias`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categorias` ;

CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NULL DEFAULT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `activa` TINYINT(1) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_categoria`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `proveedores`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `proveedores` ;

CREATE TABLE IF NOT EXISTS `proveedores` (
  `id_proveedor` INT(11) NOT NULL AUTO_INCREMENT,
  `razon_social` VARCHAR(20) NOT NULL,
  `nit` VARCHAR(20) NULL DEFAULT NULL,
  `correo` VARCHAR(30) NULL DEFAULT NULL,
  `telefono` VARCHAR(10) NULL DEFAULT NULL,
  `direccion` TEXT NULL DEFAULT NULL,
  `activo` TINYINT(1) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_proveedor`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `productos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `productos` ;

CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(15) NULL DEFAULT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `id_categoria` INT(11) NULL DEFAULT NULL,
  `id_proveedor` INT(11) NULL DEFAULT NULL,
  `stock_actual` DECIMAL(10,2) NULL DEFAULT NULL,
  `stock_minimo` DECIMAL(10,2) NULL DEFAULT NULL,
  `unidad_base` VARCHAR(10) NULL DEFAULT NULL,
  `precio_base` DECIMAL(10,2) NULL DEFAULT NULL,
  `activo` TINYINT(1) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_producto`),
  INDEX `id_categoria` (`id_categoria` ASC) VISIBLE,
  INDEX `id_proveedor` (`id_proveedor` ASC) VISIBLE,
  CONSTRAINT `productos_ibfk_1`
    FOREIGN KEY (`id_categoria`)
    REFERENCES `categorias` (`id_categoria`),
  CONSTRAINT `productos_ibfk_2`
    FOREIGN KEY (`id_proveedor`)
    REFERENCES `proveedores` (`id_proveedor`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `alertas_stock`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `alertas_stock` ;

CREATE TABLE IF NOT EXISTS `alertas_stock` (
  `id_alerta` INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto` INT(11) NULL DEFAULT NULL,
  `nombre_alerta` VARCHAR(20) NULL DEFAULT NULL,
  `mensaje` TEXT NULL DEFAULT NULL,
  `leida` TINYINT(1) NULL DEFAULT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_alerta`),
  INDEX `id_producto` (`id_producto` ASC) VISIBLE,
  CONSTRAINT `alertas_stock_ibfk_1`
    FOREIGN KEY (`id_producto`)
    REFERENCES `productos` (`id_producto`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `roles` ;

CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` VARCHAR(10) NULL DEFAULT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_rol`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuarios` ;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NULL DEFAULT NULL,
  `correo` VARCHAR(30) NULL DEFAULT NULL,
  `contraseña` VARCHAR(20) NULL DEFAULT NULL,
  `telefono` VARCHAR(10) NULL DEFAULT NULL,
  `direccion` TEXT NULL DEFAULT NULL,
  `id_rol` INT(11) NULL DEFAULT NULL,
  `activo` TINYINT(1) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  INDEX `id_rol` (`id_rol` ASC) VISIBLE,
  CONSTRAINT `usuarios_ibfk_1`
    FOREIGN KEY (`id_rol`)
    REFERENCES `roles` (`id_rol`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `pedidos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pedidos` ;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id_pedido` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` INT(11) NOT NULL,
  `id_vendedor` INT(11) NULL DEFAULT NULL,
  `tipo` VARCHAR(20) NULL DEFAULT NULL,
  `estado` VARCHAR(20) NULL DEFAULT NULL,
  `fecha_pedido` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_entrega` TIMESTAMP NULL DEFAULT NULL,
  `total` DECIMAL(12,2) NULL DEFAULT NULL,
  `notas` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_pedido`),
  INDEX `id_cliente` (`id_cliente` ASC) VISIBLE,
  INDEX `id_vendedor` (`id_vendedor` ASC) VISIBLE,
  CONSTRAINT `pedidos_ibfk_1`
    FOREIGN KEY (`id_cliente`)
    REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `pedidos_ibfk_2`
    FOREIGN KEY (`id_vendedor`)
    REFERENCES `usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `detalle_pedido`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `detalle_pedido` ;

CREATE TABLE IF NOT EXISTS `detalle_pedido` (
  `id_detalle` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` INT(11) NULL DEFAULT NULL,
  `cantidad` DECIMAL(10,2) NULL DEFAULT NULL,
  `unidad_usada` VARCHAR(20) NULL DEFAULT NULL,
  `precio_unitario` DECIMAL(10,2) NULL DEFAULT NULL,
  `subtotal` DECIMAL(12,2) NULL DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  INDEX `id_pedido` (`id_pedido` ASC) VISIBLE,
  CONSTRAINT `detalle_pedido_ibfk_1`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedidos` (`id_pedido`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `movimientos_inventario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `movimientos_inventario` ;

CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id_movimiento` INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto` INT(11) NULL DEFAULT NULL,
  `tipo_movimiento` VARCHAR(20) NULL DEFAULT NULL,
  `cantidad` DECIMAL(10,2) NULL DEFAULT NULL,
  `stock_resultante` DECIMAL(10,2) NULL DEFAULT NULL,
  `id_usuario` INT(11) NULL DEFAULT NULL,
  `id_pedido` INT(11) NULL DEFAULT NULL,
  `motivo` TEXT NULL DEFAULT NULL,
  `fecha_movimiento` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_movimiento`),
  INDEX `id_producto` (`id_producto` ASC) VISIBLE,
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  INDEX `id_pedido` (`id_pedido` ASC) VISIBLE,
  CONSTRAINT `movimientos_inventario_ibfk_1`
    FOREIGN KEY (`id_producto`)
    REFERENCES `productos` (`id_producto`),
  CONSTRAINT `movimientos_inventario_ibfk_2`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `movimientos_inventario_ibfk_3`
    FOREIGN KEY (`id_pedido`)
    REFERENCES `pedidos` (`id_pedido`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `permisos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `permisos` ;

CREATE TABLE IF NOT EXISTS `permisos` (
  `id_permiso` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(10) NULL DEFAULT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_permiso`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `promociones`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `promociones` ;

CREATE TABLE IF NOT EXISTS `promociones` (
  `id_promocion` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NULL DEFAULT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `tipo_descuento` VARCHAR(50) NULL DEFAULT NULL,
  `valor_descuento` DECIMAL(10,2) NULL DEFAULT NULL,
  `fecha_inicio` DATE NULL DEFAULT NULL,
  `fecha_fin` DATE NULL DEFAULT NULL,
  `id_producto` INT(11) NULL DEFAULT NULL,
  `id_categoria` INT(11) NULL DEFAULT NULL,
  `activa` TINYINT(1) NULL DEFAULT NULL,
  PRIMARY KEY (`id_promocion`),
  INDEX `id_producto` (`id_producto` ASC) VISIBLE,
  INDEX `id_categoria` (`id_categoria` ASC) VISIBLE,
  CONSTRAINT `promociones_ibfk_1`
    FOREIGN KEY (`id_producto`)
    REFERENCES `productos` (`id_producto`),
  CONSTRAINT `promociones_ibfk_2`
    FOREIGN KEY (`id_categoria`)
    REFERENCES `categorias` (`id_categoria`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `unidades_conversion`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `unidades_conversion` ;

CREATE TABLE IF NOT EXISTS `unidades_conversion` (
  `id_conversion` INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto` INT(11) NULL DEFAULT NULL,
  `unidad` VARCHAR(20) NULL DEFAULT NULL,
  `factor_conversion` DECIMAL(10,4) NULL DEFAULT NULL,
  `precio_unitario` DECIMAL(10,2) NULL DEFAULT NULL,
  PRIMARY KEY (`id_conversion`),
  INDEX `id_producto` (`id_producto` ASC) VISIBLE,
  CONSTRAINT `unidades_conversion_ibfk_1`
    FOREIGN KEY (`id_producto`)
    REFERENCES `productos` (`id_producto`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
