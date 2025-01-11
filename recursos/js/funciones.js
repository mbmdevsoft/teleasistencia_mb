
function eliminarBeneficiario(id, nombre, expediente) {
    Swal.fire({
        title: '¿Eliminar beneficiario?',
        html: `¿Está seguro de que desea eliminar al beneficiario <strong>${nombre}</strong>?<br>
                  Expediente: ${expediente}<br><br>
                  <span style="color: red">Esta acción eliminará todos los datos asociados al beneficiario</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef5a64',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(<?= $_SESSION['DIR_HOME'] ?> + 'controladores/BeneficiarioControlador.php', {  // ESTO DA ERROR PORQUE ESTA EN UN FICHERO .JS, PERO DEJA DE DARLO CUANDO SE METE LA FUNCION EN UN FICHERO .PHP CON LAS ETIQUETAS <script> function {} </script>
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=eliminar&id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            html: `El beneficiario <strong>${nombre}</strong> ha sido eliminado correctamente`,
                            icon: 'success',
                            confirmButtonColor: '#ef5a64'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo eliminar el beneficiario: ' + (data.error || 'Error desconocido'),
                            icon: 'error',
                            confirmButtonColor: '#ef5a64'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar el beneficiario',
                        icon: 'error',
                        confirmButtonColor: '#ef5a64'
                    });
                });
        }
    });
}

















function eliminarContacto(id, beneficiarioId, nombre, nifnie) {
    Swal.fire({
        title: '¿Eliminar contacto?',
        html: `¿Está seguro de que desea eliminar el contacto <strong>${nombre}</strong>?<br>Direccion: ${nifnie}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef5a64',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("<?= $_SESSION['DIR_HOME'] ?> + 'controladores/ContactoControlador.php?action=eliminar&id=' + id")
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            html: `El contacto <strong>${nombre}</strong> ha sido eliminado correctamente`,
                            icon: 'success',
                            confirmButtonColor: '#ef5a64'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo eliminar el contacto: ' + (data.error || 'Error desconocido'),
                            icon: 'error',
                            confirmButtonColor: '#ef5a64'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar el contacto',
                        icon: 'error',
                        confirmButtonColor: '#ef5a64'
                    });
                });
        }
    });
}

function eliminarAgenda(id, descripcion) {
    Swal.fire({
        title: '¿Eliminar agenda?',
        html: `¿Está seguro de que desea eliminar la agenda?<br><strong>${descripcion}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef5a64',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("<?= $_SESSION['DIR_HOME'] ?> + 'controladores/AgendaControlador.php?action=eliminar&id=' + id")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminada!',
                            text: 'La agenda ha sido eliminada correctamente',
                            icon: 'success',
                            confirmButtonColor: '#ef5a64'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo eliminar la agenda: ' + (data.error || 'Error desconocido'),
                            icon: 'error',
                            confirmButtonColor: '#ef5a64'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar la agenda',
                        icon: 'error',
                        confirmButtonColor: '#ef5a64'
                    });
                });
        }
    });
}

function eliminarComunicacion(id, descripcion) {
    Swal.fire({
        title: '¿Eliminar comunicación?',
        html: `¿Está seguro de que desea eliminar la comunicación?<br><strong>${descripcion}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef5a64',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("<?= $_SESSION['DIR_HOME'] ?> + 'controladores/ComunicacionControlador.php?action=eliminar&id=' + id")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminada!',
                            text: 'La comunicación ha sido eliminada correctamente',
                            icon: 'success',
                            confirmButtonColor: '#ef5a64'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo eliminar la comunicación: ' + (data.error || 'Error desconocido'),
                            icon: 'error',
                            confirmButtonColor: '#ef5a64'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar la comunicación',
                        icon: 'error',
                        confirmButtonColor: '#ef5a64'
                    });
                });
        }
    });
}
