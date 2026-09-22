<?php
/**
 * rehash_contrasenas_planas.php
 * ------------------------------------------------------------
 * Se detectó que 4 cuentas de la base de datos de ejemplo
 * (Administrador Temporal, Auxiliar Temporal, Coordinador Temporal y
 * Super Administrador Temporal) tenían su contraseña guardada en texto
 * plano ("Siguppy#Temp2026") en vez de con password_hash(). El login
 * tenía además una comparación de respaldo en texto plano para que esas
 * cuentas de prueba funcionaran; esa comparación ya se quitó de
 * login_process.php, así que estas cuentas dejarían de poder entrar
 * hasta correr este script una vez.
 *
 * Qué hace: recorre la tabla "usuario" y, para cualquier fila cuya
 * "contrasena" NO tenga forma de hash bcrypt (no empieza por $2y$/$2b$/$2a$),
 * la reemplaza por password_hash($valor_actual, PASSWORD_BCRYPT). Es decir,
 * el valor que ya tenían en texto plano queda protegido con el mismo
 * hash que usa el resto del sistema, sin cambiar la contraseña que cada
 * cuenta usa para iniciar sesión.
 *
 * Uso (una sola vez, desde la raíz del proyecto):
 *   php scripts/rehash_contrasenas_planas.php
 * ------------------------------------------------------------
 */

require_once __DIR__ . '/../Model/MasterModel.php';

$modelo = new MasterModel();

$usuarios = $modelo->selectAll("SELECT id_usuario, correo, contrasena FROM usuario");

$actualizados = 0;
foreach ($usuarios as $u) {
    $valor = (string) $u['contrasena'];
    $pareceHashBcrypt = (bool) preg_match('/^\$2[aby]\$/', $valor);

    if ($pareceHashBcrypt) {
        continue; // ya está hasheada, no se toca
    }

    $hash = password_hash($valor, PASSWORD_BCRYPT);
    $modelo->update("UPDATE usuario SET contrasena = $1 WHERE id_usuario = $2", [$hash, $u['id_usuario']]);
    $actualizados++;
    echo "Actualizado: {$u['correo']} (id_usuario {$u['id_usuario']})\n";
}

echo "\nListo. {$actualizados} cuenta(s) actualizada(s) de " . count($usuarios) . " en total.\n";
