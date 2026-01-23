<?php
session_start();

// Redirect to login page if user_id is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: Account.php");
    exit();
}

// Imposta il titolo della pagina
$page_title = "Visualizzatore 3D";
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usa i dati inviati dal form
    $trunk_size = $_POST['trunk_size'];
    $valigie = $_POST['valigie'];
    $nome_schema = $_POST['nome_schema'];
    $id_schema = 0; // Schema temporaneo
} else if(isset($_GET['id'])) {
    // Connessione al database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "my_truntris";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $id_schema = $_GET['id'];
    $sql = "SELECT * FROM predefiniti WHERE ID = ? AND id_utente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_schema, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $schema = $result->fetch_assoc();
        $trunk_size = $schema['bagagliaglio'];
        $valigie = trim($schema['Valigie']); // Rimuovi spazi extra
        $posizioni = trim($schema['posizioni']); // Rimuovi spazi extra
        $nome_schema = $schema['nome'];
        $id_schema = $schema['ID'];

        // Converti le stringhe in array per JavaScript
        $valigie_array = array_map('trim', explode(' ', $valigie));
        $posizioni_array = array_map('trim', explode(' ', $posizioni));
    } else {
        // Schema non trovato, usa valori di default
        $trunk_size = "10x8x6";
        $valigie = "3,2,2,4,3,1,2,2,2,5,1,1,3,3,1";
        $nome_schema = "Nuovo schema";
        $id_schema = 0;
    }
    $conn->close();
} else {
    // Nessun dato, usa valori di default
    $trunk_size = "10x8x6";
    $valigie = "3,2,2,4,3,1,2,2,2,5,1,1,3,3,1";
    $nome_schema = "Nuovo schema";
    $id_schema = 0;
}
?>

