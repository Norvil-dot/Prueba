// --- Ejecutar cuando el DOM esté listo ---
document.addEventListener('DOMContentLoaded', () => {
    
    // --- VARIABLES GLOBALES ---
    // URL de nuestra API (asegúrate que la ruta sea correcta)
    const API_URL = '../backend/api/denuncias_api.php';

    // Elementos del DOM
    const tablaBody = document.getElementById('tablaDenunciasBody');
    const formulario = document.getElementById('formularioDenuncia');
    const modal = new bootstrap.Modal(document.getElementById('modalNuevoReporte'));
    const modalTitulo = document.getElementById('modalTitulo');
    const denunciaIdInput = document.getElementById('denunciaId');

    // Modal de eliminación
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    let idParaEliminar = null; // Variable para guardar el ID a eliminar

    // Paginación y búsqueda
    const paginacionContainer = document.getElementById('paginacion');
    const formBusqueda = document.getElementById('formBusqueda');
    const inputBusqueda = document.getElementById('inputBusqueda');
    let paginaActual = 1;
    let busquedaActual = '';

    // Sidebar Toggle
    const toggleButton = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    // --- FUNCIÓN PRINCIPAL: CARGAR DENUNCIAS ---
    async function cargarDenuncias(pagina = 1, busqueda = '') {
        paginaActual = pagina;
        busquedaActual = busqueda;
        tablaBody.innerHTML = '<tr><td colspan="8" class="text-center">Cargando...</td></tr>';

        try {
            // Petición GET a la API con parámetros de paginación y búsqueda
            const response = await fetch(`${API_URL}?accion=listar&pagina=${pagina}&busqueda=${busqueda}`);
            if (!response.ok) {
                throw new Error('Error al cargar los datos: ' + response.statusText);
            }
            
            const data = await response.json();

            // Limpiar tabla
            tablaBody.innerHTML = '';

            if (data.denuncias.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron denuncias.</td></tr>';
            } else {
                // Iterar y agregar filas a la tabla
                data.denuncias.forEach(denuncia => {
                    const fila = `
                        <tr>
                            <td>
                                <button class="btn btn-warning btn-sm btn-editar" data-id="${denuncia.id}" data-bs-toggle="modal" data-bs-target="#modalNuevoReporte">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="${denuncia.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                            <td>${denuncia.id}</td>
                            <td>${denuncia.titulo}</td>
                            <td>${denuncia.descripcion}</td>
                            <td>${denuncia.ubicacion}</td>
                            <td>${denuncia.ciudadano}</td>
                            <td>${new Date(denuncia.fecha_registro).toLocaleDateString()}</td>
                            <td>${obtenerBadgeEstado(denuncia.estado)}</td>
                        </tr>
                    `;
                    tablaBody.innerHTML += fila;
                });
            }
            
            // Renderizar paginación
            mostrarPaginacion(data.paginas_totales, data.pagina);

        } catch (error) {
            console.error(error);
            tablaBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">${error.message}</td></tr>`;
        }
    }

    // --- FUNCIÓN PARA MOSTRAR PAGINACIÓN ---
    function mostrarPaginacion(paginasTotales, paginaActual) {
        paginacionContainer.innerHTML = '';
        
        // Botón "Anterior"
        paginacionContainer.innerHTML += `
            <li class="page-item ${paginaActual <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-pagina="${paginaActual - 1}">Anterior</a>
            </li>
        `;

        // Números de página
        for (let i = 1; i <= paginasTotales; i++) {
            paginacionContainer.innerHTML += `
                <li class="page-item ${i === paginaActual ? 'active' : ''}">
                    <a class="page-link" href="#" data-pagina="${i}">${i}</a>
                </li>
            `;
        }

        // Botón "Siguiente"
        paginacionContainer.innerHTML += `
            <li class="page-item ${paginaActual >= paginasTotales ? 'disabled' : ''}">
                <a class="page-link" href="#" data-pagina="${paginaActual + 1}">Siguiente</a>
            </li>
        `;
    }

    // --- FUNCIÓN HELPER: BADGE DE ESTADO ---
    function obtenerBadgeEstado(estado) {
        switch (estado.toLowerCase()) {
            case 'pendiente':
                return `<span class="badge bg-warning text-dark">${estado}</span>`;
            case 'en proceso':
                return `<span class="badge bg-info text-dark">${estado}</span>`;
            case 'resuelto':
                return `<span class="badge bg-success">${estado}</span>`;
            default:
                return `<span class="badge bg-secondary">${estado}</span>`;
        }
    }

    // --- FUNCIÓN HELPER: RESETEAR FORMULARIO ---
    function resetFormulario() {
        formulario.reset();
        denunciaIdInput.value = ''; // Limpiar el ID oculto
        modalTitulo.textContent = 'Nuevo Reporte de Denuncia';
    }

    // --- MANEJO DE EVENTOS ---

    // 1. Cargar denuncias al iniciar
    cargarDenuncias();

    // 2. Evento de Búsqueda
    formBusqueda.addEventListener('submit', (e) => {
        e.preventDefault(); // Evitar que la página se recargue
        cargarDenuncias(1, inputBusqueda.value);
    });

    // 3. Evento de Paginación (Click en los links)
    paginacionContainer.addEventListener('click', (e) => {
        e.preventDefault();
        if (e.target.tagName === 'A' && e.target.dataset.pagina) {
            const nuevaPagina = parseInt(e.target.dataset.pagina);
            if (nuevaPagina) {
                cargarDenuncias(nuevaPagina, busquedaActual);
            }
        }
    });

    // 4. Evento de clic en la tabla (para Editar y Eliminar)
    tablaBody.addEventListener('click', async (e) => {
        // Target más cercano que sea un botón de editar
        if (e.target.closest('.btn-editar')) {
            const id = e.target.closest('.btn-editar').dataset.id;
            await cargarDatosFormulario(id);
        }

        // Target más cercano que sea un botón de eliminar
        if (e.target.closest('.btn-eliminar')) {
            idParaEliminar = e.target.closest('.btn-eliminar').dataset.id;
            modalEliminar.show(); // Mostrar modal de confirmación
        }
    });

    // 5. Cargar datos en el formulario para EDITAR
    async function cargarDatosFormulario(id) {
        try {
            const response = await fetch(`${API_URL}?accion=obtener&id=${id}`);
            const denuncia = await response.json();

            // Llenar el formulario
            modalTitulo.textContent = 'Editar Reporte de Denuncia';
            denunciaIdInput.value = denuncia.id;
            document.getElementById('titulo').value = denuncia.titulo;
            document.getElementById('descripcion').value = denuncia.descripcion;
            document.getElementById('ubicacion').value = denuncia.ubicacion;
            document.getElementById('estado').value = denuncia.estado;
            document.getElementById('ciudadano').value = denuncia.ciudadano;
            document.getElementById('telefono_ciudadano').value = denuncia.telefono_ciudadano;

        } catch (error) {
            console.error('Error al cargar datos para editar:', error);
            alert('No se pudieron cargar los datos de la denuncia.');
        }
    }

    // 6. Evento de envío del Formulario (Crear y Actualizar)
    formulario.addEventListener('submit', async (e) => {
        e.preventDefault(); // Evitar envío tradicional

        const id = denunciaIdInput.value;
        const esEdicion = id ? true : false;
        
        // Construimos los datos del formulario
        const formData = new FormData(formulario);

        // Determinamos la acción (URL) y el método
        let url = API_URL;
        let metodo = 'POST'; // Usamos POST para ambos por simplicidad

        if (esEdicion) {
            url += '?accion=actualizar';
        } else {
            url += '?accion=crear';
        }

        try {
            const response = await fetch(url, {
                method: metodo,
                body: formData // FormData se encarga de los headers correctos
            });

            const resultado = await response.json();

            if (response.ok) {
                modal.hide(); // Ocultar el modal
                resetFormulario();
                cargarDenuncias(paginaActual, busquedaActual); // Recargar la tabla
                alert(resultado.mensaje);
            } else {
                alert(resultado.error || 'Ocurrió un error');
            }

        } catch (error) {
            console.error('Error al guardar:', error);
            alert('Error de conexión al guardar la denuncia.');
        }
    });

    // 7. Resetear formulario cuando el modal se cierra
    document.getElementById('modalNuevoReporte').addEventListener('hidden.bs.modal', () => {
        resetFormulario();
    });

    // 8. Confirmación de Eliminación
    btnConfirmarEliminar.addEventListener('click', async () => {
        if (!idParaEliminar) return;

        try {
            // Petición DELETE a la API
            const response = await fetch(`${API_URL}?accion=eliminar&id=${idParaEliminar}`, {
                method: 'DELETE'
            });

            const resultado = await response.json();

            if (response.ok) {
                modalEliminar.hide();
                cargarDenuncias(1, ''); // Recargar todo desde la pág 1
                alert(resultado.mensaje);
                idParaEliminar = null;
            } else {
                alert(resultado.error || 'Error al eliminar');
            }

        } catch (error) {
            console.error('Error al eliminar:', error);
            alert('Error de conexión al eliminar.');
        }
    });

if (toggleButton && sidebar) {
    toggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('show'); // ← ESTA ES LA CLAVE
    });
}

});