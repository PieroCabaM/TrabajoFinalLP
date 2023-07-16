class Autenticar
    @@usuario = "usuario"
    @@contrasena = "contrasena"
  
    def self.autenticar(usuario, contrasena)
      if usuario == @@usuario && contrasena == @@contrasena
        puts "Autenticación exitosa"
        return true
      else
        puts "Autenticación fallida"
        return false
      end
    end
  end
  
  
  class Texto
    def self.leer_texto(ruta_archivo)
      contenido = File.read(ruta_archivo)
      contenido.downcase
      contenido
    end
  
    def self.obtener_ruta_archivo
      puts "¿Desea utilizar el archivo actual? (S/N)"
      respuesta = gets.chomp.downcase
  
      if respuesta == "s"
        archivo = File.join(File.dirname(__FILE__), "HAMLET.txt")
      else
        puts "Ingrese la ruta del archivo:"
        archivo = gets.chomp
      end
  
      archivo
    end
  
    def self.buscar_fuerza_bruta(palabra, ruta_archivo)
      texto = leer_texto(ruta_archivo)
      coincidencias = []
      i = 0
  
      while i <= texto.length - palabra.length
        j = 0
        while j < palabra.length && texto[i + j] == palabra[j]
          j += 1
        end
  
        if j == palabra.length
          coincidencias << i
        end
  
        i += 1
      end
      coincidencias
    end
  
    def self.buscar_knuth_boyer_pratt(palabra, ruta_archivo)
      texto = leer_texto(ruta_archivo)
      coincidencias = []
      m = palabra.length
      n = texto.length
      bordes = calcular_bordes(palabra)
  
      i = 0
      j = 0
  
      while i < n
        while j >= 0 && texto[i] != palabra[j]
          j = bordes[j]
        end
  
        i += 1
        j += 1
  
        if j == m
          coincidencias << i - j
          j = bordes[j]
        end
      end
      coincidencias
    end
  
    def self.calcular_bordes(palabra)
      m = palabra.length
      bordes = Array.new(m + 1, 0)
      bordes[0] = -1
      i = 0
      j = -1
  
      while i < m
        while j >= 0 && palabra[i] != palabra[j]
          j = bordes[j]
        end
  
        i += 1
        j += 1
        bordes[i] = j
      end
      bordes
    end
  
    def self.buscar_boyer_moore(palabra, ruta_archivo)
      texto = leer_texto(ruta_archivo)
      indices = []
  
      pattern_length = palabra.length
      text_length = texto.length
      skip_table = {}
  
      (0..pattern_length - 2).each do |i|
        skip_table[palabra[i]] = pattern_length - i - 1
      end
  
      i = pattern_length - 1
  
      while i < text_length
        k = 0
  
        while k < pattern_length && palabra[pattern_length - 1 - k] == texto[i - k]
          k += 1
        end
  
        if k == pattern_length
          indices << i - pattern_length + 1
        end
  
        skip = skip_table[texto[i]] || pattern_length
        i += skip
      end
      indices
    end
  
    def self.calcular_tiempo_ejecucion(palabra_ingresada, ruta_archivo)
      palabra = palabra_ingresada # Palabra de ejemplo para realizar la búsqueda
  
      start_time = Time.now
      buscar_fuerza_bruta(palabra, ruta_archivo)
      fuerza_bruta_time = Time.now - start_time
  
      start_time = Time.now
      buscar_knuth_boyer_pratt(palabra, ruta_archivo)
      knuth_boyer_pratt_time = Time.now - start_time
  
      start_time = Time.now
      buscar_boyer_moore(palabra, ruta_archivo)
      boyer_moore_time = Time.now - start_time
  
      {
        fuerza_bruta: fuerza_bruta_time,
        knuth_boyer_pratt: knuth_boyer_pratt_time,
        boyer_moore: boyer_moore_time
      }
    end
  end
  
  class Historial
    def self.crear_historial
      unless File.exist?("Historial.txt")
        File.new("Historial.txt", "w")
        puts "Historial creado."
      end
    end
  
    def self.modificar_historial(palabra)
      contenido_actual = File.read("Historial.txt")
      nueva_linea = palabra + "\n"
      nuevo_contenido = nueva_linea + contenido_actual
      File.write("Historial.txt", nuevo_contenido)
    end
  
    def self.mostrar_historial
      contenido = File.read("Historial.txt")
      puts contenido
    end
  
    def self.borrar_historial
      File.write("Historial.txt", "")
      puts "Historial borrado."
    end
  end
  
  # Ejemplo de uso
  puts "Ingrese usuario:"
  usuario_ingresado = gets.chomp
  puts "Ingrese contraseña:"
  contrasena_ingresada = gets.chomp
  
  if Autenticar.autenticar(usuario_ingresado, contrasena_ingresada)
    puts "Ingrese palabra a buscar: "
    palabra_ingresada = gets.chomp
  
    ruta_archivo = Texto.obtener_ruta_archivo
  
    start_time = Time.now
  
    puts "¿Qué algoritmo desea usar?"
    puts "Si es 'FUERZA BRUTA', ingrese 1."
    puts "Si es 'KNUTH-MORRIS-PRATT', ingrese 2."
    puts "Si es 'BOYER-MOORE', ingrese 3: "
    algoritmo_usado = gets.chomp
  
    if algoritmo_usado == "1"
        resultados = Texto.buscar_fuerza_bruta(palabra_ingresada, ruta_archivo)
        puts "Coincidencias encontradas usando Fuerza Bruta: #{resultados}"
        fuerza_bruta_time = Time.now - start_time
        puts "Tiempos de ejecución:"
        puts "Fuerza Bruta: #{fuerza_bruta_time} segundos"

    elsif algoritmo_usado == "2"
        resultados = Texto.buscar_knuth_boyer_pratt(palabra_ingresada, ruta_archivo)
        puts "Coincidencias encontradas usando Knuth-Morris-Pratt: #{resultados}"
        knuth_boyer_pratt_time = Time.now - start_time
        puts "Tiempos de ejecución:"
        puts "Knuth-Morris-Pratt: #{knuth_boyer_pratt_time} segundos"
    elsif algoritmo_usado == "3"
        resultados = Texto.buscar_boyer_moore(palabra_ingresada, ruta_archivo)
        puts "Coincidencias encontradas usando Boyer-Moore: #{resultados}"
        boyer_moore_time = Time.now - start_time
        puts "Tiempos de ejecución:"
        puts "Boyer-Moore: #{boyer_moore_time} segundos"
    else
        puts "Opción de algoritmo no válida."
    end
end