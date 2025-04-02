<?php
$page_title = 'Dashboard - Inventario';
require_once('includes/load.php');

// Permitir acceso a usuarios de nivel 1 y nivel 2
$user = current_user(); 
if (!$session->isUserLoggedIn(true) || !in_array((int)$user['user_level'], [1, 2])) {
  $session->msg('d', 'No tienes permiso para acceder a esta página.');
  redirect('index.php');
}

// Obtener categorías
$categorias = $db->query("SELECT id_categoria, categoria FROM categoria")->fetch_all(MYSQLI_ASSOC);

// Obtener productos iniciales
$productos_existencia = $db->query("
    SELECT nombreProducto, cantidad 
    FROM producto 
    WHERE visible = 1
    ORDER BY cantidad DESC
")->fetch_all(MYSQLI_ASSOC);

// Obtener datos de entradas (add_product e inventario)
$entradas = $db->query("
    SELECT MONTH(fechaIngreso) AS mes, COUNT(*) AS total 
    FROM producto 
    WHERE YEAR(fechaIngreso) = YEAR(CURDATE()) 
    GROUP BY MONTH(fechaIngreso)
")->fetch_all(MYSQLI_ASSOC);

// Obtener datos de salidas basados únicamente en la tabla `orden_salida`
$salidas = $db->query("
    SELECT MONTH(fecha_entrega) AS mes, COUNT(*) AS total 
    FROM orden_salida 
    WHERE YEAR(fecha_entrega) = YEAR(CURDATE()) 
    GROUP BY MONTH(fecha_entrega)
")->fetch_all(MYSQLI_ASSOC);

// Obtener distribución de productos por cubículos
$productos_por_cubiculo = $db->query("
    SELECT cu.cubiculo, COUNT(p.id_producto) AS total 
    FROM producto p
    JOIN cubiculos cu ON p.id_cubiculo = cu.id_cubiculo
    WHERE p.visible = 1
    GROUP BY cu.cubiculo
")->fetch_all(MYSQLI_ASSOC);

// Preparar datos iniciales para el gráfico
$productos_nombres = [];
$productos_cantidades = [];
foreach ($productos_existencia as $producto) {
    $productos_nombres[] = $producto['nombreProducto'];
    $productos_cantidades[] = (int)$producto['cantidad'];
}

// Preparar datos para el gráfico de entradas y salidas
$datos_entradas = array_fill(1, 12, 0);
foreach ($entradas as $entrada) {
    $datos_entradas[(int)$entrada['mes']] = (int)$entrada['total'];
}

$datos_salidas = array_fill(1, 12, 0);
foreach ($salidas as $salida) {
    $datos_salidas[(int)$salida['mes']] = (int)$salida['total'];
}

// Preparar datos para el gráfico de productos por cubículo
$cubiculos_nombres = [];
$cubiculos_totales = [];
foreach ($productos_por_cubiculo as $cubiculo) {
    $cubiculos_nombres[] = $cubiculo['cubiculo'];
    $cubiculos_totales[] = (int)$cubiculo['total'];
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Filtros para Productos en Existencia</strong>
            </div>
            <div class="panel-body">
                <form id="filtroProductos" class="form-inline">
                    <div class="form-group">
                        <label for="categoria">Categoría:</label>
                        <select id="categoria" class="form-control">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id_categoria']; ?>">
                                    <?php echo $categoria['categoria']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cantidadMin">Cantidad Mínima:</label>
                        <input type="number" id="cantidadMin" class="form-control" placeholder="0" min="0">
                    </div>
                    <div class="form-group">
                        <label for="cantidadMax">Cantidad Máxima:</label>
                        <input type="number" id="cantidadMax" class="form-control" placeholder="1000" min="0">
                    </div>
                    <button type="button" id="aplicarFiltros" class="btn btn-primary">Aplicar Filtros</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Productos en Existencia</strong>
            </div>
            <div class="panel-body">
                <div id="productosExistenciaChart"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Distribución de Productos por Cubículo</strong>
            </div>
            <div class="panel-body">
                <div id="graficoProductosPorCubiculo"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Entradas y Salidas por Mes</strong>
            </div>
            <div class="panel-body">
                <div id="graficoEntradasSalidas"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Gráfico de productos en existencia
        let productosNombres = <?php echo json_encode($productos_nombres); ?>;
        let productosCantidades = <?php echo json_encode($productos_cantidades); ?>;

        let chart = new ApexCharts(document.querySelector("#productosExistenciaChart"), {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: true }
            },
            series: [{
                name: 'Cantidad',
                data: productosCantidades
            }],
            xaxis: {
                categories: productosNombres,
                labels: {
                    rotate: -45,
                    style: { fontSize: '12px' }
                }
            },
            plotOptions: {
                bar: { horizontal: false, barHeight: '100%' }
            },
            dataLabels: { enabled: false },
            title: {
                text: 'Productos en Existencia',
                align: 'center'
            }
        });

        chart.render();

        // Función para actualizar el gráfico con los filtros
        document.getElementById('aplicarFiltros').addEventListener('click', function () {
            const categoria = document.getElementById('categoria').value;

            fetch(`filtrar_productos.php?categoria=${categoria}`)
                .then(response => response.json())
                .then(data => {
                    const nombres = data.map(producto => producto.nombreProducto);
                    const cantidades = data.map(producto => producto.cantidad);

                    chart.updateOptions({
                        xaxis: { categories: nombres },
                        series: [{ name: 'Cantidad', data: cantidades }]
                    });
                })
                .catch(error => {
                    console.error('Error al actualizar el gráfico:', error);
                });
        });

        // Gráfico de productos por cubículo
        const cubiculosNombres = <?php echo json_encode($cubiculos_nombres); ?>;
        const cubiculosTotales = <?php echo json_encode($cubiculos_totales); ?>;

        new ApexCharts(document.querySelector("#graficoProductosPorCubiculo"), {
            chart: {
                type: 'pie',
                height: 350
            },
            series: cubiculosTotales,
            labels: cubiculosNombres,
            title: {
                text: 'Distribución de Productos por Cubículo',
                align: 'center'
            }
        }).render();

        // Gráfico de entradas y salidas
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const entradasData = <?php echo json_encode(array_values($datos_entradas)); ?>;
        const salidasData = <?php echo json_encode(array_values($datos_salidas)); ?>;

        new ApexCharts(document.querySelector("#graficoEntradasSalidas"), {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [
                { name: "Entradas", data: entradasData, color: '#00E396' },
                { name: "Salidas", data: salidasData, color: '#FF4560' }
            ],
            xaxis: {
                categories: meses
            },
            dataLabels: {
                enabled: false
            },
            title: {
                text: 'Entradas y Salidas por Mes',
                align: 'center'
            }
        }).render();
    });
</script>

<?php include_once('layouts/footer.php'); ?>
