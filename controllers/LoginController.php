<?php 

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login (Router $router){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            
            $alertas = $auth -> validarLogin();

            if(empty($alertas)){
                //Verificamos que el usuario introducido existe
                $usuario = Usuario::where('email', $auth->email);

                if(!$usuario){
                    Usuario::setAlerta('error', 'El usuario introducido no existe');
                }
                else if (!$usuario->confirmado) {
                    Usuario::setAlerta('error', 'El usuario introducido no ha sido confirmado');
                }else {
                    //Si pasa esas dos validaciones, tenemos que intentar autenticar mediante el password
                    if(password_verify($_POST['password'], $usuario->password)){
                        //iniciar sesión del usuario
                        session_start();
                        $_SESSION ['id'] = $usuario -> id;
                        $_SESSION ['nombre'] = $usuario -> nombre;
                        $_SESSION ['email'] = $usuario -> email;
                        $_SESSION ['login'] = true;

                        //Redireccionar
                        header('Location: /dashboard');

                    }else{
                        Usuario::setAlerta('error', 'Contraseña incorrecta');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        //Render a la visa
        $router->render('auth/login', [
            'titulo' => 'Iniciar sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout (){
        session_start();
        $_SESSION=[];
        header('Location: /');
    }

    public static function crear (Router $router){
        $alertas = [];
        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario -> validarCuentaNueva();

            if(empty($alertas)){
                //Comprobar si el usuario ya existe por el mail mediante el método de ActiveRecord where
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario){
                    Usuario::setAlerta('error', 'El usuario ya está registrado');
                    $alertas = Usuario::getAlertas();
                }
                else{
                    //Hasheamos la contraseña
                    $usuario->hashPassword();

                    //Eliminamos password2 porque no lo necesitamos
                    unset($usuario->password2);

                    //Generamos un token
                    $usuario->crearToken();

                    //Crear un nuevo usuario en la base de datos
                    $resultado = $usuario->guardar();

                    //Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email -> enviarConfirmacion();

                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
        }

        //Render a la visa
        $router->render('auth/crear', [
            'titulo' => 'Crea tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide (Router $router){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario -> validarEmail();

            if(empty($alertas)){
                //Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);
                //Comprobamos si hay un usuario y si está confirmado. 
                if($usuario && $usuario -> confirmado = "1"){
                //Generamos un nuevo token
                $usuario -> crearToken();
                unset($usuario->password2);

                //Actualizamos el usuario en le BBDD
                $usuario -> guardar();

                //Enviamos el email
                $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                $email -> enviarInstrucciones();

                //Imprimimos la alerta
                Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email.');

                    
                } else{
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');

                }
            }
        }

        $alertas = Usuario::getAlertas();

        //Render a la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi contraseña',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer (Router $router){
        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token){
            header('Location: /');
        }

        //Identificar al usuario con ese token
        $usuario = Usuario::where('token', $token);
        
        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token no válido');
            $mostrar = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //Añadir el nuevo password
            $usuario -> sincronizar($_POST);

            //Validar la nueva constraseña
            $alertas = $usuario -> validarPassword();

            if(empty($alertas)){
                //Hashear la contraseña
                $usuario -> hashPassword();
                unset($usuario->password2);

                //Eliminar el token
                $usuario -> token = null;

                //Guardar el cambio
                $resultado = $usuario -> guardar();

                //Redireccionar
                if($resultado){
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        //Render a la vista
        $router->render('auth/reestablecer', [
            'titulo'=> 'Reestablecer contraseña',
            'alertas' => $alertas, 
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje (Router $router){
        //Render a la vista
        $router->render('auth/mensaje', [
            'titulo'=> 'Cuenta creada con éxito',
        ]);
    }

    public static function confirmar (Router $router){
        //Leemos el token que nos trae la URL y lo almacenamos momentaneamente en una variable
        $token = s($_GET['token']);
        if(!$token){
            header('Location: /');
        }

        //Comparar token que viene en la URL con los que podamos tener en la BBDD
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            $usuario -> confirmado = 1;
            $usuario -> token = null;
            unset($usuario -> password2);
            
            $usuario -> guardar();

            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
        }

        $alertas = Usuario::getAlertas();

        //Render a la vista
        $router->render('auth/confirmar', [
            'titulo'=> 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}