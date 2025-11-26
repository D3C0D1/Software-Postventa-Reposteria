<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
    <!-- Script para las notificaciones -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationModal = document.getElementById('notificationModal');
            const notificationList = document.getElementById('notificationList');
            const notificationCount = document.getElementById('notificationCount');
            const closeNotificationModal = document.getElementById('closeNotificationModal');
            
            if (notificationBell) {
                // Cargar notificaciones al cargar la página
                loadNotifications();
                
                // Configurar intervalo para actualizar notificaciones cada 5 minutos
                setInterval(loadNotifications, 300000);
                
                // Mostrar el modal al hacer clic en la campana
                notificationBell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationModal.style.display = 'flex';
                });
                
                // Cerrar el modal al hacer clic en el botón de cerrar
                closeNotificationModal.addEventListener('click', function() {
                    notificationModal.style.display = 'none';
                });
                
                // Cerrar el modal al hacer clic fuera del contenido
                notificationModal.addEventListener('click', function(e) {
                    if (e.target === notificationModal) {
                        notificationModal.style.display = 'none';
                    }
                });
            }
            
            // Función para cargar notificaciones
            function loadNotifications() {
                if (!notificationList) return;
                
                notificationList.innerHTML = '<div class="notification-loading">Cargando...</div>';
                
                fetch('ajax/get_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        const notificaciones = data.notificaciones;
                        const total = data.total;
                        
                        // Actualizar contador
                        notificationCount.textContent = total;
                        notificationCount.style.display = total > 0 ? 'flex' : 'none';
                        
                        // Actualizar lista de notificaciones
                        if (notificaciones.length === 0) {
                            notificationList.innerHTML = '<div class="notification-empty">No hay notificaciones</div>';
                        } else {
                            notificationList.innerHTML = '';
                            notificaciones.forEach(notificacion => {
                                const item = document.createElement('div');
                                item.className = 'notification-item';
                                item.innerHTML = `<p>${notificacion.mensaje}</p>`;
                                
                                // Agregar evento de clic para redirigir
                                item.addEventListener('click', function() {
                                    window.location.href = notificacion.url;
                                });
                                
                                notificationList.appendChild(item);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar notificaciones:', error);
                        notificationList.innerHTML = '<div class="notification-empty">Error al cargar notificaciones</div>';
                    });
            }
        });
    </script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
    <!-- Script para las notificaciones -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationModal = document.getElementById('notificationModal');
            const notificationList = document.getElementById('notificationList');
            const notificationCount = document.getElementById('notificationCount');
            const closeNotificationModal = document.getElementById('closeNotificationModal');
            
            if (notificationBell) {
                // Cargar notificaciones al cargar la página
                loadNotifications();
                
                // Configurar intervalo para actualizar notificaciones cada 5 minutos
                setInterval(loadNotifications, 300000);
                
                // Mostrar el modal al hacer clic en la campana
                notificationBell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationModal.style.display = 'flex';
                });
                
                // Cerrar el modal al hacer clic en el botón de cerrar
                closeNotificationModal.addEventListener('click', function() {
                    notificationModal.style.display = 'none';
                });
                
                // Cerrar el modal al hacer clic fuera del contenido
                notificationModal.addEventListener('click', function(e) {
                    if (e.target === notificationModal) {
                        notificationModal.style.display = 'none';
                    }
                });
            }
            
            // Función para cargar notificaciones
            function loadNotifications() {
                if (!notificationList) return;
                
                notificationList.innerHTML = '<div class="notification-loading">Cargando...</div>';
                
                fetch('ajax/get_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        const notificaciones = data.notificaciones;
                        const total = data.total;
                        
                        // Actualizar contador
                        notificationCount.textContent = total;
                        notificationCount.style.display = total > 0 ? 'flex' : 'none';
                        
                        // Actualizar lista de notificaciones
                        if (notificaciones.length === 0) {
                            notificationList.innerHTML = '<div class="notification-empty">No hay notificaciones</div>';
                        } else {
                            notificationList.innerHTML = '';
                            notificaciones.forEach(notificacion => {
                                const item = document.createElement('div');
                                item.className = 'notification-item';
                                item.innerHTML = `<p>${notificacion.mensaje}</p>`;
                                
                                // Agregar evento de clic para redirigir
                                item.addEventListener('click', function() {
                                    window.location.href = notificacion.url;
                                });
                                
                                notificationList.appendChild(item);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar notificaciones:', error);
                        notificationList.innerHTML = '<div class="notification-empty">Error al cargar notificaciones</div>';
                    });
            }
        });
    </script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
// Comprobar si estamos en una página del dashboard para cerrar las etiquetas correctas
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;
?>

        </main> <!-- Cierra el .main-content o <main> genérico -->

        <?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
                </div> <!-- Cierra .main-wrapper -->
            </div> <!-- Cierra .dashboard-layout -->
        <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/main.js"></script>
</body>
</html>
<?php
