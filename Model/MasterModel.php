<?php

    include_once __DIR__ . '/../lib/conf/connection.php';

    // ============================================================
    // MasterModel con la extensión nativa pgsql (SIN PDO).
    //
    // Cada método acepta un segundo parámetro opcional con los
    // valores de la consulta. Si se envía, se usa pg_query_params y
    // los datos viajan APARTE del SQL ($1, $2, $3...), que es como
    // PostgreSQL evita la inyección SQL:
    //
    //   $this->selectAll("SELECT * FROM rol WHERE id_rol = $1", [$id]);
    // ============================================================
    class MasterModel extends Connection{

        protected function ejecutar($sql, $parametros = []){
            if(!empty($parametros)){
                $resultado = @pg_query_params($this->getConnect(), $sql, array_values($parametros));
            }else{
                $resultado = @pg_query($this->getConnect(), $sql);
            }

            if($resultado === false){
                error_log("Error SQL: " . pg_last_error($this->getConnect()) . " | SQL: " . $sql);
                return false;
            }

            return $resultado;
        }

        // ---- Los 4 métodos de siempre ----
        public function insert($sql, $parametros = []){ return $this->ejecutar($sql, $parametros); }
        public function select($sql, $parametros = []){ return $this->ejecutar($sql, $parametros); }
        public function update($sql, $parametros = []){ return $this->ejecutar($sql, $parametros); }
        public function delete($sql, $parametros = []){ return $this->ejecutar($sql, $parametros); }

        // ---- Ayudas para leer resultados ----

        // Todas las filas como arreglo asociativo
        public function selectAll($sql, $parametros = []){
            $resultado = $this->ejecutar($sql, $parametros);
            if($resultado === false){ return []; }

            $filas = [];
            while($fila = pg_fetch_assoc($resultado)){
                $filas[] = $fila;
            }
            pg_free_result($resultado);
            return $filas;
        }

        // Solo la primera fila
        public function selectOne($sql, $parametros = []){
            $resultado = $this->ejecutar($sql, $parametros);
            if($resultado === false){ return null; }

            $fila = pg_fetch_assoc($resultado);
            pg_free_result($resultado);
            return $fila ? $fila : null;
        }

        // Solo el primer valor (COUNT, RETURNING id...)
        public function selectValue($sql, $parametros = []){
            $resultado = $this->ejecutar($sql, $parametros);
            if($resultado === false || pg_num_rows($resultado) === 0){ return null; }

            $valor = pg_fetch_result($resultado, 0, 0);
            pg_free_result($resultado);
            return $valor;
        }

        // ---- Transacciones (sin PDO) ----
        public function beginTransaction(){ return pg_query($this->getConnect(), "BEGIN"); }
        public function commit(){           return pg_query($this->getConnect(), "COMMIT"); }
        public function rollBack(){         return @pg_query($this->getConnect(), "ROLLBACK"); }

        public function ultimoError(){ return pg_last_error($this->getConnect()); }

    }

?>
