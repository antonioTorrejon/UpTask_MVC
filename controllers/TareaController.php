<?php

namespace Controllers;

use Model\Proyecto;
use Model\Tarea;

class TareaController {
    //En este caso no requerimos el router, porque va a ser por medio de una API y por tanto no requerimos vistas
    public static function index(){
        session_start();

        $proyectoId = $_GET['id'];

        if(!$proyectoId){
            header('Location: /dashboard');
        }

        $proyecto = Proyecto::where('url', $proyectoId);

        if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /404');
        }

        $tareas = Tarea::belongsTo('proyectoId', $proyecto->id);

        echo json_encode(['tareas'=>$tareas]);
    }

    public static function crear(){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            session_start();
            $proyectoId = $_POST['proyectoId'];
            $proyecto = Proyecto::where('url', $proyectoId);

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al añadir la tarea'
                ];

                echo json_encode($respuesta);
                return;
            }

            //Instancia y crear la tarea para insertarla en la base de datos
            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;
            $resultado = $tarea -> guardar();
            $respuesta = [
                'tipo' => 'exito',
                'id' => $resultado ['id'],
                'mensaje' => 'Nueva tarea añadida con éxito',
                'proyectoId' => $proyecto->id
            ];
            echo json_encode($respuesta);
        }
    }

    public static function actualizar(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //Validamos que el proyecto existe
            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            session_start();

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al actualizar la tarea'
                ];

                echo json_encode($respuesta);
                return;
            }

            //Instancia la tarea con el nuevo estado
            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;

            $resultado = $tarea -> guardar();

            if($resultado){
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $tarea->id,
                    'proyectoId' => $proyecto->id,
                    'mensaje' => 'Actualizado correctamente'
                ];
                echo json_encode(['respuesta' => $respuesta]);
            }
        }
    }

    public static function eliminar(){
        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Validar que el proyecto exista
            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            session_start();

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al eliminar la tarea'
                ];
                echo json_encode($respuesta);
                return;
            } 

            $tarea = new Tarea($_POST);
            $resultado = $tarea -> eliminar();

            $resultado = [
                'mensaje' => 'Eliminado correctamente',
                'tipo' => 'exito',
                'resultado' => $resultado
            ];

            echo json_encode($resultado);
        
        }
    }
}