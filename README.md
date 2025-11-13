# Prueba Tecnica Backend

## Contexto

La empresa de acueduco de la ciudad tiene problemas con la gestión y notificación de sus facturas, el software que manejan actualmente es muy lento y obsoleto y tienen la necesidad de crear un software a la medida para solventar dicha novedad.

La empresa maneja una base de datos de los clientes que tiene la información personal (nombres, apellidos, identificación, correo y dirección).

Cada cliente tiene asociado como minimo un contador o medidor el cual es el que se encarga de llevar el consumo en metros cubicos (m^3) del consumo, los medidores guardan la siguient información (identificador del cliente, número de serial, fecha de instalación, estado[activo]).

Cada medidor lleva la lectura del consumo desde que se instala en la vivienda del cliente, la información de cada medida o lectura es: identificador del medido, fecha de lectura, lectura anterior, lectura actual consumo en metros cubicos (m^3) y una observación.

Las facturas se crean los 15 de cada mes, la información que lleva la factura factura es la siguiente: número de factura, información del cliente, fecha inicio de facturación, fecha final de facturación, fecha de emisión de la factura, fecha de vencimiento de la factura, estado (pendiente, pagado, vencido), conceptos o items de cobro, total a pagar.

Los conceptos de cobros incluidos en la factura tienen la siguiente informacion: identificador de la factura, concepto (Consumo básico, Alcantarillado, intereses en mora, arreglos, etc), precio unitario, cantidad, subtotal, impuesto (si aplica), total.

### Problematica

Totods los registros que se manejan estan en formato excel

Los clientes no tienen accesos a sus facturas desde hace 3 meses por lo cual tienen que trasladarse a la oficina principal de la empresa para conocer y pagar el valor de las facturas.

### Requerimientos

1. Crear la base de datos
2. Importar los datos proporcionados
3. Implementar un sistema de roles y permisos
4. Implementar funcionalidad crud para gestionar factura
5. Envio de notificaciones mediante correo electronico cuando se genere una factura
