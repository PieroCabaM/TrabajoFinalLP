<?php
class Autenticar {
    private static $usuario = "usuario";
    private static $contrasena = "contrasena";
  
    public static function autenticar($usuario, $contrasena) {
        if ($usuario === self::$usuario && $contrasena === self::$contrasena) {
            echo "Autenticación exitosa\n";
            return true;
        } else {
            echo "Autenticación fallida\n";
            return false;
        }
    }
}
  
class Texto {
    public static function leer_texto($ruta_archivo) {
        $contenido = file_get_contents($ruta_archivo);
        $contenido = strtolower($contenido);
        return $contenido;
    }
  
    public static function obtener_ruta_archivo() {
        echo "¿Desea utilizar el archivo actual? (S/N)\n";
        $respuesta = strtolower(trim(fgets(STDIN)));
  
        if ($respuesta === "s") {
            $archivo = __DIR__ . "/HAMLET.txt";
        } else {
            echo "Ingrese la ruta del archivo:\n";
            $archivo = trim(fgets(STDIN));
        }
  
        return $archivo;
    }
  
    public static function buscar_fuerza_bruta($palabra, $ruta_archivo) {
        $texto = self::leer_texto($ruta_archivo);
        $coincidencias = [];
        $i = 0;
  
        while ($i <= strlen($texto) - strlen($palabra)) {
            $j = 0;
            while ($j < strlen($palabra) && $texto[$i + $j] === $palabra[$j]) {
                $j++;
            }
  
            if ($j === strlen($palabra)) {
                $coincidencias[] = $i;
            }
  
            $i++;
        }
  
        return $coincidencias;
    }
  
    public static function buscar_knuth_boyer_pratt($palabra, $ruta_archivo) {
        $texto = self::leer_texto($ruta_archivo);
        $coincidencias = [];
        $m = strlen($palabra);
        $n = strlen($texto);
        $bordes = self::calcular_bordes($palabra);
  
        $i = 0;
        $j = 0;
  
        while ($i < $n) {
            while ($j >= 0 && $texto[$i] !== $palabra[$j]) {
                $j = $bordes[$j];
            }
  
            $i++;
            $j++;
  
            if ($j === $m) {
                $coincidencias[] = $i - $j;
                $j = $bordes[$j];
            }
        }
  
        return $coincidencias;
    }
  
    public static function calcular_bordes($palabra) {
        $m = strlen($palabra);
        $bordes = array_fill(0, $m + 1, 0);
        $bordes[0] = -1;
        $i = 0;
        $j = -1;
  
        while ($i < $m) {
            while ($j >= 0 && $palabra[$i] !== $palabra[$j]) {
                $j = $bordes[$j];
            }
  
            $i++;
            $j++;
            $bordes[$i] = $j;
        }
  
        return $bordes;
    }
  
    public static function buscar_boyer_moore($palabra, $ruta_archivo) {
        $texto = self::leer_texto($ruta_archivo);
        $indices = [];
  
        $pattern_length = strlen($palabra);
        $text_length = strlen($texto);
        $skip_table = [];
  
        for ($i = 0; $i <= $pattern_length - 2; $i++) {
            $skip_table[$palabra[$i]] = $pattern_length - $i - 1;
        }
  
        $i = $pattern_length - 1;
  
        while ($i < $text_length) {
            $k = 0;
  
            while ($k < $pattern_length && $palabra[$pattern_length - 1 - $k] === $texto[$i - $k]) {
                $k++;
            }
  
            if ($k === $pattern_length) {
                $indices[] = $i - $pattern_length + 1;
            }
  
            $skip = isset($skip_table[$texto[$i]]) ? $skip_table[$texto[$i]] : $pattern_length;
            $i += $skip;
        }
  
        return $indices;
    }
  
