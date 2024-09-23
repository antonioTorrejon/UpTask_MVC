<div class="contenedor reestablecer">
    <?php include_once __DIR__.'/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Coloca tu nueva contraseña</p>
        <?php include_once __DIR__.'/../templates/alertas.php'; ?>

        <?php if($mostrar) { ?>
            
        <form class="formulario" method="POST">
            <div class="campo">
                <label for="password">Contraseña</label>
                <input
                type="password"
                id="password"
                placeholder="Tu contraseña"
                name="password"
                />
            </div>

            <input type="submit" class="boton" value="Cuardar contraseña">
        </form>
        <?php } ?>

        <div class="acciones">
            <a href="/crear-cuenta">¿Aún no tienes una cuenta? Crea una</a>
            <a href="/olvide">¿Olvidaste tu constraseña? </a>
        </div>
    </div> <!-- cierre de .contenedor-sm -->
</div>