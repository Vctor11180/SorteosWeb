<?php
/**
 * Página de Prueba para API de Sorteos
 * Sistema de Sorteos Web
 * 
 * Esta página permite probar los endpoints de api_sorteos.php
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simular sesión para pruebas (solo en desarrollo)
// En producción, esto debe venir de un login real
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Para pruebas, crear una sesión simulada
    $_SESSION['is_logged_in'] = true;
    $_SESSION['id_usuario'] = 1; // Cambiar por un ID de usuario real
    $_SESSION['usuario_rol'] = 'Cliente';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba API Sorteos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #111318;
            color: #fff;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #2463eb;
            margin-bottom: 30px;
            text-align: center;
        }
        .test-section {
            background: #282d39;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .test-section h2 {
            color: #9da6b9;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .endpoint {
            background: #1a1f28;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 3px solid #2463eb;
        }
        .endpoint-url {
            font-family: 'Courier New', monospace;
            color: #22c55e;
            margin-bottom: 10px;
            word-break: break-all;
        }
        button {
            background: #2463eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-top: 10px;
        }
        button:hover {
            background: #1d4ed8;
        }
        .response {
            background: #0d1117;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            max-height: 500px;
            overflow-y: auto;
        }
        .response pre {
            color: #9da6b9;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 12px;
        }
        .success {
            color: #22c55e;
        }
        .error {
            color: #ef4444;
        }
        .info {
            color: #3b82f6;
            font-size: 12px;
            margin-top: 5px;
        }
        input[type="text"], input[type="number"] {
            background: #0d1117;
            border: 1px solid #3b4254;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            width: 200px;
            margin-right: 10px;
        }
        label {
            display: inline-block;
            margin-right: 10px;
            color: #9da6b9;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-ok {
            background: #22c55e;
            color: white;
        }
        .status-error {
            background: #ef4444;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Prueba de API de Sorteos</h1>
        
        <!-- Test 1: Listar Sorteos Activos -->
        <div class="test-section">
            <h2>1. Listar Sorteos Activos</h2>
            <div class="endpoint">
                <div class="endpoint-url">GET api_sorteos.php?action=list_active</div>
                <div class="info">Obtiene todos los sorteos con estado 'Activo'</div>
                <button onclick="testListActive()">Probar Endpoint</button>
                <div id="response-list" class="response" style="display: none;"></div>
            </div>
            
            <div class="endpoint">
                <div class="endpoint-url">GET api_sorteos.php?action=list_active&search={texto}</div>
                <div class="info">Buscar sorteos por título o descripción</div>
                <label>Búsqueda:</label>
                <input type="text" id="search-input" placeholder="Ej: iPhone">
                <button onclick="testListActiveSearch()">Buscar</button>
                <div id="response-search" class="response" style="display: none;"></div>
            </div>
        </div>
        
        <!-- Test 2: Detalles de Sorteo -->
        <div class="test-section">
            <h2>2. Detalles de Sorteo</h2>
            <div class="endpoint">
                <div class="endpoint-url">GET api_sorteos.php?action=get_details&id={id_sorteo}</div>
                <div class="info">Obtiene información detallada de un sorteo específico</div>
                <label>ID Sorteo:</label>
                <input type="number" id="sorteo-id" placeholder="Ej: 1" min="1">
                <button onclick="testGetDetails()">Obtener Detalles</button>
                <div id="response-details" class="response" style="display: none;"></div>
            </div>
        </div>
        
        <!-- Test 3: Estadísticas de Sorteo -->
        <div class="test-section">
            <h2>3. Estadísticas de Sorteo</h2>
            <div class="endpoint">
                <div class="endpoint-url">GET api_sorteos.php?action=get_stats&id={id_sorteo}</div>
                <div class="info">Obtiene estadísticas de boletos (vendidos, reservados, disponibles)</div>
                <label>ID Sorteo:</label>
                <input type="number" id="stats-id" placeholder="Ej: 1" min="1">
                <button onclick="testGetStats()">Obtener Estadísticas</button>
                <div id="response-stats" class="response" style="display: none;"></div>
            </div>
        </div>
        
        <!-- Test 4: Probar todos -->
        <div class="test-section">
            <h2>4. Prueba Completa</h2>
            <div class="endpoint">
                <div class="info">Ejecuta todas las pruebas en secuencia</div>
                <button onclick="testAll()" style="background: #22c55e;">Ejecutar Todas las Pruebas</button>
                <div id="response-all" class="response" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = 'api_sorteos.php';
        
        function showResponse(elementId, data, isError = false) {
            const element = document.getElementById(elementId);
            element.style.display = 'block';
            
            if (isError) {
                element.innerHTML = `<pre class="error">${JSON.stringify(data, null, 2)}</pre>`;
            } else {
                element.innerHTML = `<pre class="success">${JSON.stringify(data, null, 2)}</pre>`;
            }
        }
        
        async function testListActive() {
            try {
                const response = await fetch(`${API_BASE}?action=list_active`);
                const text = await response.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    // Si no es JSON, mostrar el texto completo
                    showResponse('response-list', { 
                        error: 'La respuesta no es JSON válido',
                        raw_response: text,
                        status: response.status,
                        statusText: response.statusText
                    }, true);
                    return;
                }
                
                showResponse('response-list', {
                    status: response.status,
                    statusText: response.statusText,
                    data: data
                }, !data.success);
            } catch (error) {
                showResponse('response-list', { 
                    error: error.message,
                    stack: error.stack 
                }, true);
            }
        }
        
        async function testListActiveSearch() {
            const search = document.getElementById('search-input').value.trim();
            if (!search) {
                alert('Por favor ingresa un término de búsqueda');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}?action=list_active&search=${encodeURIComponent(search)}`);
                const text = await response.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    showResponse('response-search', { 
                        error: 'La respuesta no es JSON válido',
                        raw_response: text,
                        status: response.status
                    }, true);
                    return;
                }
                
                showResponse('response-search', {
                    status: response.status,
                    data: data
                }, !data.success);
            } catch (error) {
                showResponse('response-search', { 
                    error: error.message,
                    stack: error.stack 
                }, true);
            }
        }
        
        async function testGetDetails() {
            const id = document.getElementById('sorteo-id').value;
            if (!id || id < 1) {
                alert('Por favor ingresa un ID de sorteo válido');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}?action=get_details&id=${id}`);
                const text = await response.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    showResponse('response-details', { 
                        error: 'La respuesta no es JSON válido',
                        raw_response: text,
                        status: response.status
                    }, true);
                    return;
                }
                
                showResponse('response-details', {
                    status: response.status,
                    data: data
                }, !data.success);
            } catch (error) {
                showResponse('response-details', { 
                    error: error.message,
                    stack: error.stack 
                }, true);
            }
        }
        
        async function testGetStats() {
            const id = document.getElementById('stats-id').value;
            if (!id || id < 1) {
                alert('Por favor ingresa un ID de sorteo válido');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}?action=get_stats&id=${id}`);
                const text = await response.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    showResponse('response-stats', { 
                        error: 'La respuesta no es JSON válido',
                        raw_response: text,
                        status: response.status
                    }, true);
                    return;
                }
                
                showResponse('response-stats', {
                    status: response.status,
                    data: data
                }, !data.success);
            } catch (error) {
                showResponse('response-stats', { 
                    error: error.message,
                    stack: error.stack 
                }, true);
            }
        }
        
        async function testAll() {
            const allResponse = document.getElementById('response-all');
            allResponse.style.display = 'block';
            allResponse.innerHTML = '<pre class="info">Ejecutando pruebas...</pre>';
            
            const results = {
                timestamp: new Date().toISOString(),
                tests: []
            };
            
            // Test 1: List Active
            try {
                const response = await fetch(`${API_BASE}?action=list_active`);
                const text = await response.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                    results.tests.push({
                        name: 'Listar Sorteos Activos',
                        status: data.success ? 'OK' : 'ERROR',
                        status_code: response.status,
                        data: data
                    });
                } catch (e) {
                    results.tests.push({
                        name: 'Listar Sorteos Activos',
                        status: 'ERROR',
                        status_code: response.status,
                        error: 'No es JSON válido',
                        raw_response: text.substring(0, 500) // Primeros 500 caracteres
                    });
                }
            } catch (error) {
                results.tests.push({
                    name: 'Listar Sorteos Activos',
                    status: 'ERROR',
                    error: error.message,
                    stack: error.stack
                });
            }
            
            // Test 2: Get Details (si hay sorteos)
            if (results.tests[0] && results.tests[0].data && results.tests[0].data.data && results.tests[0].data.data.length > 0) {
                const firstSorteoId = results.tests[0].data.data[0].id_sorteo;
                try {
                    const response = await fetch(`${API_BASE}?action=get_details&id=${firstSorteoId}`);
                    const data = await response.json();
                    results.tests.push({
                        name: `Detalles de Sorteo #${firstSorteoId}`,
                        status: data.success ? 'OK' : 'ERROR',
                        data: data
                    });
                } catch (error) {
                    results.tests.push({
                        name: `Detalles de Sorteo #${firstSorteoId}`,
                        status: 'ERROR',
                        error: error.message
                    });
                }
                
                // Test 3: Get Stats
                try {
                    const response = await fetch(`${API_BASE}?action=get_stats&id=${firstSorteoId}`);
                    const data = await response.json();
                    results.tests.push({
                        name: `Estadísticas de Sorteo #${firstSorteoId}`,
                        status: data.success ? 'OK' : 'ERROR',
                        data: data
                    });
                } catch (error) {
                    results.tests.push({
                        name: `Estadísticas de Sorteo #${firstSorteoId}`,
                        status: 'ERROR',
                        error: error.message
                    });
                }
            }
            
            // Mostrar resultados
            const successCount = results.tests.filter(t => t.status === 'OK').length;
            const errorCount = results.tests.filter(t => t.status === 'ERROR').length;
            
            allResponse.innerHTML = `
                <pre class="${errorCount === 0 ? 'success' : 'error'}">
Resumen de Pruebas:
✅ Exitosas: ${successCount}
❌ Errores: ${errorCount}

${JSON.stringify(results, null, 2)}
                </pre>
            `;
        }
    </script>
</body>
</html>
