<?php

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController{
    public static function index(Router $router){
        session_start();
        isAuth(); //Función global que nos ayuda a proteger las rutas

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        //Render a la visa
        $router->render('/dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router){
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $proyecto = new Proyecto($_POST);

            //Validación 
            $alertas = $proyecto -> validarProyecto();

            if(empty($alertas)){
                //Generamos una URL única
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                //Guardar al creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                //Guardamos el proyecto en la BB DD
                $proyecto->guardar();

                //Redireccionar
                header('Location: /proyecto?id=' . $proyecto->url );
            }
        }

        $router->render('/dashboard/crear-proyecto', [
            'titulo' => 'Crear proyecto',
            'alertas' => $alertas
        ]);

    }

    public static function proyecto(Router $router){
        session_start();
        isAuth();

        //Revisar que la persona que está viendo el proyecto es quien la creo
        $token = $_GET['id'];
        if(!$token){
            header('Location: /dashboard');
        }

        $proyecto = Proyecto::where('url', $token);
        if($proyecto->propietarioId !== $_SESSION['id']){
            header('Location: /dashboard');
        }

        $router->render('/dashboard/proyecto', [
            'titulo' => $proyecto->proyecto,
        ]);

    }

    public static function perfil(Router $router){
        session_start();
        isAuth();

        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario -> sincronizar($_POST);

            $alertas = $usuario->validar_perfil();

            if(empty($alertas)) {
                //Verificar que no hay un email igual
                $existeUsuario = Usuario::where('email', $usuario->email);
                
                if($existeUsuario && $existeUsuario->id !== $usuario->id){
                    //Mostrar mensaje de error
                    Usuario::setAlerta('error', 'Email no válido, ya pertenece a otra cuenta');
                    $alertas = $usuario->getAlertas();
                }else{
                    //Guardar el registro
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Guardado correctamente');
                    $alertas = $usuario->getAlertas();

                    //Asignar el nombre nuevo a la barra
                     $_SESSION ['nombre'] = $usuario->nombre;
                }
            }
        }

        $router->render('/dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);

    }

    public static function cambiar_password(Router $router){
        session_start();
        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = Usuario::find($_SESSION['id']);

            //Sincronizamos datos de la base de datos con los datos del usuario
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();

            if(empty ($alertas)){
                $resultado = $usuario->comprobarPassword();
                if($resultado){
                    //Reasignamos el password_nuevo a la columna de password
                    $usuario->password = $usuario->password_nuevo;

                    //Eliminamos propiedades no necesarias
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);

                    //Hasheamos la nueva contraseña
                    $usuario->hashPassword();

                    //Guardamos en la base de datos la nueva contraseña
                    $resultado = $usuario->guardar();

                    if($resultado){
                        Usuario::setAlerta('exito', 'Contraseña modificada correctamente');
                        $alertas = $usuario->getAlertas();
                    }
                }else{
                    Usuario::setAlerta('error', 'Contraseña incorrecta');
                    $alertas = $usuario->getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar contraseña',
            'alertas' => $alertas
        ]);
    }
}