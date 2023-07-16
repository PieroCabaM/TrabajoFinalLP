<?php

// Definición de usuarios registrados
$usuarios = array(
    'david' => 'david123',
    'motomoto' => 'david123'
);

// Definición de una variable para almacenar los nombres de los archivos cargados
$archivosCargados = array();

// Definición de una variable para almacenar el historial de búsquedas
$historialBusquedas = array();

// Función para ingresar al sistema
function ingresarAlSistema() {
    global $usuarios;

    // Solicitar usuario y contraseña
    $usuario = readline("Usuario: ");
    $contrasena = readline("Contraseña: ");

    // Verificar si el usuario y la contraseña son válidos
    if (array_key_exists($usuario, $usuarios) && $usuarios[$usuario] === $contrasena) {
        echo "Bienvenido, $usuario.\n";
        mostrarMenu($usuario);
    } else {
        echo "Usuario o contraseña incorrectos. Inténtalo nuevamente.\n";
        ingresarAlSistema();
    }
}

// Función para mostrar el menú y procesar la opción seleccionada
function mostrarMenu($usuario) {
    global $archivosCargados;
    echo "Menú:\n";
    echo "1. Registrar un texto\n";
    echo "2. Buscar palabra/oracion en el texto\n";
    echo "3. Ver historial de búsquedas\n";
    echo "0. Salir\n";

    $opcion = readline("Selecciona una opción: ");

    switch ($opcion) {
        case '1':
            registrarTexto($usuario);
            break;
        case '2':
            buscarPalabraOracion($usuario);
            break;
        case '3':
            verHistorial($usuario);
            break;
        case '0':
            echo "Gracias por utilizar el sistema. ¡Hasta luego!\n";
            break;
        default:
            echo "Opción inválida. Selecciona una opción válida.\n";
            mostrarMenu($usuario);
            break;
    }
}

// Función para registrar un texto
function registrarTexto($usuario) {
    global $archivosCargados;

    echo "Registrar un texto:\n";

    // Variable para controlar la carga de múltiples archivos
    $cargarMasArchivos = true;

    while ($cargarMasArchivos) {
        $archivo = readline("Ingrese la ruta del archivo a cargar: ");

        // Verificar si el archivo existe
        if (file_exists($archivo)) {
            $texto = file_get_contents($archivo);

            // Aquí puedes agregar la lógica para almacenar el texto en el usuario correspondiente

            echo "Texto registrado exitosamente.\n";

            // Agregar el nombre del archivo a la lista de archivos cargados
            $archivosCargados[] = $archivo;
        } else {
            echo "El archivo no existe. Verifica la ruta e inténtalo nuevamente.\n";
        }

        echo "¿Desea añadir otro texto?\n";
        echo "1. SI\n";
        echo "2. NO\n";
        $opcion = readline("Selecciona una opción: ");

        // Verificar la opción seleccionada
        if ($opcion == '1') {
            $cargarMasArchivos = true;
        } elseif ($opcion == '2') {
            $cargarMasArchivos = false;

            // Mostrar los nombres de los archivos cargados
            echo "Archivos cargados confirmados:\n";
            foreach ($archivosCargados as $nombreArchivo) {
                echo "$nombreArchivo\n";
            }
        } else {
            echo "Opción inválida. Selecciona una opción válida.\n";
        }
    }

    mostrarMenu($usuario);
}
// Funciones de búsqueda

function buscarPalabraFuerzaBruta($texto, $palabra) {
    $ocurrencias = array();
    $textoLength = strlen($texto);
    $palabraLength = strlen($palabra);

    for ($i = 0; $i <= $textoLength - $palabraLength; $i++) {
        $j = 0;
        while ($j < $palabraLength && $texto[$i + $j] === $palabra[$j]) {
            $j++;
        }

        if ($j === $palabraLength) {
            $ocurrencias[] = $i;
        }
    }

    return $ocurrencias;
}

function calcularTablaKMP($palabra) {
    $tabla = array();
    $length = strlen($palabra);
    $i = 0;
    $j = -1;
    $tabla[0] = -1;

    while ($i < $length) {
        while ($j >= 0 && $palabra[$i] !== $palabra[$j]) {
            $j = $tabla[$j];
        }

        $i++;
        $j++;
        $tabla[$i] = $j;
    }

    return $tabla;
}

function buscarPalabraKMP($texto, $palabra) {
    $ocurrencias = array();
    $textoLength = strlen($texto);
    $palabraLength = strlen($palabra);
    $tabla = calcularTablaKMP($palabra);
    $i = 0;
    $j = 0;

    while ($i < $textoLength) {
        while ($j >= 0 && $texto[$i] !== $palabra[$j]) {
            $j = $tabla[$j];
        }

        $i++;
        $j++;

        if ($j === $palabraLength) {
            $ocurrencias[] = $i - $j;
            $j = $tabla[$j];
        }
    }

    return $ocurrencias;
}

function calcularTablaSaltos($palabra) {
    $tabla = array();
    $length = strlen($palabra);

    for ($i = 0; $i < $length - 1; $i++) {
        $tabla[$palabra[$i]] = $length - $i - 1;
    }

    return $tabla;
}

