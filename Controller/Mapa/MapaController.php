<?php

include_once __DIR__ . '/../../Model/Mapa/MapaModel.php';

class MapaController
{
    public function puntos()
    {
        $obj = new MapaModel();

        $zoocriaderos = array_map(function ($z) {
            return [
                'id' => (int) $z['id'], 'categoria' => 'zoocriadero', 'tipo' => 'Zoocriadero',
                'nombre' => $z['nombre'], 'direccion' => $z['direccion'],
                'comuna' => $z['comuna'], 'barrio' => $z['barrio'],
                'lat' => (float) $z['latitud'], 'lng' => (float) $z['longitud'],
            ];
        }, $obj->zoocriaderos());

        $depositos = array_map(function ($d) {
            return [
                'id' => (int) $d['id'], 'categoria' => 'deposito', 'tipo' => $d['tipo_deposito'],
                'nombre' => ($d['descripcion'] !== null && $d['descripcion'] !== '') ? $d['descripcion'] : $d['tipo_deposito'],
                'sitio' => $d['sitio'], 'direccion' => $d['direccion'],
                'comuna' => $d['comuna'], 'barrio' => $d['barrio'],
                'lat' => (float) $d['latitud'], 'lng' => (float) $d['longitud'],
            ];
        }, $obj->depositos());

        jsonResponse(['ok' => true, 'data' => array_merge($zoocriaderos, $depositos)]);
    }
}
?>