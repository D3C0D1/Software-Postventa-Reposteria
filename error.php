<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Servicio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    // Obtener la configuración del sitio para usar los mismos colores del menú
    require_once 'config/database.php';
    
    // Valores por defecto en caso de error de conexión
    $color_primario = '#6b4f3e';
    $color_secundario = '#f5e6d8';
    $color_texto = '#4a3728';
    $site_name = 'App';
    
    // Intentar obtener los colores de la base de datos
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$conn->connect_error) {
            $result = $conn->query("SELECT nombre_empresa, color_primario, color_secundario, color_texto FROM configuracion WHERE id = 1");
            if ($result && $result->num_rows > 0) {
                $config = $result->fetch_assoc();
                $site_name = $config['nombre_empresa'];
                $color_primario = $config['color_primario'];
                $color_secundario = $config['color_secundario'];
                $color_texto = $config['color_texto'];
            }
            $conn->close();
        }
    } catch (Exception $e) {
        // Usar los valores por defecto si hay error
    }
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        
        :root {
            --primary-color: <?php echo $color_primario; ?>;
            --secondary-color: <?php echo $color_secundario; ?>;
            --text-color: <?php echo $color_texto; ?>;
            --light-brown: #f5e6d8;
            --medium-brown: #e8d5c4;
            --dark-brown: #6b4f3e;
            --accent-color: #d9c0a9;
            --whatsapp-color: #25D366;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--light-brown), var(--medium-brown));
            color: var(--dark-brown);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            position: relative;
            z-index: 10;
            animation: fadeIn 1s ease-out;
            border: 1px solid var(--accent-color);
        }
        
        h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
            animation: pulse 2s infinite;
        }
        
        p {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: slideUp 0.8s ease-out;
            color: var(--text-color);
        }
        
        .contact {
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 2rem 0;
            animation: highlight 3s infinite;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .contact-text {
            font-size: 1.5rem;
            color: var(--dark-brown);
            margin-bottom: 10px;
            animation: bounce 2s infinite;
        }
        
        .contact a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            padding: 15px 25px;
            background-color: var(--medium-brown);
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .contact a:before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: 0.5s;
        }
        
        .contact a:hover:before {
            left: 100%;
        }
        
        .contact a:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            background-color: var(--accent-color);
        }
        
        .icon-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .icon {
            font-size: 4rem;
            color: var(--primary-color);
            transition: all 0.3s ease;
            animation: float 3s infinite;
        }
        
        .icon:nth-child(2) {
            animation-delay: 0.5s;
        }
        
        .icon:nth-child(3) {
            animation-delay: 1s;
        }
        
        .icon:hover {
            transform: scale(1.2) rotate(10deg);
            color: var(--accent-color);
        }
        
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            animation: moveParticle 15s infinite linear;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes slideUp {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
        
        @keyframes highlight {
            0%, 100% {
                color: var(--primary-color);
                text-shadow: 0 0 10px rgba(255, 255, 255, 0.7);
            }
            50% {
                color: var(--dark-brown);
                text-shadow: 0 0 15px rgba(255, 255, 255, 0.9);
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }
        
        @keyframes moveParticle {
            0% {
                transform: translate(0, 0);
            }
            25% {
                transform: translate(100%, 100%);
            }
            50% {
                transform: translate(100%, -100%);
            }
            75% {
                transform: translate(-100%, -100%);
            }
            100% {
                transform: translate(0, 0);
            }
        }
        
        .shake {
            animation: shake 0.5s ease-in-out infinite;
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-5px);
            }
            75% {
                transform: translateX(5px);
            }
        }
        
        /* Estilo para el botón de WhatsApp */
        .whatsapp-btn {
            background-color: var(--whatsapp-color);
            color: white;
            border-radius: 50%;
            width: 80px; /* Más grande */
            height: 80px; /* Más grande */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem; /* Más grande */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            animation: whatsappPulse 2s infinite;
            transition: all 0.3s ease;
            margin: 15px auto;
        }
        
        .whatsapp-btn:hover {
            transform: scale(1.15) rotate(10deg);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        @keyframes whatsappPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
                transform: scale(1);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
                transform: scale(1.1);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
                transform: scale(1);
            }
        }
        
        .site-name {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 1rem;
            animation: textPulse 1.5s infinite;
        }
        
        @keyframes textPulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        /* Nueva animación de rebote */
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        /* Animación de flecha */
        .arrow-down {
            font-size: 2rem;
            color: var(--primary-color);
            animation: arrowBounce 2s infinite;
            margin-top: 10px;
        }
        
        @keyframes arrowBounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(10px);
            }
            60% {
                transform: translateY(5px);
            }
        }
        
        /* Animación de rotación */
        .rotate {
            animation: rotate 10s linear infinite;
            display: inline-block;
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="site-name"><?php echo htmlspecialchars($site_name); ?> <span class="rotate"><i class="fas fa-cog"></i></span></div>
        <h1><i class="fas fa-exclamation-triangle shake"></i> Error de Servicio</h1>
        
        <p>No se pudieron iniciar los servicios de Apache o MySQL correctamente.</p>
        
        <div class="icon-container">
            <i class="fas fa-server icon"></i>
            <i class="fas fa-database icon"></i>
            <i class="fas fa-cogs icon"></i>
        </div>
        
        <p>Por favor comunícate con el desarrollador para solucionar este problema:</p>
        
        <div class="contact-text">
            <i class="fas fa-hand-point-down"></i> Presiona aquí para comunicarte con el desarrollador <i class="fas fa-hand-point-down"></i>
        </div>
        
        <div class="arrow-down">
            <i class="fas fa-chevron-down"></i>
        </div>
        
        <div class="contact">
            <a href="https://wa.me/573184483187" target="_blank">
                <i class="fas fa-headset"></i> +57 3184483187
            </a>
            
            <a href="https://wa.me/573184483187" target="_blank" class="whatsapp-btn">
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>
    </div>

    <script>
        // Crear partículas animadas en el fondo
        const particlesContainer = document.getElementById('particles');
        const particleCount = 50; // Más partículas
        
        for (let i = 0; i < particleCount; i++) {
            const size = Math.random() * 15 + 5; // Partículas más grandes
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            particle.style.opacity = Math.random() * 0.5 + 0.3;
            particle.style.animationDuration = `${Math.random() * 20 + 10}s`;
            particle.style.animationDelay = `${Math.random() * 5}s`;
            
            // Colores que coinciden con el tema del menú
            const colors = ['#e8d5c4', '#d9c0a9', '#6b4f3e', '#8c6e5a', '#4a3728'];
            particle.style.background = colors[Math.floor(Math.random() * colors.length)];
            
            particlesContainer.appendChild(particle);
        }
        
        // Añadir efecto de brillo al pasar el cursor
        document.querySelectorAll('.contact a').forEach(link => {
            link.addEventListener('mousemove', function(e) {
                const x = e.pageX - this.offsetLeft;
                const y = e.pageY - this.offsetTop;
                
                this.style.setProperty('--x', x + 'px');
                this.style.setProperty('--y', y + 'px');
            });
        });
    </script>
</body>
</html>