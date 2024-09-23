<?php

namespace Model;

use Model\ActiveRecord;
use EmptyIterator;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public $id; 
    public $nombre;
    public $email; 
    public $password; 
    public $password2; 
    public $password_actual;
    public $password_nuevo;
    public $token; 
    public $confirmado; 

    public function __construct($args=[])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? null;
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    //Validación login
    public function validarLogin(){
        if(!$this->email){
            self::$alertas['error'][]='El email del usuario es obligatorio';
        }
        if(!$this->password){
            self::$alertas['error'][]='La contraseña no puede estar vacía';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][]='Email no válido';
        }

        return self::$alertas;

    }

    //Validación para cuentas nuevas
    public function validarCuentaNueva(){
        if(!$this->nombre){
            self::$alertas['error'][]='El nombre del usuario es obligatorio';
        }
        if(!$this->email){
            self::$alertas['error'][]='El email del usuario es obligatorio';
        }
        if(!$this->password){
            self::$alertas['error'][]='La contraseña no puede estar vacía';
        }
        if(strlen($this->password) < 6){
            self::$alertas['error'][]='La contraseña debe tener al menos 6 caracteres';
        }
        if($this->password !== $this->password2){
            self::$alertas['error'][]='Las constraseñas introducidas son diferentes';
        }

        return self::$alertas;
    }

    //Validación email para recuperación de contraseña
    public function validarEmail(){
        if(!$this->email){
            self::$alertas['error'][]='El email es obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][]='Email no válido';
        }

        return self::$alertas;
    }

    //Valida unicamente el password
    public function validarPassword(){

        if(!$this->password){
            self::$alertas['error'][]='La contraseña no puede estar vacía';
        }

        if(strlen($this->password) < 6){
            self::$alertas['error'][]='La contraseña debe tener al menos 6 caracteres';
        }

        return self::$alertas;   
    }

    public function validar_perfil(){
        if(!$this->nombre){
            self::$alertas['error'][]='El nombre es obligatorio';
        }
        if(!$this->email){
            self::$alertas['error'][]='El email es obligatorio';
        }

        return self::$alertas;
    }

    public function nuevo_password() : array{
        if(!$this->password_actual){
            self::$alertas['error'][]='La contraseña actual no puede estar vacia';
        }
        if(!$this->password_nuevo){
            self::$alertas['error'][]='La contraseña nueva no puede estar vacia';
        }

        if(strlen($this->password_nuevo)<6){
            self::$alertas['error'][]='La contraseña debe tener al menos 6 caracteres';
        }

        return self::$alertas;
    }

    //Comprobar el password
    public function comprobarPassword(): bool{
        return password_verify($this->password_actual, $this->password);
    }

    //Función que hashea el password
    public function hashPassword(): void{
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    //Generar un token
    public function crearToken (): void{
        $this->token = uniqid();
    }
}