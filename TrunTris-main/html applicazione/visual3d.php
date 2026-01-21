<?php
session_start();

// Redirect to login page if user_id is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: Account.php");
    exit();
}

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

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ottimizzazione Bagagliaio 3D - <?php echo htmlspecialchars($nome_schema); ?></title>
    <style>
        body { 
            margin: 0; 
            overflow: hidden; 
            font-family: Arial, sans-serif;
        }
        #info {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255,255,255,0.9);
            padding: 10px;
            border-radius: 5px;
            max-width: 300px;
        }
        #container {
            width: 100%;
            height: 100vh;
        }
        #controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.9);
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        #form-container {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            padding: 15px;
            border-radius: 5px;
            width: 300px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        button {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #45a049;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        #save-btn {
            background: #2196F3;
        }
        #save-btn:hover {
            background: #0b7dda;
        }
        #home-btn {
            background: #ff4444;
            margin-left: 20px;
        }
        #home-btn:hover {
            background: #cc0000;
        }
    </style>
</head>
<body>
    <div id="info">
        <h2>Ottimizzazione Bagagliaio 3D - <?php echo htmlspecialchars($nome_schema); ?></h2>
        <p>Visualizzazione delle valigie impilate nel bagagliaio</p>
        <p>Trascina i cubi per riposizionarli manualmente</p>
    </div>
    
    <div id="form-container">
        <h3>Configurazione Bagagliaio</h3>
        <form id="packing-form">
            <input type="hidden" id="schema-id" value="<?php echo $id_schema; ?>">
            <input type="hidden" id="nome_schema" value="<?php echo htmlspecialchars($nome_schema); ?>">
            <input type="hidden" name="id_user" value="<?php echo $_SESSION['user_id']; ?>">
            <div class="form-group">
                <label for="trunk-size">Dimensioni Bagagliaio (LxPxA):</label>
                <input type="text" id="trunk-size" value="<?php echo htmlspecialchars($trunk_size); ?>" placeholder="es. 10x8x6">
            </div>
            <div class="form-group">
                <label for="suitcases">Valigie (L,P,A separate da virgola):</label>
                <textarea id="suitcases" rows="5" placeholder="es. 3,2,2, 4,3,1, 2,2,2"><?php echo htmlspecialchars($valigie); ?></textarea>
            </div>
            <div class="form-group">
                <label for="algorithm">Algoritmo di Packing:</label>
                <select id="algorithm">
                    <option value="simple">Semplice (First-Fit)</option>
                    <option value="max-volume">Massimo Volume Prima</option>
                    <option value="layered">A Strati</option>
                </select>
            </div>
            <button type="submit">Ottimizza</button>
            <button type="button" id="save-btn">Salva Configurazione</button>
        </form>
    </div>

    <div id="container"></div>
    <div id="controls">
        <p>Modalità movimento: <span id="mode-display">XY</span></p>
        <button id="toggle-mode">Cambia Asse (M)</button>
        <button id="reset-view">Reset Vista</button>
        <button id="home-btn" onclick="window.location.href='home.php'">Torna alla Home</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/DragControls.min.js"></script>

    <script>
        // Inizializzazione Three.js
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf0f0f0);
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.getElementById('container').appendChild(renderer.domElement);

        // Creazione dei controlli orbit di base
        const orbitControls = new THREE.OrbitControls(camera, renderer.domElement);
        orbitControls.enableDamping = true;
        orbitControls.dampingFactor = 0.05;

        // Illuminazione di base
        const ambientLight = new THREE.AmbientLight(0x404040);
        scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(5, 10, 7);
        scene.add(directionalLight);

        // Variabili globali
        let trunkMesh, cubes = [], dragControls;
        let currentMode = 'XY';
        const colors = [0xff0000, 0x00ff00, 0x0000ff, 0xffff00, 0xff00ff, 0x00ffff, 0xff8800, 0x8800ff];

        // Funzione per creare il bagagliaio 3D con proporzioni realistiche
        function createTrunk(length, width, height) {
        // Rimuovi il bagagliaio esistente se presente
        if (trunkMesh) {
            scene.remove(trunkMesh);
        }

        const group = new THREE.Group();
        group.userData = { length, width, height };
        
        // Base (senza griglia)
        const baseGeometry = new THREE.BoxGeometry(length, 0.2, width);
        const baseMaterial = new THREE.MeshPhongMaterial({ 
            color: 0x8B4513,
            transparent: true,
            opacity: 0.8
        });
        const base = new THREE.Mesh(baseGeometry, baseMaterial);
        base.position.y = -0.1;
        group.add(base);

        // Pareti semi-trasparenti (solo laterali e posteriore)
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

        scene.add(group);
        trunkMesh = group;

        // Aggiorna la vista della camera
        resetView();
        
        // Aggiorna le impostazioni di OrbitControls in base alle dimensioni del bagagliaio
        orbitControls.minDistance = Math.min(length, width, height) * 0.5;
        orbitControls.maxDistance = Math.max(length, width, height) * 3;
        orbitControls.maxPolarAngle = Math.PI * 0.8;
        orbitControls.update();
        
        return { length, width, height };
}

        // Funzione per creare una valigia con proporzioni realistiche
        function createSuitcase(length, width, height, color, position) {
            const geometry = new THREE.BoxGeometry(length, height, width);
            
            // Materiale con bordo evidenziato
            const edges = new THREE.EdgesGeometry(geometry);
            const lineMaterial = new THREE.LineBasicMaterial({ color: 0x000000, linewidth: 2 });
            const edgeLines = new THREE.LineSegments(edges, lineMaterial);
            
            const material = new THREE.MeshPhongMaterial({ 
                color: color,
                transparent: true,
                opacity: 0.85,
                shininess: 30
            });
            
            const cube = new THREE.Mesh(geometry, material);
            cube.add(edgeLines); // Aggiungi i bordi alla valigia
            
            // Posiziona la valigia
            if (position) {
                cube.position.set(
                    position.x - trunkMesh.userData.length/2 + length/2,
                    position.y + height/2,
                    position.z - trunkMesh.userData.width/2 + width/2
                );
            } else {
                // Posizione casuale se non specificata
                cube.position.set(
                    (Math.random() - 0.5) * trunkMesh.userData.length * 0.8,
                    (Math.random() - 0.5) * trunkMesh.userData.height * 0.8 + trunkMesh.userData.height/2,
                    (Math.random() - 0.5) * trunkMesh.userData.width * 0.8
                );
            }
            
            // Aggiungi etichetta con le dimensioni
            // Correzione: aggiunta backticks per il template literal
            const label = createLabel(`${length}x${width}x${height}`, cube);
            cube.userData = { length, width, height };
            
            scene.add(cube);
            return cube;
        }

        // Funzione per creare etichette più leggibili
        function createLabel(text, parent) {
            const canvas = document.createElement('canvas');
            canvas.width = 256;
            canvas.height = 128;
            const context = canvas.getContext('2d');
            
            // Sfondo semitrasparente
            context.fillStyle = 'rgba(255, 255, 255, 0.7)';
            context.fillRect(0, 0, canvas.width, canvas.height);
            
            // Testo
            context.font = 'Bold 14px Arial';
            context.fillStyle = '#000000';
            context.textAlign = 'center';
            context.fillText(text, canvas.width/2, canvas.height/2);
            
            // Aggiungi icona valigia
            context.font = '20px Arial';
            context.fillText('🧳', canvas.width/2, canvas.height/2 + 25);
            
            const texture = new THREE.CanvasTexture(canvas);
            const material = new THREE.SpriteMaterial({ 
                map: texture,
                transparent: true
            });
            const sprite = new THREE.Sprite(material);
            sprite.scale.set(1.5, 0.75, 1);
            sprite.position.set(0, parent.userData.height/2 + 0.3, 0);
            parent.add(sprite);
            return sprite;
        }

        // Algoritmi di packing migliorati
        const packingAlgorithms = {
            // Algoritmo semplice (First-Fit)
            simple: (trunkDim, suitcases) => {
                const packed = [];
                const positions = [];
                const trunk = { ...trunkDim, used: [] };
                
                for (const suitcase of suitcases) {
                    let placed = false;
                    
                    // Prova a posizionare la valigia nella prima posizione disponibile
                    for (let y = 0; y <= trunk.height - suitcase.height && !placed; y += 0.5) {
                        for (let x = 0; x <= trunk.length - suitcase.length && !placed; x += 0.5) {
                            for (let z = 0; z <= trunk.width - suitcase.width && !placed; z += 0.5) {
                                const newPlace = { 
                                    x, y, z, 
                                    length: suitcase.length, 
                                    width: suitcase.width, 
                                    height: suitcase.height 
                                };
                                
                                if (!isOverlapping(newPlace, trunk.used)) {
                                    trunk.used.push(newPlace);
                                    packed.push(suitcase);
                                    positions.push({ x, y, z });
                                    placed = true;
                                }
                            }
                        }
                    }
                    
                    if (!placed) {
                        console.log(`Valigia ${suitcase.length}x${suitcase.width}x${suitcase.height} non entra`);
                    }
                }
                
                return { packed, positions };
            },
            
            // Algoritmo che ordina per volume decrescente
            'max-volume': (trunkDim, suitcases) => {
                // Ordina le valigie per volume decrescente
                const sorted = [...suitcases].sort((a, b) => 
                    (b.length * b.width * b.height) - (a.length * a.width * a.height));
                
                return packingAlgorithms.simple(trunkDim, sorted);
            },
            
            // Algoritmo a strati migliorato
            layered: (trunkDim, suitcases) => {
                const packed = [];
                const positions = [];
                const trunk = { ...trunkDim, used: [] };
                let currentLayerHeight = 0;
                
                // Ordina le valigie per altezza decrescente
                const sorted = [...suitcases].sort((a, b) => b.height - a.height);
                
                for (const suitcase of sorted) {
                    if (currentLayerHeight + suitcase.height > trunk.height) {
                        console.log(`Valigia ${suitcase.length}x${suitcase.width}x${suitcase.height} non entra`);
                        continue;
                    }
                    
                    let placed = false;
                    
                    // Prova a posizionare nello strato corrente
                    for (let x = 0; x <= trunk.length - suitcase.length && !placed; x += 0.5) {
                        for (let z = 0; z <= trunk.width - suitcase.width && !placed; z += 0.5) {
                            const newPlace = { 
                                x, y: currentLayerHeight, z, 
                                length: suitcase.length, 
                                width: suitcase.width, 
                                height: suitcase.height 
                            };
                            
                            if (!isOverlapping(newPlace, trunk.used)) {
                                trunk.used.push(newPlace);
                                packed.push(suitcase);
                                positions.push({ x, y: currentLayerHeight, z });
                                placed = true;
                            }
                        }
                    }
                    
                    // Se non entra, crea un nuovo strato
                    if (!placed) {
                        currentLayerHeight = getNextLayerHeight(trunk.used, trunk.height);
                        if (currentLayerHeight + suitcase.height > trunk.height) {
                            console.log(`Valigia ${suitcase.length}x${suitcase.width}x${suitcase.height} non entra`);
                            continue;
                        }
                        
                        // Riprova a posizionare nel nuovo strato
                        for (let x = 0; x <= trunk.length - suitcase.length && !placed; x += 0.5) {
                            for (let z = 0; z <= trunk.width - suitcase.width && !placed; z += 0.5) {
                                const newPlace = { 
                                    x, y: currentLayerHeight, z, 
                                    length: suitcase.length, 
                                    width: suitcase.width, 
                                    height: suitcase.height 
                                };
                                
                                if (!isOverlapping(newPlace, trunk.used)) {
                                    trunk.used.push(newPlace);
                                    packed.push(suitcase);
                                    positions.push({ x, y: currentLayerHeight, z });
                                    placed = true;
                                }
                            }
                        }
                    }
                }
                
                return { packed, positions };
            }
        };

        // Trova l'altezza per il prossimo strato
        function getNextLayerHeight(usedPlaces, trunkHeight) {
            if (usedPlaces.length === 0) return 0;
            
            let maxHeight = 0;
            usedPlaces.forEach(place => {
                const top = place.y + place.height;
                if (top > maxHeight) {
                    maxHeight = top;
                }
            });
            
            return maxHeight;
        }

        // Funzione per verificare sovrapposizioni
        function isOverlapping(newPlace, usedPlaces) {
            for (const place of usedPlaces) {
                if (newPlace.x < place.x + place.length &&
                    newPlace.x + newPlace.length > place.x &&
                    newPlace.z < place.z + place.width &&
                    newPlace.z + newPlace.width > place.z &&
                    newPlace.y < place.y + place.height &&
                    newPlace.y + newPlace.height > place.y) {
                    return true;
                }
            }
            return false;
        }

        // Funzione per rimuovere tutte le valigie
        function clearSuitcases() {
            cubes.forEach(cube => {
                scene.remove(cube);
                // Rimuovi anche eventuali etichette
                if (cube.children) {
                    cube.children.forEach(child => scene.remove(child));
                }
            });
            cubes = [];
        }

        // Funzione per gestire il form
        function handlePackingForm(e) {
            e.preventDefault();
            
            // Leggi i valori dal form
            const trunkSize = document.getElementById('trunk-size').value.split('x').map(Number);
            const suitcasesInput = document.getElementById('suitcases').value.split(',').map(Number);
            const algorithm = document.getElementById('algorithm').value;
            
            // Verifica input
            if (trunkSize.length !== 3 || trunkSize.some(isNaN)) {
                alert('Inserisci dimensioni bagagliaio valide (es. 10x8x6)');
                return;
            }
            
            if (suitcasesInput.length % 3 !== 0 || suitcasesInput.some(isNaN)) {
                alert('Inserisci dimensioni valigie valide (gruppi di 3 numeri separati da virgola)');
                return;
            }
            
            // Crea array di valigie
            const suitcases = [];
            for (let i = 0; i < suitcasesInput.length; i += 3) {
                suitcases.push({
                    length: suitcasesInput[i],
                    width: suitcasesInput[i+1],
                    height: suitcasesInput[i+2],
                    color: colors[(i/3) % colors.length] // Assegna un colore in base all'indice
                });
            }
            
            // Crea il bagagliaio
            const trunkDim = createTrunk(trunkSize[0], trunkSize[1], trunkSize[2]);
            
            // Pulisci valigie esistenti
            clearSuitcases();
            
            // Esegui l'algoritmo di packing
            const { packed, positions } = packingAlgorithms[algorithm](trunkDim, suitcases);
            
            // Crea le valigie impilate
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
            
            // Aggiorna i controlli di trascinamento
            setupDragControls();
            
            // Aggiorna le informazioni
            document.getElementById('info').innerHTML = `
                <h2>Ottimizzazione Bagagliaio 3D - <?php echo htmlspecialchars($nome_schema); ?></h2>
                <p>Dimensioni bagagliaio: ${trunkSize.join('x')}</p>
                <p>Valigie inserite: ${packed.length}/${suitcases.length}</p>
                <p>Spazio utilizzato: ${calculateUsedVolume(packed, trunkDim)}%</p>
                <p>Algoritmo: ${document.getElementById('algorithm').options[document.getElementById('algorithm').selectedIndex].text}</p>
            `;
        }

        // Calcola lo spazio utilizzato
        function calculateUsedVolume(packed, trunkDim) {
            const trunkVolume = trunkDim.length * trunkDim.width * trunkDim.height;
            let packedVolume = 0;
            
            packed.forEach(suitcase => {
                packedVolume += suitcase.length * suitcase.width * suitcase.height;
            });
            
            return ((packedVolume / trunkVolume) * 100).toFixed(1);
        }

        // Configura i controlli di trascinamento
        function setupDragControls() {
            if (dragControls) {
                dragControls.deactivate();
            }
            
            dragControls = new THREE.DragControls(cubes, camera, renderer.domElement);
            
            dragControls.addEventListener('dragstart', function (event) {
                orbitControls.enabled = false;
                event.object.material.opacity = 0.6;
            });
            
            dragControls.addEventListener('dragend', function (event) {
                orbitControls.enabled = true;
                event.object.material.opacity = 0.85;
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
                    
                    // Mantieni la valigia dentro il bagagliaio con margini
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

        // Gestione cambio modalità movimento
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

        // Reset vista
        // Sostituisci la funzione resetView() esistente con questa versione
        function resetView() {
            if (trunkMesh) {
                // Posizione della camera: frontale e leggermente rialzata
                camera.position.set(
                    0,                                    // Centrata orizzontalmente
                    trunkMesh.userData.height * 1,     // Leggermente sopra il centro
                    trunkMesh.userData.width * 2         // Distanza frontale
                );
                
                // Punto di mira centrato nel bagagliaio
                camera.lookAt(
                    0,                                  // Centro orizzontale
                    trunkMesh.userData.height * 0.1,    // Leggermente sopra la base
                    0                                   // Centro della profondità
                );
                
                orbitControls.target.set(
                    0,
                    trunkMesh.userData.height * 0.3,
                    0
                );
                orbitControls.update();
            }
        }

        // Funzione per salvare la configurazione
        // Sostituisci la funzione saveConfiguration esistente con questa versione
        function saveConfiguration() {
            const schemaId = document.getElementById('schema-id').value;
            let nome_schema = document.getElementById('nome_schema').value;
            
            // If no name is set, prompt for one
            if (!nome_schema) {
                nome_schema = prompt("Inserisci un nome per questa configurazione:", "Configurazione " + new Date().toLocaleString());
                if (!nome_schema) return; // Annulla se l'utente non inserisce un nome
                // Update the hidden input with the new name
                document.getElementById('nome_schema').value = nome_schema;
            }
            
            // Raccogli tutti i dati necessari
            const dimensioni_bagagliaio = document.getElementById('trunk-size').value;
            
            // Raccogli i dati delle valigie e le loro posizioni
            const valigie_inserite = [];
            const posizioni = [];
            cubes.forEach(cube => {
                // Aggiungi le dimensioni della valigia
                valigie_inserite.push([
                    cube.userData.length,
                    cube.userData.width,
                    cube.userData.height
                ]);
                
                // Aggiungi la posizione della valigia
                posizioni.push([
                    cube.position.x + trunkMesh.userData.length/2 - cube.userData.length/2,
                    cube.position.y - cube.userData.height/2,
                    cube.position.z + trunkMesh.userData.width/2 - cube.userData.width/2
                ]);
            });

            // Crea l'oggetto dati da inviare
            const data = {
                nome_schema: nome_schema,
                dimensioni_bagagliaio: dimensioni_bagagliaio,
                user: document.querySelector('input[name="id_user"]').value,
                data: JSON.stringify({
                    valigie_inserite: valigie_inserite,
                    posizioni: posizioni
                })
            };

            // Crea un form nascosto per inviare i dati
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'ricevi.php';

            // Aggiungi i campi nascosti
            for (const [key, value] of Object.entries(data)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            // Invia il form
            document.body.appendChild(form);
            form.submit();
        }

        // Event listeners
        document.getElementById('packing-form').addEventListener('submit', handlePackingForm);
        document.getElementById('toggle-mode').addEventListener('click', toggleMoveMode);
        document.getElementById('reset-view').addEventListener('click', resetView);
        document.getElementById('save-btn').addEventListener('click', saveConfiguration);
        document.addEventListener('keydown', (event) => {
            if (event.key.toLowerCase() === 'm') {
                toggleMoveMode();
            }
        });

        // Animazione
        function animate() {
            requestAnimationFrame(animate);
            orbitControls.update();
            renderer.render(scene, camera);
        }

        // Gestione ridimensionamento finestra
        window.addEventListener('resize', function () {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Inizializzazione
        animate();
        
        // Crea un bagagliaio di default all'avvio con alcune valigie
        // Crea il bagagliaio con le dimensioni specificate
        const [trunkLength, trunkWidth, trunkHeight] = '<?php echo $trunk_size; ?>'.split('x').map(Number);
        createTrunk(trunkLength, trunkWidth, trunkHeight);

        // Inizializzazione dei dati dello schema
        const initialData = {
            valigie: <?php echo isset($valigie_array) ? json_encode($valigie_array) : '[]'; ?>,
            posizioni: <?php echo isset($posizioni_array) ? json_encode($posizioni_array) : '[]'; ?>
        };

        // Se ci sono dati iniziali, caricali invece di fare il submit del form
        if (initialData.valigie.length > 0) {
            initialData.valigie.forEach((valigia, index) => {
                const [length, width, height] = valigia.split(',').map(Number);
                const [x, y, z] = initialData.posizioni[index].split(',').map(Number);
                const cube = createSuitcase(
                    length, 
                    width, 
                    height, 
                    colors[index % colors.length],
                    { x, y, z }
                );
                cubes.push(cube);
            });
            setupDragControls();
        } else {
            // Se non ci sono dati iniziali, usa il form
            document.getElementById('packing-form').dispatchEvent(new Event('submit'));
        }
    </script>
</body>
</html>