function buscarPalabraBoyerMoore($texto, $palabra) {
    $ocurrencias = array();
    $textoLength = strlen($texto);
    $palabraLength = strlen($palabra);
    $tablaSaltos = calcularTablaSaltos($palabra);
    $i = $palabraLength - 1;
    $j = $palabraLength - 1;

    while ($i < $textoLength) {
        if ($texto[$i] === $palabra[$j]) {
            if ($j === 0) {
                $ocurrencias[] = $i;
                $i += $palabraLength;
                $j = $palabraLength - 1;
            } else {
                $i--;
                $j--;
            }
        } else {
            $i += $tablaSaltos[$texto[$i]];
            $j = $palabraLength - 1;
        }
    }

    return $ocurrencias;
}

function obtenerContextoTexto($texto, $inicio, $longitudContexto = 50) {
    $textoLength = strlen($texto);
    $inicioContexto = max(0, $inicio - $longitudContexto);
    $finContexto = min($textoLength - 1, $inicio + $longitudContexto);
    $contexto = substr($texto, $inicioContexto, $finContexto - $inicioContexto + 1);
    return $contexto;
}

// Función para buscar una palabra u oración
function buscarPalabraOracion($usuario) {
    global $historialBusquedas;
    global $archivosCargados;

    echo "Seleccione el archivo en el que desea buscar:\n";
    foreach ($archivosCargados as $indice => $nombreArchivo) {
        echo $indice + 1 . ". $nombreArchivo\n";
    }
    $opcionArchivo = readline("Seleccione el número del archivo: ");

    $archivoSeleccionado = $archivosCargados[$opcionArchivo - 1];
    $texto = file_get_contents($archivoSeleccionado);

    $palabraOracion = readline("Ingrese la palabra u oración a buscar: ");

    echo "Seleccione el algoritmo de búsqueda:\n";
    echo "1. Fuerza Bruta\n";
    echo "2. Knuth-Morris-Pratt\n";
    echo "3. Boyer-Moore\n";
    $opcion = readline("Ingrese el número del algoritmo de búsqueda: ");

    $ocurrencias = array();
    $tiempoInicio = microtime(true); // Registro del tiempo de inicio

    switch ($opcion) {
        case '1':
            $ocurrencias = buscarPalabraFuerzaBruta($texto, $palabraOracion);
            break;
        case '2':
            $ocurrencias = buscarPalabraKMP($texto, $palabraOracion);
            break;
        case '3':
            $ocurrencias = buscarPalabraBoyerMoore($texto, $palabraOracion);
            break;
        default:
            echo "Opción inválida. Selecciona una opción válida.\n";
            buscarPalabraOracion($usuario);
            break;
    }

    $tiempoFin = microtime(true); // Registro del tiempo de fin
    $duracionBusqueda = $tiempoFin - $tiempoInicio; // Cálculo de la duración de la búsqueda en segundos

    $cantidadApariciones = count($ocurrencias);

    if ($cantidadApariciones > 0) {
        echo "Se encontró la palabra u oración en el archivo.\n";

        echo "Seleccione una opción:\n";
        echo "1. Mostrar cantidad de apariciones\n";
        echo "2. Ver apariciones\n";
        $opcionMostrar = readline("Ingrese el número de opción: ");

        switch ($opcionMostrar) {
            case '1':
                echo "Nombre del archivo: $archivoSeleccionado\n";
                echo "Palabra / oración de búsqueda: $palabraOracion\n";
                echo "Tiempo de duración de la búsqueda: $duracionBusqueda segundos\n";
                echo "Cantidad de apariciones de la palabra: $cantidadApariciones\n";
                break;
            case '2':
                echo "Apariciones:\n";
                foreach ($ocurrencias as $indice => $inicio) {
                    echo "Ocurrencia " . ($indice + 1) . ": $inicio\n";
                    $contexto = obtenerContextoTexto($texto, $inicio);
                    echo "Contexto: $contexto\n";
                    echo "----------\n";
                }
                break;
            default:
                echo "Opción inválida. Selecciona una opción válida.\n";
                break;
        }
    } else {
        echo "No se encontró la palabra u oración en el archivo.\n";
    }

    // Almacenar la búsqueda en el historial
    $historialBusquedas[] = array(
        'archivo' => $archivoSeleccionado,
        'palabraOracion' => $palabraOracion,
        'tiempo' => $duracionBusqueda,
        'ocurrencias' => $cantidadApariciones
    );

    mostrarMenu($usuario);
}

function verHistorial($usuario) {
    global $historialBusquedas;

    echo "Historial de búsquedas:\n";

    // Ordenar el historial por cantidad de apariciones de forma descendente
    usort($historialBusquedas, function ($a, $b) {
        return $b['ocurrencias'] - $a['ocurrencias'];
    });

    foreach ($historialBusquedas as $historia) {
        echo "Nombre del archivo: " . $historia['archivo'] . "\n";
        echo "Palabra / oración de búsqueda: " . $historia['palabraOracion'] . "\n";
        echo "Tiempo de duración de la búsqueda: " . $historia['tiempo'] . " segundos\n";
        echo "Cantidad de apariciones de la palabra: " . $historia['ocurrencias'] . "\n";
        echo "--------------------------------\n";
    }

    mostrarMenu($usuario);
}


// Inicio del programa
echo "Sistema de Registro y Búsqueda\n";
ingresarAlSistema();
