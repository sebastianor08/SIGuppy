<?php
/**
 * Vista: Resumen / Dashboard principal (SIGuppy)
 * -------------------------------------------------
 * Por ahora esta vista trabaja con datos de EJEMPLO porque la base de
 * datos aún no está implementada. Cuando el Model esté listo, basta con
 * reemplazar los arreglos $zoocriaderos y $depositos por el resultado
 * real de las consultas, por ejemplo:
 *
 *   $zoocriaderos = (new ZoocriaderoModel())->listarActivosConCoordenadas();
 *   $depositos    = (new DepositoModel())->listarRegistradosConCoordenadas();
 *
 * Cada elemento debe conservar las mismas llaves (codigo, lat, lng,
 * direccion) para que el JS del mapa no requiera cambios.
 */

$tituloPagina = 'Resumen';

// ------- DATOS DE EJEMPLO (reemplazar por consulta al Model) -------
$zoocriaderos = [
    ['codigo' => 'ZIG0-0012', 'lat' => 3.4372, 'lng' => -76.5478, 'direccion' => 'Cra 1 # 2-34, Comuna 20'],
    ['codigo' => 'ZIG0-0090', 'lat' => 3.4368, 'lng' => -76.5455, 'direccion' => 'Cra 3 # 4-12, Comuna 20'],
    ['codigo' => 'ZIG0-0203', 'lat' => 3.4555, 'lng' => -76.5290, 'direccion' => 'Calle 10 # 8-45, Comuna 3'],
    ['codigo' => 'ZK00-0300', 'lat' => 3.4548, 'lng' => -76.5260, 'direccion' => 'Calle 11 # 9-20, Comuna 3'],
    ['codigo' => 'ZI060-0226', 'lat' => 3.4610, 'lng' => -76.5180, 'direccion' => 'Cra 15 # 20-05, Comuna 2'],
    ['codigo' => 'ZI034-0004', 'lat' => 3.4260, 'lng' => -76.5390, 'direccion' => 'Calle 25 # 6-30, Comuna 19'],
    ['codigo' => 'ZIG0-0003', 'lat' => 3.4250, 'lng' => -76.5375, 'direccion' => 'Calle 26 # 7-10, Comuna 19'],
    ['codigo' => 'ZIC10-0104', 'lat' => 3.4300, 'lng' => -76.5300, 'direccion' => 'Cra 20 # 15-40, Comuna 19'],
    ['codigo' => 'ZO61-9203', 'lat' => 3.4500, 'lng' => -76.5240, 'direccion' => 'Cra 12 # 5-60, Comuna 3'],
    ['codigo' => 'Z054-0006', 'lat' => 3.4490, 'lng' => -76.5195, 'direccion' => 'Calle 9 # 11-15, Comuna 3'],
];

$depositos = [
    ['codigo' => 'SI00-0011', 'lat' => 3.4568, 'lng' => -76.5265, 'direccion' => 'Calle 12 # 6-08, Comuna 3'],
    ['codigo' => 'SI00-0103', 'lat' => 3.4345, 'lng' => -76.5450, 'direccion' => 'Cra 2 # 3-50, Comuna 20'],
    ['codigo' => 'SI80-0908', 'lat' => 3.4285, 'lng' => -76.5405, 'direccion' => 'Calle 24 # 5-18, Comuna 19'],
    ['codigo' => 'SI00-0907', 'lat' => 3.4315, 'lng' => -76.5320, 'direccion' => 'Cra 18 # 14-22, Comuna 19'],
    ['codigo' => 'Z051-0104', 'lat' => 3.4560, 'lng' => -76.5215, 'direccion' => 'Calle 8 # 10-33, Comuna 3'],
    ['codigo' => 'Z051-0103', 'lat' => 3.4515, 'lng' => -76.5205, 'direccion' => 'Calle 9 # 12-05, Comuna 3'],
    ['codigo' => 'Z055-0005', 'lat' => 3.4470, 'lng' => -76.5225, 'direccion' => 'Cra 13 # 6-40, Comuna 3'],
    ['codigo' => 'Z054-0021', 'lat' => 3.4405, 'lng' => -76.5280, 'direccion' => 'Calle 20 # 8-15, Comuna 19'],
    ['codigo' => 'Z054-0005', 'lat' => 3.4275, 'lng' => -76.5370, 'direccion' => 'Calle 23 # 6-05, Comuna 19'],
    ['codigo' => 'Z055-0100', 'lat' => 3.4245, 'lng' => -76.5355, 'direccion' => 'Calle 27 # 8-20, Comuna 19'],
    ['codigo' => 'Z054-0106', 'lat' => 3.4230, 'lng' => -76.5340, 'direccion' => 'Calle 28 # 7-12, Comuna 19'],
    ['codigo' => 'Z007-0105', 'lat' => 3.4220, 'lng' => -76.5395, 'direccion' => 'Calle 29 # 5-08, Comuna 19'],
];

