<?php
require 'config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: dashboard.php');
    exit;
}

$db = conectarDB();
$errores = [];
$page_title = 'Acceso a Kalua'; // Título para la página
$is_dashboard_page = false; // Aseguramos que no cargue el layout del dashboard

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- LÓGICA DE LOGIN ---
    if (isset($_POST['login'])) {
        $usuario_email = mysqli_real_escape_string($db, filter_var($_POST['usuario'], FILTER_VALIDATE_EMAIL));
        $password = mysqli_real_escape_string($db, $_POST['password']);

        if (!$usuario_email) $errores[] = "El email no es válido";
        if (!$password) $errores[] = "La contraseña es obligatoria";

        if (empty($errores)) {
            $query = "SELECT id, usuario, nombre, password, rol FROM usuarios WHERE usuario = '{$usuario_email}'";
            $resultado = mysqli_query($db, $query);

            if ($resultado->num_rows) {
                $usuarioData = mysqli_fetch_assoc($resultado);
                $auth = password_verify($password, $usuarioData['password']);

                if ($auth) {
                    // *** CORRECCIÓN AQUÍ ***
                    $_SESSION['login'] = true;
                    $_SESSION['user_id'] = $usuarioData['id']; // Corregido de 'id_usuario' a 'user_id'
                    $_SESSION['nombre_usuario'] = $usuarioData['nombre'] ?? $usuarioData['usuario']; // Usar nombre si existe, si no, el email
                    $_SESSION['rol'] = $usuarioData['rol'];
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $errores[] = 'La contraseña es incorrecta';
                }
            } else {
                $errores[] = "El usuario no existe";
            }
        }
    }
    // --- LÓGICA DE REGISTRO ---
    elseif (isset($_POST['register'])) {
        $nombre = mysqli_real_escape_string($db, trim($_POST['nombre']));
        $usuario_email = mysqli_real_escape_string($db, filter_var($_POST['usuario'], FILTER_VALIDATE_EMAIL));
        $password = mysqli_real_escape_string($db, $_POST['password']);
        $rol = mysqli_real_escape_string($db, $_POST['rol']);

        if (!$nombre) $errores[] = 'El nombre es obligatorio.';
        if (!$usuario_email) $errores[] = 'El email no es válido.';
        if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        if (!in_array($rol, ['admin', 'invitado', 'operador'])) $errores[] = 'Rol no válido.';

        if (empty($errores)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES ('{$nombre}', '{$usuario_email}', '{$passwordHash}', '{$rol}')";
            $resultado = mysqli_query($db, $query);

            if ($resultado) {
                header('Location: index.php?vista=login&resultado=1');
                exit;
            } else {
                $errores[] = 'Error al registrar el usuario. Es posible que el email ya exista.';
            }
        }
    }
}

// Incluir el header después de toda la lógica PHP
include 'includes/header.php';

$vista = $_GET['vista'] ?? 'login';
?>

