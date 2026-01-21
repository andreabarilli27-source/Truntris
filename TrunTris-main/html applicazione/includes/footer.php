    </main>
    
    <footer style="background: var(--secondary-color); color: white; padding: 40px 0; margin-top: 60px;">
        <div class="container" style="text-align: center;">
            <div style="margin-bottom: 20px;">
                <img src="img/Logo.png" alt="Logo TrunTris" style="width: 60px; height: auto; margin-bottom: 15px;">
                <h3 style="color: white; margin-bottom: 10px;">TrunTris</h3>
                <p style="color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto;">
                    Sistema di ottimizzazione 3D per l'impilamento di bagagli nel bagagliaio dell'auto.
                </p>
            </div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                    &copy; <?php echo date('Y'); ?> TrunTris. Tutti i diritti riservati.
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Funzioni JavaScript comuni
        function confirmDelete(message = "Sei sicuro di voler eliminare questo elemento?") {
            return confirm(message);
        }
        
        // Toast notifications
        function showToast(message, type = 'success') {
            // Implementa se necessario
            console.log(message);
        }
    </script>
</body>
</html>