<!-- Stili specifici per questa pagina -->
<style>
    /* Override di alcuni stili per la pagina 3D */
    main.container {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        height: calc(100vh - 70px) !important;
        overflow: hidden;
    }
    
    .visual3d-container {
        display: flex;
        height: 100%;
        width: 100%;
        position: relative;
        background: #f8fafc;
    }
    
    #three-container {
        flex: 1;
        height: 100%;
        position: relative;
    }
    
    /* Tooltip per le valigie */
    #valigia-tooltip {
        position: absolute;
        background: rgba(0, 0, 0, 0.85);
        color: white;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        pointer-events: none;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        min-width: 200px;
        display: none;
    }
    
    #valigia-tooltip.show {
        opacity: 1;
        display: block;
    }
    
    .tooltip-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .valigia-icon {
        font-size: 18px;
    }
    
    .valigia-id {
        font-weight: 700;
        font-size: 16px;
        color: #FF6B35;
    }
    
    .dimensione-row {
        display: flex;
        justify-content: space-between;
        margin: 4px 0;
        padding: 3px 0;
    }
    
    .dimensione-label {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .dimensione-value {
        font-weight: 600;
        color: white;
    }
    
    .volume {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        font-weight: 600;
        color: #4ECDC4;
    }
    
    /* Sidebar */
    .visual3d-sidebar {
        width: 380px;
        background: white;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
        z-index: 100;
        position: relative;
        overflow-y: auto;
        height: 100%;
    }
    
    .visual3d-sidebar.collapsed {
        transform: translateX(calc(100% - 50px));
    }
    
    .visual3d-sidebar-header {
        padding: 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--secondary-color);
        color: white;
    }
    
    .visual3d-sidebar-content {
        padding: 20px;
        flex: 1;
        overflow-y: auto;
    }
    
    .toggle-visual3d-sidebar {
        background: var(--primary-color);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .toggle-visual3d-sidebar:hover {
        background: #FF8C42;
        transform: scale(1.05);
    }
    
    /* Controlli fluttuanti */
    .floating-controls {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 15px;
        border-radius: 12px;
        display: flex;
        gap: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    /* Info fluttuanti */
    .floating-info {
        position: absolute;
        top: 20px;
        left: 20px;
        max-width: 300px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .visual3d-sidebar {
            position: absolute;
            right: 0;
            height: 100%;
        }
        
        .visual3d-sidebar.collapsed {
            transform: translateX(calc(100% - 40px));
        }
        
        .floating-controls {
            flex-wrap: wrap;
            justify-content: center;
            width: 90%;
        }
    }
</style>

<div id="valigia-tooltip">
    <div class="tooltip-header">
        <span class="valigia-icon">🧳</span>
        <span class="valigia-id">Valigia #<span id="tooltip-id">0</span></span>
    </div>
    <div class="dimensione-row">
        <span class="dimensione-label">Larghezza:</span>
        <span class="dimensione-value" id="tooltip-larghezza">0</span>
    </div>
    <div class="dimensione-row">
        <span class="dimensione-label">Altezza:</span>
        <span class="dimensione-value" id="tooltip-altezza">0</span>
    </div>
    <div class="dimensione-row">
        <span class="dimensione-label">Profondità:</span>
        <span class="dimensione-value" id="tooltip-profondita">0</span>
    </div>
    <div class="dimensione-row volume">
        <span class="dimensione-label">Volume:</span>
        <span class="dimensione-value" id="tooltip-volume">0</span>
    </div>
</div>

<div class="visual3d-container">
    <!-- Contenitore per Three.js -->
    <div id="three-container"></div>
    
    <!-- Sidebar laterale per configurazione -->
    <div class="visual3d-sidebar" id="sidebar">
        <div class="visual3d-sidebar-header">
            <div>
                <h3 style="margin: 0; font-size: 1.3rem;"><i class="fas fa-sliders-h"></i> Configurazione</h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 0.9rem;">Schema: <?php echo htmlspecialchars($nome_schema); ?></p>
            </div>
            <button class="toggle-visual3d-sidebar" id="toggle-sidebar">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <div class="visual3d-sidebar-content">
            <form id="packing-form">
                <input type="hidden" id="schema-id" value="<?php echo $id_schema; ?>">
                <input type="hidden" id="nome_schema" value="<?php echo htmlspecialchars($nome_schema); ?>">
                <input type="hidden" name="id_user" value="<?php echo $_SESSION['user_id']; ?>">
                
                <div class="form-group">
                    <label class="form-label">Dimensioni Bagagliaio (LxPxA):</label>
                    <input type="text" id="trunk-size" value="<?php echo htmlspecialchars($trunk_size); ?>" 
                           class="form-control" placeholder="es. 100x80x50" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Valigie (L,P,A separate da virgola):</label>
                    <textarea id="suitcases" rows="4" class="form-control" 
                              placeholder="es. 30,20,20, 40,30,10, 20,20,20" required><?php echo htmlspecialchars($valigie); ?></textarea>
                    <small style="color: #666; display: block; margin-top: 5px;">Inserisci gruppi di 3 numeri separati da virgola</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Algoritmo di Packing:</label>
                    <select id="algorithm" class="form-control">
                        <option value="simple">Semplice (First-Fit)</option>
                        <option value="max-volume">Massimo Volume Prima</option>
                        <option value="layered">A Strati</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-magic"></i> Ottimizza
                    </button>
                    <button type="button" id="save-btn" class="btn btn-success" style="flex: 1;">
                        <i class="fas fa-save"></i> Salva
                    </button>
                </div>
            </form>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <h4><i class="fas fa-info-circle"></i> Info Schema</h4>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-top: 10px;">
                    <p style="margin: 5px 0;"><i class="fas fa-ruler"></i> <strong>Bagagliaio:</strong> <?php echo htmlspecialchars($trunk_size); ?></p>
                    <p style="margin: 5px 0;"><i class="fas fa-suitcase"></i> <strong>Valigie:</strong> <?php echo count(explode(' ', trim($valigie))); ?></p>
                    <p style="margin: 5px 0;"><i class="fas fa-calendar"></i> <strong>ID Schema:</strong> <?php echo $id_schema; ?></p>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="home.php" class="btn btn-secondary" style="width: 100%;">
                    <i class="fas fa-arrow-left"></i> Torna alla Home
                </a>
            </div>
        </div>
    </div>
    
    <!-- Info fluttuanti in alto a sinistra -->
    <div class="floating-info">
        <h3 style="margin: 0 0 10px 0;"><i class="fas fa-cube"></i> Visualizzatore 3D</h3>
        <p style="margin: 0 0 10px 0; color: #666;">
            <i class="fas fa-mouse-pointer"></i> Passa sulle valigie per i dettagli<br>
            <i class="fas fa-arrows-alt"></i> Trascina per riposizionare<br>
            <i class="fas fa-expand-alt"></i> Ruota la vista con il mouse
        </p>
        <button id="toggle-mode" class="btn btn-outline" style="width: 100%; margin-top: 10px;">
            <i class="fas fa-arrows-alt"></i> Modalità: <span id="mode-display">XY</span>
        </button>
    </div>
    
    <!-- Controlli fluttuanti in basso -->
    <div class="floating-controls">
        <button id="reset-view" class="btn btn-primary">
            <i class="fas fa-sync-alt"></i> Reset Vista
        </button>
        <button id="toggle-sidebar-mobile" class="btn btn-outline">
            <i class="fas fa-cog"></i> Configura
        </button>
        <button onclick="window.location.href='home.php'" class="btn btn-danger">
            <i class="fas fa-home"></i> Home
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/DragControls.min.js"></script>

<script>
    // Inizializzazione Three.js
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf8fafc);
    
    // Ottieni le dimensioni corrette del container
    const container = document.getElementById('three-container');
    const width = container.clientWidth;
    const height = container.clientHeight;
    
    const camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(width, height);
    container.appendChild(renderer.domElement);

    // Controlli di orbita
    const orbitControls = new THREE.OrbitControls(camera, renderer.domElement);
    orbitControls.enableDamping = true;
    orbitControls.dampingFactor = 0.05;

    // Illuminazione
    const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
    scene.add(ambientLight);
    
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(5, 10, 7);
    scene.add(directionalLight);
    
    const hemisphereLight = new THREE.HemisphereLight(0x87CEEB, 0x006400, 0.3);
    scene.add(hemisphereLight);

    // Variabili globali
    let trunkMesh, cubes = [], dragControls;
    let currentMode = 'XY';
    const colors = [0xFF6B35, 0x1A3A5F, 0x4ECDC4, 0xFFD166, 0x06D6A0, 0x118AB2, 0xEF476F, 0x073B4C];
    let valigiaCounter = 0;
    
    // Variabili per gestire hover e drag
    let isDragging = false;
    let raycasterEnabled = true;
    let hoveredCube = null;
    
    // Elementi del tooltip
    const tooltip = document.getElementById('valigia-tooltip');
    const tooltipId = document.getElementById('tooltip-id');
    const tooltipLarghezza = document.getElementById('tooltip-larghezza');
    const tooltipAltezza = document.getElementById('tooltip-altezza');
    const tooltipProfondita = document.getElementById('tooltip-profondita');
    const tooltipVolume = document.getElementById('tooltip-volume');

    // Raycaster per rilevare il mouseover
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();

    // Toggle sidebar
    const sidebar = document.getElementById('sidebar');
    const toggleSidebarBtn = document.getElementById('toggle-sidebar');
    const toggleSidebarMobileBtn = document.getElementById('toggle-sidebar-mobile');
    
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        const icon = toggleSidebarBtn.querySelector('i');
        if (sidebar.classList.contains('collapsed')) {
            icon.className = 'fas fa-chevron-left';
        } else {
            icon.className = 'fas fa-chevron-right';
        }
        // Ridimensiona il renderer
        setTimeout(updateRendererSize, 300);
    }
    
    toggleSidebarBtn.addEventListener('click', toggleSidebar);
    toggleSidebarMobileBtn.addEventListener('click', toggleSidebar);

    // Funzione per aggiornare le dimensioni del renderer
    function updateRendererSize() {
        const container = document.getElementById('three-container');
        const width = container.clientWidth;
        const height = container.clientHeight;
        
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    }

    // Funzione per creare il bagagliaio 3D
    function createTrunk(length, width, height) {
        if (trunkMesh) {
            scene.remove(trunkMesh);
        }

        const group = new THREE.Group();
        group.userData = { length, width, height };
        
        // Base
        const baseGeometry = new THREE.BoxGeometry(length, 0.2, width);
        const baseMaterial = new THREE.MeshPhongMaterial({ 
            color: 0x8B4513,
            transparent: true,
            opacity: 0.8
        });
        const base = new THREE.Mesh(baseGeometry, baseMaterial);
        base.position.y = -0.1;
        group.add(base);

        // Pareti semi-trasparenti
        const wallMaterial = new THREE.MeshPhongMaterial({ 
            color: 0xA0522D, 
            transparent: true, 
            opacity: 0.5,
            side: THREE.DoubleSide
        });

        // Parete sinistra
        const leftWall = new THREE.Mesh(
            new THREE.BoxGeometry(0.2, height, width), wallMaterial
        );
        leftWall.position.set(-length/2, height/2, 0);
        group.add(leftWall);

        // Parete destra
        const rightWall = new THREE.Mesh(
            new THREE.BoxGeometry(0.2, height, width), wallMaterial
        );
        rightWall.position.set(length/2, height/2, 0);
        group.add(rightWall);

        // Parete posteriore
        const backWall = new THREE.Mesh(
            new THREE.BoxGeometry(length, height, 0.2), wallMaterial
        );
        backWall.position.set(0, height/2, -width/2);
        group.add(backWall);

        // Griglia sul fondo
        const gridHelper = new THREE.GridHelper(Math.max(length, width), 10, 0x000000, 0x000000);
        gridHelper.position.y = 0.1;
        gridHelper.material.opacity = 0.2;
        gridHelper.material.transparent = true;
        group.add(gridHelper);

        scene.add(group);
        trunkMesh = group;

        // Posiziona la camera
        resetView();
        
        orbitControls.minDistance = Math.min(length, width, height) * 0.5;
        orbitControls.maxDistance = Math.max(length, width, height) * 3;
        orbitControls.maxPolarAngle = Math.PI * 0.8;
        orbitControls.update();
        
        return { length, width, height };
    }

    // Funzione per creare una valigia con ID
    function createSuitcase(length, width, height, color, position) {
        valigiaCounter++;
        const geometry = new THREE.BoxGeometry(length, height, width);
        
        // Materiale principale
        const material = new THREE.MeshPhongMaterial({ 
            color: color,
            transparent: true,
            opacity: 0.85,
            shininess: 30,
            emissive: 0x000000,
            emissiveIntensity: 0
        });
        
        const cube = new THREE.Mesh(geometry, material);
        
        // Bordo evidenziato
        const edges = new THREE.EdgesGeometry(geometry);
        const lineMaterial = new THREE.LineBasicMaterial({ 
            color: 0x000000, 
            linewidth: 2 
        });
        const edgeLines = new THREE.LineSegments(edges, lineMaterial);
        cube.add(edgeLines);
        
        // Posiziona la valigia
        if (position) {
            cube.position.set(
                position.x - trunkMesh.userData.length/2 + length/2,
                position.y + height/2,
                position.z - trunkMesh.userData.width/2 + width/2
            );
        }
        
        // Salva i dati della valigia
        cube.userData = { 
            id: valigiaCounter,
            length: length, 
            width: width, 
            height: height,
            color: color,
            originalOpacity: 0.85,
            isBeingDragged: false
        };
        
        scene.add(cube);
        return cube;
    }

    // Funzione per mostrare il tooltip
    function showTooltip(cube, mouseX, mouseY) {
        if (!cube || isDragging) return;
        
        tooltipId.textContent = cube.userData.id;
        tooltipLarghezza.textContent = cube.userData.length.toFixed(1);
        tooltipAltezza.textContent = cube.userData.height.toFixed(1);
        tooltipProfondita.textContent = cube.userData.width.toFixed(1);
        
        const volume = cube.userData.length * cube.userData.width * cube.userData.height;
        tooltipVolume.textContent = volume.toFixed(1) + ' cm³';
        
        tooltip.style.left = (mouseX + 15) + 'px';
        tooltip.style.top = (mouseY - 15) + 'px';
        tooltip.classList.add('show');
        
        // Evidenzia la valigia
        cube.material.emissive.setHex(0x333333);
        cube.material.emissiveIntensity = 0.3;
        cube.material.opacity = 1;
    }

    // Funzione per nascondere il tooltip
    function hideTooltip(cube) {
        tooltip.classList.remove('show');
        
        if (cube) {
            cube.material.emissive.setHex(0x000000);
            cube.material.emissiveIntensity = 0;
            cube.material.opacity = cube.userData.originalOpacity;
        }
    }

    // Gestione del mouse move per il tooltip
    function onMouseMove(event) {
        if (isDragging || !raycasterEnabled) return;
        
        const rect = renderer.domElement.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(cubes);
        
        if (intersects.length > 0) {
            const intersect = intersects[0];
            const cube = intersect.object;
            
            if (cube.userData.isBeingDragged) {
                if (hoveredCube) {
                    hideTooltip(hoveredCube);
                    hoveredCube = null;
                }
                return;
            }
            
            if (hoveredCube !== cube) {
                if (hoveredCube) {
                    hideTooltip(hoveredCube);
                }
                hoveredCube = cube;
                showTooltip(cube, event.clientX, event.clientY);
            } else {
                tooltip.style.left = (event.clientX + 15) + 'px';
                tooltip.style.top = (event.clientY - 15) + 'px';
            }
        } else {
            if (hoveredCube) {
                hideTooltip(hoveredCube);
                hoveredCube = null;
            }
        }
    }

    function setupDragControls() {
        if (dragControls) {
            dragControls.deactivate();
        }
        
        dragControls = new THREE.DragControls(cubes, camera, renderer.domElement);
        
        dragControls.addEventListener('dragstart', function (event) {
            orbitControls.enabled = false;
            event.object.material.opacity = 0.6;
            event.object.userData.isBeingDragged = true;
            isDragging = true;
            raycasterEnabled = false;
            hideTooltip(event.object);
        });
        
        dragControls.addEventListener('dragend', function (event) {
            orbitControls.enabled = true;
            event.object.material.opacity = 0.85;
            event.object.userData.isBeingDragged = false;
            isDragging = false;
            setTimeout(() => {
                raycasterEnabled = true;
            }, 100);
        });
        
        dragControls.addEventListener('drag', function (event) {
            const object = event.object;
            const planeIntersect = dragControls.getPlaneIntersection(object, true);
            
            if (planeIntersect) {
                if (currentMode === 'XY') {
                    object.position.x = planeIntersect.x;
                    object.position.y = planeIntersect.y;
                } else if (currentMode === 'XZ') {
                    object.position.x = planeIntersect.x;
                    object.position.z = planeIntersect.z;
                } else if (currentMode === 'YZ') {
                    object.position.y = planeIntersect.y;
                    object.position.z = planeIntersect.z;
                }
                
                const halfLength = object.userData.length / 2;
                const halfWidth = object.userData.width / 2;
                const halfHeight = object.userData.height / 2;
                
                object.position.x = Math.max(
                    -trunkMesh.userData.length/2 + halfLength,
                    Math.min(
                        trunkMesh.userData.length/2 - halfLength,
                        object.position.x
                    )
                );
                
                object.position.y = Math.max(
                    halfHeight,
                    Math.min(
                        trunkMesh.userData.height - halfHeight,
                        object.position.y
                    )
                );
                
                object.position.z = Math.max(
                    -trunkMesh.userData.width/2 + halfWidth,
                    Math.min(
                        trunkMesh.userData.width/2 - halfWidth,
                        object.position.z
                    )
                );
            }
        });
    }

    function toggleMoveMode() {
        if (currentMode === 'XY') {
            currentMode = 'XZ';
        } else if (currentMode === 'XZ') {
            currentMode = 'YZ';
        } else {
            currentMode = 'XY';
        }
        document.getElementById('mode-display').textContent = currentMode;
    }

    function resetView() {
        if (trunkMesh) {
            const maxDim = Math.max(trunkMesh.userData.length, trunkMesh.userData.width, trunkMesh.userData.height);
            camera.position.set(maxDim * 1.5, maxDim * 1.2, maxDim * 1.5);
            camera.lookAt(0, trunkMesh.userData.height * 0.3, 0);
            orbitControls.target.set(0, trunkMesh.userData.height * 0.3, 0);
            orbitControls.update();
        }
    }

    function handlePackingForm(e) {
        e.preventDefault();
        
        const trunkSize = document.getElementById('trunk-size').value.split('x').map(Number);
        const suitcasesInput = document.getElementById('suitcases').value.split(',').map(Number);
        const algorithm = document.getElementById('algorithm').value;
        
        if (trunkSize.length !== 3 || trunkSize.some(isNaN)) {
            alert('Inserisci dimensioni bagagliaio valide (es. 100x80x50)');
            return;
        }
        
        if (suitcasesInput.length % 3 !== 0 || suitcasesInput.some(isNaN)) {
            alert('Inserisci dimensioni valigie valide (gruppi di 3 numeri separati da virgola)');
            return;
        }
        
        const suitcases = [];
        for (let i = 0; i < suitcasesInput.length; i += 3) {
            suitcases.push({
                length: suitcasesInput[i],
                width: suitcasesInput[i+1],
                height: suitcasesInput[i+2],
                color: colors[(i/3) % colors.length]
            });
        }
        
        const trunkDim = createTrunk(trunkSize[0], trunkSize[1], trunkSize[2]);
        
        // Pulisci valigie esistenti
        cubes.forEach(cube => {
            scene.remove(cube);
        });
        cubes = [];
        valigiaCounter = 0;
        isDragging = false;
        raycasterEnabled = true;
        hoveredCube = null;
        
        // Algoritmi di packing (versione semplificata)
        const packed = [];
        const positions = [];
        const trunk = { ...trunkDim, used: [] };
        
        for (const suitcase of suitcases) {
            let placed = false;
            
            for (let y = 0; y <= trunk.height - suitcase.height && !placed; y += 0.5) {
                for (let x = 0; x <= trunk.length - suitcase.length && !placed; x += 0.5) {
                    for (let z = 0; z <= trunk.width - suitcase.width && !placed; z += 0.5) {
                        const newPlace = { 
                            x, y, z, 
                            length: suitcase.length, 
                            width: suitcase.width, 
                            height: suitcase.height 
                        };
                        
                        // Controlla sovrapposizioni
                        let overlapping = false;
                        for (const place of trunk.used) {
                            if (newPlace.x < place.x + place.length &&
                                newPlace.x + newPlace.length > place.x &&
                                newPlace.z < place.z + place.width &&
                                newPlace.z + newPlace.width > place.z &&
                                newPlace.y < place.y + place.height &&
                                newPlace.y + newPlace.height > place.y) {
                                overlapping = true;
                                break;
                            }
                        }
                        
                        if (!overlapping) {
                            trunk.used.push(newPlace);
                            packed.push(suitcase);
                            positions.push({ x, y, z });
                            placed = true;
                            break;
                        }
                    }
                    if (placed) break;
                }
                if (placed) break;
            }
        }
        
        // Crea le valigie
        packed.forEach((suitcase, i) => {
            const cube = createSuitcase(
                suitcase.length, 
                suitcase.width, 
                suitcase.height, 
                suitcase.color || colors[i % colors.length],
                positions[i]
            );
            cubes.push(cube);
        });
        
        setupDragControls();
    }

    function saveConfiguration() {
        const schemaId = document.getElementById('schema-id').value;
        let nome_schema = document.getElementById('nome_schema').value;
        
        if (!nome_schema) {
            nome_schema = prompt("Inserisci un nome per questa configurazione:", "Configurazione " + new Date().toLocaleString());
            if (!nome_schema) return;
            document.getElementById('nome_schema').value = nome_schema;
        }
        
        const dimensioni_bagagliaio = document.getElementById('trunk-size').value;
        
        const valigie_inserite = [];
        const posizioni = [];
        cubes.forEach(cube => {
            valigie_inserite.push([
                cube.userData.length,
                cube.userData.width,
                cube.userData.height
            ]);
            
            posizioni.push([
                cube.position.x + trunkMesh.userData.length/2 - cube.userData.length/2,
                cube.position.y - cube.userData.height/2,
                cube.position.z + trunkMesh.userData.width/2 - cube.userData.width/2
            ]);
        });

        const data = {
            nome_schema: nome_schema,
            dimensioni_bagagliaio: dimensioni_bagagliaio,
            user: document.querySelector('input[name="id_user"]').value,
            data: JSON.stringify({
                valigie_inserite: valigie_inserite,
                posizioni: posizioni
            })
        };

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'ricevi.php';

        for (const [key, value] of Object.entries(data)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }

    // Event listeners
    document.getElementById('packing-form').addEventListener('submit', handlePackingForm);
    document.getElementById('toggle-mode').addEventListener('click', toggleMoveMode);
    document.getElementById('reset-view').addEventListener('click', resetView);
    document.getElementById('save-btn').addEventListener('click', saveConfiguration);
    
    // Event listener per il mouse move
    renderer.domElement.addEventListener('mousemove', onMouseMove);
    
    // Event listener per nascondere il tooltip quando il mouse esce
    renderer.domElement.addEventListener('mouseleave', function() {
        if (hoveredCube) {
            hideTooltip(hoveredCube);
            hoveredCube = null;
        }
        isDragging = false;
        raycasterEnabled = true;
    });

    // Reset dello stato dragging se il mouse viene rilasciato ovunque
    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            setTimeout(() => {
                raycasterEnabled = true;
            }, 50);
        }
    });

    // Animazione
    function animate() {
        requestAnimationFrame(animate);
        orbitControls.update();
        renderer.render(scene, camera);
    }

    // Gestione ridimensionamento finestra
    window.addEventListener('resize', updateRendererSize);

    // Inizializzazione
    animate();
    
    // Crea il bagagliaio iniziale
    const [trunkLength, trunkWidth, trunkHeight] = '<?php echo $trunk_size; ?>'.split('x').map(Number);
    if (trunkLength && trunkWidth && trunkHeight) {
        createTrunk(trunkLength, trunkWidth, trunkHeight);
    } else {
        createTrunk(100, 80, 50); // Dimensioni di default
    }

    // Inizializzazione dei dati dello schema
    const initialData = {
        valigie: <?php echo isset($valigie_array) ? json_encode($valigie_array) : '[]'; ?>,
        posizioni: <?php echo isset($posizioni_array) ? json_encode($posizioni_array) : '[]'; ?>
    };

    if (initialData.valigie.length > 0 && initialData.valigie[0] !== '') {
        initialData.valigie.forEach((valigia, index) => {
            const [length, width, height] = valigia.split(',').map(Number);
            const [x, y, z] = initialData.posizioni[index].split(',').map(Number);
            if (length && width && height) {
                const cube = createSuitcase(
                    length, 
                    width, 
                    height, 
                    colors[index % colors.length],
                    { x, y, z }
                );
                cubes.push(cube);
            }
        });
        if (cubes.length > 0) {
            setupDragControls();
        }
    } else {
        // Carica le valigie dal form
        setTimeout(() => {
            document.getElementById('packing-form').dispatchEvent(new Event('submit'));
        }, 500);
    }
</script>

<?php require_once 'includes/footer.php'; ?>
