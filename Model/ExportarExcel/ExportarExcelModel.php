<?php


class ExportarExcelModel {

  
    private function definiciones() {
        return [
            'rep-actividades-zoo' => [
                'titulo'   => 'Seguimiento de Actividades en los Zoocriaderos',
                'archivo'  => 'SeguimientoDeActividadesController.php',
                'funcion'  => 'obtenerDatosSeguimientoDeActividades',
                'clave'    => 'actividadesFiltradas',
                'columnas' => [
                    'Actividad' => 'actividad', 'Zoocriadero' => 'zoocriadero',
                    'Fecha inicio' => 'inicio', 'Fecha fin' => 'fin',
                    'Responsable' => 'responsable', 'Estado' => 'estado',
                ],
                'resumen'  => [
                    'Actividades totales' => 'totalActividades', 'Completas' => 'totalCompletas',
                    'En progreso' => 'totalEnProgreso', 'Retrasadas' => 'totalRetrasadas',
                ],
            ],
            'rep-peces-tanque' => [
                'titulo'   => 'Peces nacidos o muertos por tanque',
                'archivo'  => 'PesesNacidos-MuertosPorTanqueController.php',
                'funcion'  => 'obtenerDatosPecesNacidosMuertosPorTanque',
                'clave'    => 'resumenPorTanque',
                'columnas' => [
                    'Tanque' => 'tanque', 'Zoocriadero' => 'zoocriadero',
                    'Nacidos' => 'nacidos', 'Muertos' => 'muertos',
                ],
                'resumen'  => [
                    'Peces nacidos' => 'totalNacidos', 'Peces muertos' => 'totalMuertos',
                    'Tasa de mortalidad (%)' => 'tasaMortalidadGeneral',
                ],
            ],
            'rep-tanques-zoo' => [
                'titulo'   => 'Tanques por Zoocriadero',
                'archivo'  => 'TanquesPorZoocriaderoController.php',
                'funcion'  => 'obtenerDatosTanquesPorZoocriadero',
                'clave'    => 'registrosFiltrados',
                'columnas' => [
                    'Zoocriadero' => 'zoocriadero', 'Cantidad' => 'cantidad',
                    'Tipo de tanque' => 'tipo', 'Encargado' => 'encargado', 'Estado' => 'estado',
                ],
                'resumen'  => [
                    'Zoocriaderos' => 'totalZoocriaderos', 'Tanques totales' => 'totalTanques',
                    'Tanques activos' => 'totalTanquesActivos',
                ],
            ],
            'rep-terreno-tipo' => [
                'titulo'   => 'Actividades de Terreno por Tipo',
                'archivo'  => 'ActividadesDeTerrenoPorTipoController.php',
                'funcion'  => 'obtenerDatosActividadesDeTerrenoPorTipo',
                'clave'    => 'resumenPorTipo',
                'columnas' => [
                    'Tipo de actividad' => 'tipo', 'Completadas' => 'completadas',
                    'En progreso' => 'enProgreso', 'Retrasadas' => 'retrasadas',
                    'Total' => 'total', '% del total' => 'porcentaje',
                ],
                'resumen'  => [
                    'Actividades totales' => 'totalActividades', 'Completas' => 'totalCompletas',
                    'En progreso' => 'totalEnProgreso', 'Retrasadas' => 'totalRetrasadas',
                ],
            ],
            'rep-terreno-auxiliar' => [
                'titulo'   => 'Actividades de Terreno por Auxiliar Responsable',
                'archivo'  => 'ActividadesPorAuxiliarController.php',
                'funcion'  => 'obtenerDatosActividadesPorAuxiliar',
                'clave'    => 'auxiliaresPagina',
                'paginada' => true,
                'columnas' => [
                    'Auxiliar' => 'auxiliar', 'Completadas' => 'completadas',
                    'En progreso' => 'enProgreso', 'Retrasadas' => 'retrasadas',
                    'Total' => 'total', '% Cumplimiento' => 'cumplimiento',
                ],
                'resumen'  => [
                    'Actividades totales' => 'totalActividades', 'Completas' => 'totalCompletas',
                    'En progreso' => 'totalEnProgreso', 'Retrasadas' => 'totalRetrasadas',
                    'Cumplimiento general (%)' => 'cumplimientoGeneral',
                ],
            ],
            'rep-sitios-deposito' => [
                'titulo'   => 'Gráfico de Sitios por Tipo de Depósito',
                'archivo'  => 'GráficoDeSitiosPorDepósitoController.php',
                'funcion'  => 'obtenerDatosSitiosPorDeposito',
                'clave'    => 'sitiosPagina',
                'paginada' => true,
                'columnas' => [
                    'ID' => 'id', 'Sitio' => 'nombre', 'Barrio' => 'zoocriadero',
                    'Tipo de depósito' => 'tipo', 'Visitas' => 'tanques',
                    'Estado' => 'estado', 'Fecha' => 'fecha',
                ],
                'resumen'  => [
                    'Total de sitios' => 'totalSitios', 'Tipos de depósito' => 'totalTiposDeposito',
                    'Sitios con depósito' => 'totalConDeposito', 'Sin depósito' => 'totalSinDeposito',
                ],
            ],
        ];
    }

    
    private function etiquetasFiltros() {
        return [
            'zoocriadero'   => 'Zoocriadero',
            'tanque'        => 'Tanque',
            'sexo'          => 'Sexo',
            'actividad'     => 'Actividad',
            'auxiliar'      => 'Auxiliar',
            'tipo'          => 'Tipo',
            'estado'        => 'Estado',
            'tipo_deposito' => 'Tipo de depósito',
            'fecha_inicio'  => 'Desde',
            'fecha_fin'     => 'Hasta',
        ];
    }

