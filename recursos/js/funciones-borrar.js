document.addEventListener('DOMContentLoaded', function () {
    // Log para debug que indica que la página se ha cargado
    console.log('Página cargada, iniciando actualizaciones...');

    // Primera llamada a actualizar estadísticas
    actualizarEstadisticas();

    // Configura actualización automática cada 5 segundos (5000 ms)
    //setInterval(actualizarEstadisticas, 5000);


});

function actualizarEstadisticas() {
    console.log('Iniciando actualización de estadísticas...');

    // Realiza petición AJAX al controlador de estadísticas
    fetch('/teleasistencia/controladores/EstadisticasControlador.php', {
        method: 'GET',  // Método HTTP GET
        headers: {
            'Accept': 'application/json',  // Espera respuesta JSON
            'Cache-Control': 'no-cache'    // Evita caché
        },
        credentials: 'same-origin'  // Incluye cookies en la petición
    })
        .then(response => {
            // Log del estado de la respuesta HTTP
            console.log('Estado de la respuesta:', response.status);
            return response.text();  // Convierte la respuesta a texto
        })
        .then(text => {
            // Log del texto recibido para debug
            console.log('Texto recibido:', text);
            try {
                // Intenta parsear el texto a JSON
                const data = JSON.parse(text);
                console.log('Datos parseados:', data);

                // Mapeo de IDs del DOM con datos recibidos
                const elementos = {
                    'total-beneficiarios': data.totalBeneficiarios,
                    'activos-beneficiarios': data.beneficiariosActivos,
                    'agenda-pendiente': data.citasPendientes,
                    'agenda-completa': data.citasCompletadas,
                    'com-abiertas': data.comunicacionesAbiertas,
                    'com-emergencias': data.comunicacionesEmergencia
                };

                // Actualiza cada elemento del DOM con su valor correspondiente
                Object.entries(elementos).forEach(([id, valor]) => {
                    const elemento = document.getElementById(id);
                    if (elemento) {
                        // Log de actualización exitosa
                        console.log(`Actualizando ${id} con valor ${valor}`);
                        elemento.textContent = valor;
                    } else {
                        // Log de error si no encuentra el elemento
                        console.error(`Elemento no encontrado: ${id}`);
                    }
                });
            } catch (e) {
                // Logs de error si falla el parseo JSON
                console.error('Error al parsear JSON:', e);
                console.error('Texto que causó el error:', text);
            }
        })
        .catch(error => {
            // Log de error si falla la petición
            console.error('Error en la petición:', error);
        });
}


