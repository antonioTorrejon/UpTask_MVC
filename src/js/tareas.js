//IIFE: sintaxis que sirve para proteger las variables en los archivos de JS y que solo se utilicen en ese archivo de JS y no se mezcle con otros

(function(){

    obtenerTareas();
    let tareas = []; //Creamos una variable global para utilizar el virtual DOM, de tal modo que al agregar una nueva tarea no haya que hacer una consulta nueva y construir de nuevo todo el template
    let filtradas = [];

    //Botón para mostrar el modal de agregar tarea
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', function(){
        mostrarFormulario(false);
    });

    //Filtros de búsqueda
    const filtros = document.querySelectorAll('#filtros input[type="radio"]');
    //Al ser un querySelectorAll hay que iterar sobre los items
    filtros.forEach(radio=>{
        radio.addEventListener('input', filtrarTareas);
    })

    function filtrarTareas(e){
        const filtro =e.target.value;

        if(filtro !== ''){
            filtradas = tareas.filter(tarea => tarea.estado === filtro);
        } else {
            filtradas = [];
        }

        mostrarTareas();
    }


    async function obtenerTareas(){
        try {
            const id = obtenerProyecto();
            const url = `api/tareas?id=${id}`;
            const respuesta = await fetch (url);
            const resultado = await respuesta.json();

            tareas = resultado.tareas;
            mostrarTareas();

        } catch (error) {
            console.log(error);
        }
    }

    function mostrarTareas(){
        limpiarTareas();
        //Para evitar que si están todas las tareas pendientes o todas completas, al filtrar el array de filtradas aparezca vacio y pase a la siguiente condición, mostrando todas las tareas, calculamos antes el número de tareas pendientes y el de completas

        totalPendientes();
        totalCompletadas();

        const arrayTareas = filtradas.length ? filtradas : tareas; //Comparador ternario

        if(arrayTareas.length === 0){
            const contenedorTareas = document.querySelector('#listado-tareas');

            const textoNoTareas = document.createElement('LI');
            textoNoTareas.textContent = 'No hay tareas';
            textoNoTareas.classList.add('no-tareas');

            contenedorTareas.appendChild(textoNoTareas);
            return;
        }

        const estados = {
            0: 'Pendiente',
            1: 'Completa'
        };

        arrayTareas.forEach(tarea => {
            const contenedorTarea = document.createElement('LI');
            contenedorTarea.dataset.tareaId = tarea.id;
            contenedorTarea.classList.add('tarea');

            const nombreTarea = document.createElement('P');
            nombreTarea.textContent = tarea.nombre;
            nombreTarea.ondblclick = function (){
                mostrarFormulario(true, {...tarea});
            };

            const opcionesDiv = document.createElement('DIV');
            opcionesDiv.classList.add('opciones');

            //Botones
            const btnEstadoTarea = document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`)
            btnEstadoTarea.textContent = estados[tarea.estado];
            btnEstadoTarea.dataset.estadoTarea = tarea.estado;
            btnEstadoTarea.ondblclick = function (){
                cambiarEstadoTarea({...tarea});
            };

            const btnElmininarTarea = document.createElement('BUTTON');
            btnElmininarTarea.classList.add('eliminar-tarea');
            btnElmininarTarea.dataset.idTarea = tarea.id;
            btnElmininarTarea.textContent = 'Eliminar';
            btnElmininarTarea.ondblclick = function (){
                confirmarElmininarTarea({...tarea});
            }

            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnElmininarTarea);

            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);

            const listadoTareas = document.querySelector('#listado-tareas');
            listadoTareas.appendChild(contenedorTarea);

        });
    }

    function totalPendientes(){
        const totalPendientes = tareas.filter(tarea => tarea.estado === "0"); //Nos va a devolver cuantas tareas están pendientes

        const pendientesRadio = document.querySelector("#pendientes");
        if(totalPendientes.length === 0){
            pendientesRadio.disabled = true;
        } else{
            pendientesRadio.disabled = false;
        }
    }

    function totalCompletadas(){
        const totalCompletadas = tareas.filter(tarea => tarea.estado === "1"); //Nos va a devolver cuantas tareas están completadas

        const completadasRadio = document.querySelector("#completadas");
        if(totalCompletadas.length === 0){
            completadasRadio.disabled = true;
        } else{
            completadasRadio.disabled = false;
        }
    }


    function mostrarFormulario(editar = false, tarea = {}){
        console.log(tarea);
        const modal = document.createElement('DIV');
        modal.classList.add('modal');
        modal.innerHTML = `
           <form class="formulario nueva-tarea">
                <legend>${editar ? 'Editar tarea' : 'Añade una nueva tarea'}</legend>
                <div class="campo">
                    <label>Tarea</label>
                    <input
                    type="text"
                    name="tarea"
                    placeholder="${tarea.nombre ? 'Edita la tarea' : 'Añadir tarea al proyecto actual'}"
                    id="tarea"
                    value="${tarea.nombre ? tarea.nombre : ''}"
                    />
                </div>
                <div class="opciones">
                    <input 
                        type="submit" 
                        class="submit-nueva-tarea" 
                        value="${tarea.nombre ? 'Guardar cambios' : 'Añadir tarea'}"
                    />
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        `;

        setTimeout(() => {
            const formulario = document.querySelector('.formulario');
            formulario.classList.add('animar');
        }, 100);

        modal.addEventListener('click', function(e){
            e.preventDefault(); //Prevenimos las opciones por default

            //Delegation: se trata de identificar a que elemento hemos dado click. Lo usamos cuando el templte lo hemos hecho con innerHTML, es decir con JS
            if (e.target.classList.contains('cerrar-modal')) {
                const formulario = document.querySelector('.formulario');
                formulario.classList.add('cerrar');
                setTimeout(() => {
                    modal.remove(); 
                }, 500);
            }
            if(e.target.classList.contains('submit-nueva-tarea')) {
                const nombreTarea = document.querySelector('#tarea').value.trim(); //El trim nos elimina espacios al inicio y al final
                if(nombreTarea === ''){
                    //Mostrar alerta de error
                    mostrarAlerta('El nombre de la tarea es obligatorio', 'error', document.querySelector('.formulario legend'));
                    return;
                }

                if(editar){
                    tarea.nombre = nombreTarea;
                    actualizarTarea(tarea);
                } else{
                    agregarTarea(nombreTarea);
                }
            } 
        })

        document.querySelector('.dashboard').appendChild(modal);
    }


    //Muestra un mensaje en la interfaz
    function mostrarAlerta(mensaje, tipo, referencia) {
        //Previene la creación de multiples alertas
        const alertaPrevia = document.querySelector('.alerta');
        if(alertaPrevia) {
            alertaPrevia.remove();
        }

        const alerta = document.createElement('DIV');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;

        //Inserta la alerta antes del legend. Si utilizamos un appenChild lo inserta dentro del legend, pero no podemos tener un div dentro de un legend
        //referencia.appendChild(alerta);

        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

        //Eliminar la alerta pasado un tiempo
        setTimeout(() => {
            alerta.remove();
        }, 5000);

    }

    //Consultar el servidor para añadir una nueva tarea al proyecto actual
    async function agregarTarea (tarea) {
        //Construimos la petición. Comunicamos PHP con JS
        const datos = new FormData(); 
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea'; //url a la que enviamos la petición
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            
            const resultado = await respuesta.json();

            mostrarAlerta(resultado.mensaje, resultado.tipo, document.querySelector('.formulario legend'));

            if(resultado.tipo === 'exito'){
                const modal = document.querySelector('.modal');
                setTimeout(() => {
                    modal.remove();
                }, 3000);

                //Agregar el objeto de tarea al global de tareas
                const tareaObj = {
                    id: String(resultado.id),
                    nombre: tarea,
                    estado: "0",
                    proyectoId: resultado.proyectoId
                }

                tareas = [...tareas, tareaObj];
                mostrarTareas();
            }

        } catch (error) {
            console.log(error);
        }
    }

    function cambiarEstadoTarea(tarea){
        const nuevoEstado = tarea.estado === "1" ? "0" : "1";
        tarea.estado = nuevoEstado;

        actualizarTarea(tarea);

    }

    async function actualizarTarea(tarea){
        const {estado, id, nombre, proyectoId} = tarea;
        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea/actualizar';

            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });

            const resultado = await respuesta.json();

            if(resultado.respuesta.tipo === 'exito'){
                Swal.fire(
                    resultado.respuesta.mensaje, 
                    resultado.respuesta.mensaje, 
                    'success'
                );

                const modal = document.querySelector('.modal');
                if(modal){
                    modal.remove();
                }

                tareas = tareas.map(tareaMemoria => {
                    if(tareaMemoria.id === id){
                        tareaMemoria.estado = estado;
                        tareaMemoria.nombre = nombre;
                    }

                    return tareaMemoria;
                });

                mostrarTareas();
            }
        } catch (error) {
            console.log(error);
        }

    }

    function confirmarElmininarTarea(tarea){
        Swal.fire({
            title: '¿Seguro que quieres eliminar la tarea?',
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'No'
        }).then((result) => {
        if (result.isConfirmed) {
            eliminarTarea(tarea);
        }
        })
    }

    async function eliminarTarea(tarea){
        const {estado, id, nombre} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea/eliminar';

            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });

            const resultado = await respuesta.json();
            if(resultado.resultado ){
/*                 mostrarAlerta(
                        resultado.mensaje, 
                        resultado.tipo, 
                        document.querySelector('.contenedor-nueva-tarea')
                ); */

                Swal.fire('Eliminado!', resultado.mensaje, 'success');
                
                tareas = tareas.filter( tareaMemoria => tareaMemoria.id !== tarea.id); //Filtra todos los elementos mostrando todos exceptos el que he clicado y por tanto eliminado
                mostrarTareas();
            }

        } catch (error) {
            console.log(error);
        }
    }

    function obtenerProyecto(){
        //Leemos lo que hay en la URL para conseguir la url del proyecto actual
        const proyectoParams = new URLSearchParams(window.location.search);
        const proyecto = Object.fromEntries(proyectoParams.entries());
        return proyecto.id;
    }

    function limpiarTareas(){
        const listadoTareas = document.querySelector('#listado-tareas');
        
        while(listadoTareas.firstChild){
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }

})();