<div class="auth-container">
    <div class="auth-form-wrapper">
        <div class="logo-container">
            <div class="logo-circle">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="<?php echo htmlspecialchars($site_name); ?>" class="logo-img">
                <?php else: ?>
                    <div class="logo-placeholder"><?php echo substr(htmlspecialchars($site_name), 0, 1); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($vista === 'login'): ?>
            <h2 class="auth-title">Iniciar Sesión</h2>
        <?php else: ?>
            <h2 class="auth-title">Crear Cuenta</h2>
        <?php endif; ?>

        <?php foreach ($errores as $error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endforeach; ?>

        <?php if(isset($_GET['resultado']) && $_GET['resultado'] == 1): ?>
            <div class="alert alert-success">Usuario registrado correctamente. ¡Ya puedes iniciar sesión!</div>
        <?php endif; ?>

        <?php if ($vista === 'login'): ?>
            <form action="index.php?vista=login" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="login-usuario">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="login-usuario" name="usuario" placeholder="tu@email.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="login-password">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="login-password" name="password" placeholder="Tu contraseña" required>
                    </div>
                </div>
                <button type="submit" name="login" class="btn-auth">Entrar</button>
            </form>
            <p class="auth-switch-link">¿No tienes una cuenta? <a href="index.php?vista=registro">Regístrate aquí</a></p>
        <?php else: // Vista de registro ?>
            <form action="index.php?vista=registro" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="reg-nombre">Nombre Completo</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="reg-nombre" name="nombre" placeholder="Tu nombre y apellido" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reg-usuario">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="reg-usuario" name="usuario" placeholder="tu@email.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reg-password">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="reg-password" name="password" placeholder="Mínimo 6 caracteres" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reg-rol">Rol de Usuario</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user-tag"></i>
                        <select id="reg-rol" name="rol" required>
                            <option value="" disabled selected>-- Seleccionar Rol --</option>
                            <option value="invitado">Invitado</option>
                            <?php
                            // Verificar si ya existe un admin
                            $query_admin = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin'";
                            $result_admin = mysqli_query($db, $query_admin);
                            $admin_count = mysqli_fetch_assoc($result_admin)['total'];
                            
                            // Verificar si ya existe un operador
                            $query_operador = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'operador'";
                            $result_operador = mysqli_query($db, $query_operador);
                            $operador_count = mysqli_fetch_assoc($result_operador)['total'];
                            
                            // Mostrar opción de admin solo si no existe uno
                            if ($admin_count == 0): ?>
                                <option value="admin">Administrador</option>
                            <?php endif; ?>
                            
                            <!-- Mostrar opción de operador solo si no existe uno -->
                            <?php if ($operador_count == 0): ?>
                                <option value="operador">Operador</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="register" class="btn-auth">Crear Cuenta</button>
            </form>
            <p class="auth-switch-link">¿Ya tienes una cuenta? <a href="index.php?vista=login">Inicia sesión aquí</a></p>
        <?php endif; ?>
    </div>
</div>

<style>
    /* === DISEÑO CLARO Y LEGIBLE === */
    
    /* Fondo principal claro */
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #FFFEF7 0%, #FFF8E1 50%, #FFFEF7 100%);
    }
    
    /* Contenedor del formulario con colores claros */
    .auth-form-wrapper {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(74, 44, 23, 0.1), 0 5px 15px rgba(74, 44, 23, 0.05);
        border: 1px solid rgba(74, 44, 23, 0.1);
        max-width: 400px;
        width: 100%;
        position: relative;
        z-index: 10;
        animation: slideInUp 0.8s ease-out, fadeIn 1s ease-out;
        transition: all 0.3s ease;
    }
    
    .auth-form-wrapper:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(74, 44, 23, 0.15), 0 8px 20px rgba(74, 44, 23, 0.08);
    }
    
    /* Logo con colores claros */
    .logo-container {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }
    
    .logo-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #F4E4BC, #E8D5A3);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(74, 44, 23, 0.2);
        position: relative;
        overflow: hidden;
        animation: gentlePulse 3s infinite, gentleFloat 4s ease-in-out infinite;
        transition: transform 0.3s ease;
    }
    
    .logo-circle:hover {
        transform: scale(1.05) rotate(5deg);
    }
    
    .logo-img {
        width: 70%;
        height: 70%;
        object-fit: contain;
        border-radius: 50%;
        background-color: white;
        padding: 8px;
        animation: gentleSpin 20s linear infinite;
    }
    
    .logo-placeholder {
        font-size: 2.5rem;
        font-weight: bold;
        color: #4A2C17;
        text-shadow: 1px 1px 3px rgba(74, 44, 23, 0.3);
    }
    
    /* Título con excelente contraste */
    .auth-title {
        text-align: center;
        margin-bottom: 30px;
        font-size: 2rem;
        font-weight: 600;
        color: #4A2C17;
        position: relative;
        animation: titleFadeIn 1s ease-out 0.3s both;
    }
    
    .auth-title::after {
        content: '';
        position: absolute;
        width: 50px;
        height: 3px;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(90deg, #D4AF37, #F4E4BC);
        border-radius: 2px;
        animation: lineGrow 0.8s ease-out 0.8s both;
    }
    
    /* Grupos de formulario */
    .form-group {
        margin-bottom: 25px;
        position: relative;
        animation: slideInLeft 0.6s ease-out both;
    }
    
    .form-group:nth-child(1) { animation-delay: 0.4s; }
    .form-group:nth-child(2) { animation-delay: 0.6s; }
    .form-group:nth-child(3) { animation-delay: 0.8s; }
    .form-group:nth-child(4) { animation-delay: 1s; }
    
    /* Etiquetas con excelente legibilidad */
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #4A2C17;
        font-size: 0.95rem;
    }
    
    /* Contenedor de input con icono */
    .input-with-icon {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .input-with-icon i {
        position: absolute;
        left: 15px;
        color: #8B6F47;
        font-size: 1.1rem;
        z-index: 2;
        transition: all 0.3s ease;
    }
    
    /* Campos de entrada con excelente contraste */
    .input-with-icon input,
    .input-with-icon select {
        width: 100%;
        padding: 15px 15px 15px 45px;
        border: 2px solid #E8D5A3;
        border-radius: 12px;
        background-color: #FFFFFF;
        color: #4A2C17;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(74, 44, 23, 0.05);
    }
    
    .input-with-icon input::placeholder,
    .input-with-icon select option {
        color: #8B6F47;
    }
    
    .input-with-icon input:focus,
    .input-with-icon select:focus {
        outline: none;
        border-color: #D4AF37;
        background-color: #FFFEF7;
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
        transform: translateY(-2px);
    }
    
    .input-with-icon input:focus + i,
    .input-with-icon select:focus + i {
        color: #D4AF37;
        transform: scale(1.1);
    }
    
    /* Botón con colores claros y buen contraste */
    .btn-auth {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #F4E4BC, #E8D5A3);
        color: #4A2C17;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        position: relative;
        overflow: hidden;
        animation: buttonFadeIn 0.6s ease-out 1.2s both;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .btn-auth:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        background: linear-gradient(135deg, #D4AF37, #F4E4BC);
        color: #4A2C17;
    }
    
    .btn-auth:active {
        transform: translateY(-1px);
        background: linear-gradient(135deg, #E8D5A3, #D4AF37);
        color: #4A2C17;
    }
    
    .btn-auth::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s;
    }
    
    .btn-auth:hover::before {
        left: 100%;
    }
    
    /* Enlaces de cambio de vista */
    .auth-switch-link {
        text-align: center;
        margin-top: 25px;
        color: #8B6F47;
        animation: fadeIn 0.6s ease-out 1.4s both;
    }
    
    .auth-switch-link a {
        color: #D4AF37;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .auth-switch-link a:hover {
        color: #4A2C17;
    }
    
    .auth-switch-link a::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: #4A2C17;
        transition: width 0.3s ease;
    }
    
    .auth-switch-link a:hover::after {
        width: 100%;
    }
    
    /* Alertas con colores claros */
    .alert {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        animation: slideInDown 0.5s ease-out;
    }
    
    .alert-danger {
        background-color: #FFF5F5;
        color: #C53030;
        border: 1px solid #FEB2B2;
    }
    
    .alert-success {
        background-color: #F0FFF4;
        color: #38A169;
        border: 1px solid #9AE6B4;
    }
    
    /* Partículas doradas flotantes */
    .particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;
    }
    
    .particle {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, #F4E4BC, #D4AF37);
        opacity: 0.6;
        animation: floatParticle linear infinite;
    }
    
    /* Círculos decorativos */
    .decorative-circle {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(244, 228, 188, 0.1), transparent 70%);
        animation: gentlePulse 8s infinite alternate;
        z-index: 1;
        pointer-events: none;
    }
    
    /* === ANIMACIONES SUAVES === */
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes gentlePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    @keyframes gentleFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    
    @keyframes gentleSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes titleFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes lineGrow {
        from { width: 0; }
        to { width: 50px; }
    }
    
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes buttonFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes floatParticle {
        0% {
            transform: translate(0, 100vh) rotate(0deg);
            opacity: 0;
        }
        10% {
            opacity: 1;
        }
        90% {
            opacity: 1;
        }
        100% {
            transform: translate(var(--move-x, 0), -100px) rotate(360deg);
            opacity: 0;
        }
    }
    
    /* === RESPONSIVE === */
    
    @media (max-width: 768px) {
        .auth-container {
            padding: 15px;
        }
        
        .auth-form-wrapper {
            padding: 30px 25px;
        }
        
        .auth-title {
            font-size: 1.8rem;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
        }
        
        .logo-placeholder {
            font-size: 2rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Crear partículas para el fondo
        const container = document.querySelector('.auth-container');
        const particlesContainer = document.createElement('div');
        particlesContainer.className = 'particles';
        container.prepend(particlesContainer);
        
        // Generar partículas
        for (let i = 0; i < 100; i++) {
            createParticle();
        }
        
        // Añadir círculos decorativos
        for (let i = 0; i < 5; i++) {
            createDecorativeCircle();
        }
        
        function createParticle() {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Tamaño aleatorio
            const size = Math.random() * 15 + 2;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            // Posición aleatoria
            const posX = Math.random() * 100;
            const posY = Math.random() * 100;
            particle.style.left = `${posX}%`;
            particle.style.top = `${posY}%`;
            
            // Opacidad aleatoria
            particle.style.opacity = Math.random() * 0.5 + 0.1;
            
            // Color aleatorio con brillo
            const hue = Math.random() * 60 + 320; // Tonos rosados y morados
            particle.style.backgroundColor = `hsla(${hue}, 100%, 75%, ${Math.random() * 0.5 + 0.2})`;
            particle.style.boxShadow = `0 0 ${Math.random() * 10 + 5}px hsla(${hue}, 100%, 75%, 0.3)`;
            
            // Animación aleatoria
            const duration = Math.random() * 30 + 10;
            particle.style.animationDuration = `${duration}s`;
            
            // Dirección aleatoria
            const directionX = Math.random() * 200 - 100;
            const directionY = Math.random() * 200 - 100;
            particle.style.setProperty('--move-x', `${directionX}px`);
            particle.style.setProperty('--move-y', `${directionY}px`);
            
            particlesContainer.appendChild(particle);
            
            // Recrear partícula cuando termine su animación
            setTimeout(() => {
                particle.remove();
                createParticle();
            }, duration * 1000);
        }
        
        function createDecorativeCircle() {
            const circle = document.createElement('div');
            circle.className = 'decorative-circle';
            
            // Tamaño aleatorio grande
            const size = Math.random() * 300 + 200;
            circle.style.width = `${size}px`;
            circle.style.height = `${size}px`;
            
            // Posición aleatoria
            const posX = Math.random() * 100;
            const posY = Math.random() * 100;
            circle.style.left = `${posX}%`;
            circle.style.top = `${posY}%`;
            
            // Animación aleatoria
            circle.style.animationDuration = `${Math.random() * 10 + 5}s`;
            
            container.appendChild(circle);
        }
        
        // Efecto de brillo al pasar el cursor sobre los botones
        const buttons = document.querySelectorAll('.btn-auth');
        buttons.forEach(button => {
            button.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                this.style.background = `radial-gradient(circle at ${x}px ${y}px, 
                    <?php echo $color_secundario ?? '#F55D93'; ?>, 
                    <?php echo $color_primario ?? '#6D3D2A'; ?> 70%)`;
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.background = `linear-gradient(45deg, 
                    <?php echo $color_primario ?? '#6D3D2A'; ?>, 
                    <?php echo $color_secundario ?? '#F55D93'; ?>)`;
            });
        });
        
        // Efecto de tipeo para los títulos
        const title = document.querySelector('.auth-title');
        if (title) {
            const text = title.textContent;
            title.textContent = '';
            
            let i = 0;
            const typeWriter = () => {
                if (i < text.length) {
                    title.textContent += text.charAt(i);
                    i++;
                    setTimeout(typeWriter, 100);
                }
            };
            
            setTimeout(typeWriter, 500);
        }
        
        // Función para convertir hex a objeto RGB
        function hexToRgbObj(hex) {
            hex = hex.replace('#', '');
            
            if (hex.length === 3) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }
            
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            
            return { r, g, b };
        }
        
        // Función para convertir RGB a HSL
        function rgbToHsl(r, g, b) {
            r /= 255;
            g /= 255;
            b /= 255;
            
            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;
            
            if (max === min) {
                h = s = 0; // acromático
            } else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                
                h /= 6;
            }
            
            return { h, s, l };
        }
    });
</script>

<?php 
// Función para convertir color hex a rgb
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "$r, $g, $b";
}

// Función para ajustar el brillo de un color
function adjustBrightness($hex, $steps) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

mysqli_close($db);
include 'includes/footer.php'; 
?>