    public function existe($reporte) {
        return isset($this->definiciones()[$reporte]);
    }

    // "Zoocriadero: Central   ·   Desde: 01/08/2026" o "Sin filtros (todos los datos)".
    public function textoFiltros() {
        $partes = [];
        foreach ($this->etiquetasFiltros() as $clave => $etiqueta) {
            $valor = trim((string) ($_GET[$clave] ?? ''));
            if ($valor === '') {
                continue;
            }
            if (in_array($clave, ['fecha_inicio', 'fecha_fin'], true)) {
                $fecha = DateTime::createFromFormat('Y-m-d', $valor);
                if ($fecha) {
                    $valor = $fecha->format('d/m/Y');
                }
            }
            $partes[] = $etiqueta . ': ' . $valor;
        }
        return $partes ? implode('   ·   ', $partes) : 'Sin filtros (todos los datos)';
    }

    // Devuelve [datos del reporte, todas las filas]. En las vistas paginadas
    // llama a la misma función una vez por página y junta las filas.
    private function obtenerTodo($def) {
        if (empty($def['paginada'])) {
            $datos = call_user_func($def['funcion']);
            return [$datos, array_values($datos[$def['clave']] ?? [])];
        }

        $_GET['pagina'] = 1; // por si la URL traía otra página
        $datos = call_user_func($def['funcion']);
        $filas = array_values($datos[$def['clave']] ?? []);
        $totalPaginas = (int) ($datos['totalPaginas'] ?? 1);

        for ($p = 2; $p <= $totalPaginas; $p++) {
            $_GET['pagina'] = $p;
            $pagina = call_user_func($def['funcion']);
            $filas = array_merge($filas, array_values($pagina[$def['clave']] ?? []));
        }
        return [$datos, $filas];
    }

    
    public function obtenerReporte($reporte) {
        $def = $this->definiciones()[$reporte];

        require_once __DIR__ . '/../../Controller/Reportes/' . $def['archivo'];
        list($datos, $filas) = $this->obtenerTodo($def);

        return [
            'reporte'    => $reporte,
            'definicion' => $def,
            'datos'      => $datos,
            'filas'      => $filas,
            'filtros'    => $this->textoFiltros(),
        ];
    }
}