$totalZoocriaderosActivos = count($zoocriaderos);
$totalDepositosRegistrados = count($depositos);

include '../partials/header_app.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="page-topbar">
    <i data-lucide="home"></i>
    <h1>Resumen - SIGuppys</h1>
</div>

<div class="welcome-block">
    <h2>¡Bienvenido, Administrador!</h2>
    <p>Aquí tienes un resumen actualizado del Control Biológico contra el Dengue.</p>
</div>

<div class="dashboard-grid">

    <div class="card map-card">
        <div class="map-search">
            <div class="input-wrapper">
                <i data-lucide="map-pin" class="input-icon"></i>
                <input type="text" id="input-direccion" placeholder="Ej: Carrera 15 # 20-05, Cali">
            </div>
            <button type="button" id="btn-buscar-direccion" class="btn-primary">Buscar</button>
        </div>
        <p id="direccion-resultado" class="direccion-resultado"></p>

        <!--
            NOTA IMPORTANTE sobre por qué el mapa a veces "no aparece":
            Leaflet dibuja el mapa DENTRO de este div, pero si el div no
            tiene una altura definida (por CSS), su altura es 0px y el mapa
            queda invisible aunque el JS sí se haya ejecutado bien.
            La altura normal viene de Web/css/dashboard.css (#mapa-resumen),
            pero aquí además se deja un "style" en línea como respaldo, por
            si esa hoja de estilos no se copió o no está enlazada bien en tu
            proyecto local. Si el mapa sigue sin verse, revisa en las
            herramientas de desarrollador del navegador (pestaña Network)
            si dashboard.css está dando error 404.
        -->
        <div id="mapa-resumen" style="height:480px; border-radius:12px;"></div>
    </div>

    <div class="dashboard-sidebar">

        <div class="card">
            <div class="legend-title">Leyenda del Mapa</div>
            <div class="legend-item">
                <span class="legend-dot zoocriadero"></span>
                <span>Zoocriadero</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot deposito"></span>
                <span>Depósitos</span>
            </div>
        </div>

        <div>
            <div class="stats-title">Recuentos Rápidos</div>
            <div class="stats-row">
                <div class="stat-card zoocriadero">
                    <div class="stat-label">Zoocriaderos<br>Activos:</div>
                    <div class="stat-value"><?php echo $totalZoocriaderosActivos; ?></div>
                </div>
                <div class="stat-card deposito">
                    <div class="stat-label">Depósitos<br>Registrados:</div>
                    <div class="stat-value"><?php echo $totalDepositosRegistrados; ?></div>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    /* ================================================================
     * DATOS DEL MAPA
     * ----------------------------------------------------------------
     * $zoocriaderos y $depositos vienen de PHP (arreglos de ejemplo por
     * ahora). PHP los convierte a JSON con json_encode() y quedan como
     * arreglos normales de JavaScript, listos para recorrer con forEach.
     * ================================================================ */
    const zoocriaderos = <?php echo json_encode($zoocriaderos, JSON_UNESCAPED_UNICODE); ?>;
    const depositos = <?php echo json_encode($depositos, JSON_UNESCAPED_UNICODE); ?>;

    /* ================================================================
     * CREAR EL MAPA BASE
     * ----------------------------------------------------------------
     * L.map('mapa-resumen', {...}) busca el <div id="mapa-resumen">
     * en el HTML y convierte ESE div en el mapa. Por eso el div necesita
     * tener una altura (ver el comentario junto al div más arriba); si
     * la altura es 0, aquí no habrá ningún error en la consola, pero
     * visualmente no se ve nada.
     *
     * center: [lat, lng] con el que arranca el mapa (Cali, por ahora fijo).
     * zoom: qué tan acercado empieza (13 muestra casi toda la ciudad).
     * dragging: true permite arrastrar el mapa con el mouse (que sea
     *           "movible", como pediste).
     * ================================================================ */

    const mapa = L.map('mapa-resumen', {
        center: [3.4372, -76.5320],
        zoom: 13,
        dragging: true,      // mapa movible
        scrollWheelZoom: true,
        zoomControl: true
    });

    /* ----------------------------------------------------------------
     * CAPA DE TILES (las imágenes del mapa en sí)
     * L.tileLayer descarga las "baldosas" (imágenes cuadradas) del mapa
     * desde los servidores públicos de OpenStreetMap y las va pegando
     * según el zoom/posición. .addTo(mapa) es lo que realmente las
     * dibuja sobre el div del mapa.
     * ---------------------------------------------------------------- */
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapa);

    /* ----------------------------------------------------------------
     * ÍCONOS PERSONALIZADOS (pines de colores)
     * L.divIcon crea un ícono a partir de un pedacito de HTML/CSS en vez
     * de una imagen. El color real lo pone dashboard.css a través de la
     * variable --pin-color (así reutilizamos la misma forma de pin para
     * zoocriadero, depósito y para el resultado de la búsqueda).
     * ---------------------------------------------------------------- */

    function crearIconoPin(colorVar) {
        return L.divIcon({
            className: 'mapa-pin-icon',
            html: `<div class="pin" style="--pin-color: var(${colorVar})"></div>`,
            iconSize: [24, 32],
            iconAnchor: [12, 32],   // la puntita del pin señala exactamente el punto
            popupAnchor: [0, -30]
        });
    }

    const iconoZoocriadero = crearIconoPin('--zoocriadero-color'); // azul
    const iconoDeposito = crearIconoPin('--deposito-color');       // naranja

    /* ----------------------------------------------------------------
     * DIBUJAR LOS MARCADORES FIJOS (zoocriaderos y depósitos)
     * Por cada punto del arreglo: se crea un marcador en [lat, lng], se
     * le pone una etiqueta visible todo el tiempo (bindTooltip permanent)
     * con el código, y un popup (bindPopup) que aparece al hacer clic,
     * con el detalle completo.
     * ---------------------------------------------------------------- */
    function agregarMarcadores(lista, icono, tipoLabel) {
        lista.forEach(function (punto) {
            const marcador = L.marker([punto.lat, punto.lng], { icon: icono }).addTo(mapa);

            marcador.bindTooltip(punto.codigo, {
                permanent: true,
                direction: 'top',
                offset: [0, -28],
                className: 'pin-tooltip'
            });

            marcador.bindPopup(
                `<b>${tipoLabel}</b><br>${punto.codigo}<br>${punto.direccion}`
            );
        });
    }

    agregarMarcadores(zoocriaderos, iconoZoocriadero, 'Zoocriadero');
    agregarMarcadores(depositos, iconoDeposito, 'Depósito');

    /* ================================================================
     * BUSCADOR DE DIRECCIONES (geocodificación)
     * ----------------------------------------------------------------
     *  escribas una dirección con
     * nomenclatura colombiana (Cra, Cll, Av, Dg, Tv, etc.) y el mapa
     * la ubique solo, sin que tú tengas que buscar las coordenadas a
     * mano. Cuando exista el formulario real de Zoocriadero/Depósito,
     * este mismo bloque puede reutilizarse para autocompletar lat/lng
     * antes de guardar en la BD.
     *
     * ¿Cómo se hace la búsqueda?
     *  Tomamos el texto que escribiste en el input.
     * Si no mencionas "Cali", se lo agregamos, para que Nominatim
     *    no busque en cualquier otra ciudad/país.
     * Le hacemos fetch() a Nominatim (el geocodificador gratuito de
     *    OpenStreetMap: https://nominatim.org), que nos responde una
     *    lista de posibles lugares en JSON; tomamos el primero (limit=1).
     * Con lat/lng de la respuesta, ponemos un pin verde en el mapa,
     *    centramos la vista ahí y hacemos zoom.
     *
     * ¿Por qué a veces las coordenadas que devuelve no son exactas?
     * Nominatim es un buscador de propósito general: funciona muy bien
     * con direcciones "formales" que ya están bien mapeadas en
     * OpenStreetMap, pero con la nomenclatura de Cali (carrera/calle +
     * número + "-" + complemento) a veces NO encuentra el predio exacto
     * y en su lugar devuelve el centro de una zona más amplia (un barrio,
     * una comuna, etc.) — por eso el resultado puede decir algo genérico
     * como "Cali, Sur, Valle del Cauca..." en vez de la dirección puntual.
     * Además, texto extra que no es parte de la nomenclatura (por
     * ejemplo "p6" de "piso 6") puede confundir al buscador.
     * Por esto se agregó el punto (7): poder arrastrar el pin para
     * corregir la ubicación a mano cuando el buscador no da con el
     * punto exacto.
     * ================================================================ */
    const inputDireccion = document.getElementById('input-direccion');
    const btnBuscarDireccion = document.getElementById('btn-buscar-direccion');
    const direccionResultado = document.getElementById('direccion-resultado');
    let marcadorBusqueda = null; // aquí se guarda el pin verde de la búsqueda

    const iconoBusqueda = L.divIcon({
        className: 'mapa-pin-icon',
        html: '<div class="pin" style="--pin-color:#16a34a"></div>', // verde = resultado de búsqueda
        iconSize: [24, 32],
        iconAnchor: [12, 32],
        popupAnchor: [0, -30]
    });

    // Tipos de resultado que Nominatim considera "área amplia" y no una
    // dirección puntual (barrio, comuna, ciudad, etc.). Si el resultado
    // es de uno de estos tipos, avisamos que puede no ser exacto.
    const TIPOS_IMPRECISOS = ['suburb', 'city_district', 'neighbourhood', 'quarter', 'city', 'town', 'village', 'state_district'];

    async function buscarDireccion() {
        const texto = inputDireccion.value.trim();
        if (!texto) {
            direccionResultado.textContent = 'Escribe una dirección para buscar.';
            return;
        }

        direccionResultado.textContent = 'Buscando...';

        // si el usuario no menciona la ciudad, se la agregamos.
        const consulta = /cali/i.test(texto) ? texto : `${texto}, Cali, Colombia`;

        try {
            //  petición a Nominatim.
            // - format=json           -> queremos la respuesta en JSON
            // - limit=1                -> solo el resultado más relevante
            // - countrycodes=co        -> restringe la búsqueda a Colombia
            // - addressdetails=1       -> pide el tipo de lugar encontrado
            const url = 'https://nominatim.openstreetmap.org/search'
                + '?format=json&limit=1&countrycodes=co&addressdetails=1'
                + '&q=' + encodeURIComponent(consulta);

            const respuesta = await fetch(url, { headers: { 'Accept-Language': 'es' } });
            const datos = await respuesta.json();

            if (!datos.length) {
                direccionResultado.textContent = 'No se encontró esa dirección. Prueba variando el formato (Cra, Cll, Av, Dg, Tv...) o quita datos que no sean parte de la dirección (piso, apto, etc.).';
                return;
            }

            const lugar = datos[0];
            const lat = parseFloat(lugar.lat);
            const lng = parseFloat(lugar.lon);

            // dibujar/mover el pin verde.
            if (marcadorBusqueda) {
                mapa.removeLayer(marcadorBusqueda);
            }

            marcadorBusqueda = L.marker([lat, lng], {
                icon: iconoBusqueda,
                draggable: true   //  se puede arrastrar para corregir la posición
            }).addTo(mapa);

            marcadorBusqueda.bindPopup('<b>Dirección buscada</b><br>' + lugar.display_name).openPopup();
            mapa.setView([lat, lng], 17);

            // Aviso de precisión: si Nominatim solo encontró una zona
            // amplia (no la dirección puntual), lo dejamos claro.
            const esImpreciso = TIPOS_IMPRECISOS.includes(lugar.addresstype) || TIPOS_IMPRECISOS.includes(lugar.type);
            const aviso = esImpreciso
                ? ' ⚠️ Este resultado es una zona aproximada, no el predio exacto. Arrastra el pin verde hasta el punto correcto.'
                : '';

            direccionResultado.innerHTML = 'Encontrado: ' + lugar.display_name
                + ' (lat: ' + lat.toFixed(6) + ', lng: ' + lng.toFixed(6) + ')' + aviso;

            // Al soltar el pin en una nueva posición, mostramos las
            // coordenadas corregidas a mano, para poder copiarlas.
            marcadorBusqueda.on('dragend', function () {
                const posicion = marcadorBusqueda.getLatLng();
                direccionResultado.textContent = 'Ubicación ajustada manualmente — lat: '
                    + posicion.lat.toFixed(6) + ', lng: ' + posicion.lng.toFixed(6);
            });
        } catch (error) {
            direccionResultado.textContent = 'Ocurrió un error al buscar la dirección. Intenta de nuevo.';
        }
    }

    btnBuscarDireccion.addEventListener('click', buscarDireccion);
    inputDireccion.addEventListener('keydown', function (evento) {
        if (evento.key === 'Enter') {
            evento.preventDefault();
            buscarDireccion();
        }
    });
</script>

<?php include '../partials/footer_app.php'; ?>
