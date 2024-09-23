<div class="contenedor login">
    <?php include_once __DIR__.'/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Iniciar sesión</p>
        <?php include_once __DIR__.'/../templates/alertas.php'; ?>

        <form class="formulario" method="POST" action="/">
            <div class="campo">
                <label for="email">Email</label>
                <input
                type="email"
                id="email"
                placeholder="Tu email"
                name="email"
                />
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input
                type="password"
                id="password"
                placeholder="Tu contraseña"
                name="password"
                />
            </div>

            <input type="submit" class="boton" value="Iniciar sesión">
        </form>

        <div class="acciones">
            <a href="/crear-cuenta">¿Aún no tienes una cuenta? Crea una</a>
            <a href="/olvide">¿Olvidaste tu contraseña? </a>
        </div>
    </div> <!-- cierre de .contenedor-sm -->
</div>