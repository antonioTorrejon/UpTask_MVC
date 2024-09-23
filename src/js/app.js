const movilMenuBtn = document.querySelector('#movil-menu');
const cerrarMenuBtn = document.querySelector('#cerrar-menu');
const sidebar = document.querySelector('.sidebar')

if(movilMenuBtn){
    movilMenuBtn.addEventListener('click', function(){
        sidebar.classList.add('mostrar');
    });
}

if(cerrarMenuBtn){
    cerrarMenuBtn.addEventListener('click', function(){
        sidebar.classList.add('ocultar');
        setTimeout(() => {
            sidebar.classList.remove('mostrar');
            sidebar.classList.remove('ocultar')
        }, 1000);
    })
}

//Eliminamos la clase de mostrar cuando el tamaño es de tablet o más
const anchoPantalla = document.body.clientWidth; 

window.addEventListener('resize', function(){
    const anchoPantalla = document.body.clientWidth; 
    if(anchoPantalla >= 768) {
        sidebar.classList.remove('mostrar');
    };
})