    public static function calcular_tiempo_ejecucion($palabra_ingresada, $ruta_archivo) {
        $palabra = $palabra_ingresada; // Palabra de ejemplo para realizar la búsqueda
  
        $start_time = microtime(true);
        self::buscar_fuerza_bruta($palabra, $ruta_archivo);
        $fuerza_bruta_time = microtime(true) - $start_time;
  
        $start_time = microtime(true);
        self::buscar_knuth_boyer_pratt($palabra, $ruta_archivo);
        $knuth_boyer_pratt_time = microtime(true) - $start_time;
  
        $start_time = microtime(true);
        self::buscar_boyer_moore($palabra, $ruta_archivo);
        $boyer_moore_time = microtime(true) - $start_time;
  
        return [
            'fuerza_bruta' => $fuerza_bruta_time,
            'knuth_boyer_pratt' => $knuth_boyer_pratt_time,
            'boyer_moore' => $boyer_moore_time
        ];
    }
}
  
class Historial {
    public static function crear_historial() {
        if (!file_exists("Historial.txt")) {
            file_put_contents("Historial.txt", "");
            echo "Historial creado.\n";
        }
    }
  
    public static function modificar_historial($palabra) {
        $contenido_actual = file_get_contents("Historial.txt");
        $nueva_linea = $palabra . "\n";
        $nuevo_contenido = $nueva_linea . $contenido_actual;
        file_put_contents("Historial.txt", $nuevo_contenido);
    }
  
    public static function mostrar_historial() {
        $contenido = file_get_contents("Historial.txt");
        echo $contenido;
    }
  
    public static function borrar_historial() {
        file_put_contents("Historial.txt", "");
        echo "Historial borrado.\n";
    }
}
  
// Ejemplo de uso
echo "Ingrese usuario: ";
$usuario_ingresado = trim(fgets(STDIN));
echo "Ingrese contraseña: ";
$contrasena_ingresada = trim(fgets(STDIN));
  
if (Autenticar::autenticar($usuario_ingresado, $contrasena_ingresada)) {
    echo "Ingrese palabra a buscar: ";
    $palabra_ingresada = trim(fgets(STDIN));
  
    $ruta_archivo = Texto::obtener_ruta_archivo();
  
    $start_time = microtime(true);
  
    echo "¿Qué algoritmo desea usar?\n";
    echo "Si es 'FUERZA BRUTA', ingrese 1.\n";
    echo "Si es 'KNUTH-MORRIS-PRATT', ingrese 2.\n";
    echo "Si es 'BOYER-MOORE', ingrese 3: ";
    $algoritmo_usado = trim(fgets(STDIN));
  
    if ($algoritmo_usado === "1") {
        $resultados = Texto::buscar_fuerza_bruta($palabra_ingresada, $ruta_archivo);
        echo "Coincidencias encontradas usando Fuerza Bruta: " . implode(", ", $resultados) . "\n";
        $fuerza_bruta_time = microtime(true) - $start_time;
        echo "Tiempos de ejecución:\n";
        echo "Fuerza Bruta: {$fuerza_bruta_time} segundos\n";
    } elseif ($algoritmo_usado === "2") {
        $resultados = Texto::buscar_knuth_boyer_pratt($palabra_ingresada, $ruta_archivo);
        echo "Coincidencias encontradas usando Knuth-Morris-Pratt: " . implode(", ", $resultados) . "\n";
        $knuth_boyer_pratt_time = microtime(true) - $start_time;
        echo "Tiempos de ejecución:\n";
        echo "Knuth-Morris-Pratt: {$knuth_boyer_pratt_time} segundos\n";
    } elseif ($algoritmo_usado === "3") {
        $resultados = Texto::buscar_boyer_moore($palabra_ingresada, $ruta_archivo);
        echo "Coincidencias encontradas usando Boyer-Moore: " . implode(", ", $resultados) . "\n";
        $boyer_moore_time = microtime(true) - $start_time;
        echo "Tiempos de ejecución:\n";
        echo "Boyer-Moore: {$boyer_moore_time} segundos\n";
    } else {
        echo "Opción de algoritmo no válida.\n";
    }
}
?>