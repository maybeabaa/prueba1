<?php

// Conexión a Base de Datos
$host = "localhost";
$usuario = "root";
$password = "";
$basededatos = "apipanel";

$conexion = new mysqli($host, $usuario, $password, $basededatos);

if ($conexion->connect_error) {
    die("Conexión no establecida: " . $conexion->connect_error);
}

// Configurar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Recepción de Métodos
header("Content-Type: application/json");

$metodo = $_SERVER['REQUEST_METHOD'];

$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

$buscarId = explode('/', $path);

$id = ($path !== '/') ? end($buscarId) : null;

// Analizar Datos
switch($metodo) {
    // SELECT
    case 'GET':
        consulta($conexion, $id);
        break;
    // INSERT
    case 'POST':
        insertar($conexion);
        break;
    // UPDATE
    case 'PUT':
        actualizar($conexion, $id);
        break;
    // DELETE
    case 'DELETE':
        borrar($conexion, $id);
        break;
    default:
        echo "Método no permitido";
        break;
}

// Consultar Datos
function consulta($conexion, $id) {
    $sql = ($id === null) ? "SELECT * FROM datos" : "SELECT * FROM datos WHERE id=$id";
    $resultado = $conexion->query($sql);

    if ($resultado) {
        $datos = array();
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }
        echo json_encode($datos);
    }
}

// Insertar Datos
function insertar($conexion) {
    // Obtén los datos enviados via POST
    $voltaje = isset($_POST['voltaje']) ? $_POST['voltaje'] : null;
    $corriente = isset($_POST['corriente']) ? $_POST['corriente'] : null;
    $eficiencia = isset($_POST['eficiencia']) ? $_POST['eficiencia'] : null;

    // Verifica que los datos no estén vacíos
    if ($voltaje !== null && $corriente !== null && $eficiencia !== null) {
        // Prepara la sentencia SQL para evitar inyección
        $stmt = $conexion->prepare("INSERT INTO datos (voltaje, corriente, eficiencia, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ddd", $voltaje, $corriente, $eficiencia);

        if ($stmt->execute()) {
            $dato['id'] = $conexion->insert_id;
            $dato['fecha'] = date('Y-m-d H:i:s');  // Agrega la fecha a la respuesta
            echo json_encode($dato);
        } else {
            echo json_encode(array('error' => 'Error al crear registro'));
        }

        $stmt->close();
    } else {
        echo json_encode(array('error' => 'Datos incompletos'));
    }
}


// Borrar Datos
function borrar($conexion, $id) {
    $sql = "DELETE FROM datos WHERE id=$id";
    $resultado = $conexion->query($sql);

    if ($resultado) {
        echo json_encode(array('mensaje' => 'Registro eliminado'));
    } else {
        echo json_encode(array('error' => 'Error al borrar registro'));
    }
}

// Actualizar Datos
function actualizar($conexion, $id) {
    $dato = json_decode(file_get_contents('php://input'), true);
    $voltaje = $dato['voltaje'];
    $corriente = $dato['corriente'];
    $eficiencia = $dato['eficiencia'];

    $sql = "UPDATE datos SET voltaje='$voltaje', corriente='$corriente', eficiencia='$eficiencia' WHERE id=$id";
    $resultado = $conexion->query($sql);

    if ($resultado) {
        echo json_encode(array('mensaje' => 'Registro actualizado'));
    } else {
        echo json_encode(array('error' => 'Error al actualizar registro'));
    }
}

?>
