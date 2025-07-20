<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee API Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fafafa;
        }
        .test-section h3 {
            color: #555;
            margin-bottom: 15px;
        }
        .button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .button.success {
            background-color: #28a745;
        }
        .button.danger {
            background-color: #dc3545;
        }
        .button.warning {
            background-color: #ffc107;
            color: #212529;
        }
        .results {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
        .loading {
            color: #007bff;
            font-style: italic;
        }
        .error {
            color: #dc3545;
        }
        .success {
            color: #28a745;
        }
        .search-box {
            width: 300px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Employee CRUD API Testing</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Testing fetch API implementation untuk Employee Management System
        </p>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalEmployees">-</div>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalLocations">-</div>
                <div class="stat-label">Locations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="apiCalls">0</div>
                <div class="stat-label">API Calls Made</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="responseTime">-</div>
                <div class="stat-label">Avg Response (ms)</div>
            </div>
        </div>

        <!-- Test 1: Load All Employees -->
        <div class="test-section">
            <h3>📋 Test 1: Load All Employees</h3>
            <p>Test loading employee data dengan pagination</p>
            <button class="button" onclick="testLoadEmployees()">Load Employees</button>
            <button class="button" onclick="testLoadEmployeesWithFilter()">Load with Filters</button>
            <div id="loadResults" class="results" style="display: none;"></div>
        </div>

        <!-- Test 2: Search Functionality -->
        <div class="test-section">
            <h3>🔍 Test 2: Search Employees</h3>
            <p>Test search autocomplete functionality</p>
            <input type="text" id="searchInput" class="search-box" placeholder="Search employees..." 
                   oninput="debounceSearch(this.value)">
            <button class="button" onclick="testSearch()">Search</button>
            <div id="searchResults" class="results" style="display: none;"></div>
        </div>

        <!-- Test 3: CRUD Operations -->
        <div class="test-section">
            <h3>✏️ Test 3: CRUD Operations (Simulation)</h3>
            <p>Test create, update, dan delete operations</p>
            <button class="button success" onclick="testCreateEmployee()">Simulate Create</button>
            <button class="button warning" onclick="testUpdateEmployee()">Simulate Update</button>
            <button class="button danger" onclick="testDeleteEmployee()">Simulate Delete</button>
            <div id="crudResults" class="results" style="display: none;"></div>
        </div>

        <!-- Test 4: Error Handling -->
        <div class="test-section">
            <h3>⚠️ Test 4: Error Handling</h3>
            <p>Test error handling untuk various scenarios</p>
            <button class="button danger" onclick="testErrorHandling()">Test 404 Error</button>
            <button class="button danger" onclick="testNetworkError()">Test Network Error</button>
            <div id="errorResults" class="results" style="display: none;"></div>
        </div>

        <!-- Test 5: Performance -->
        <div class="test-section">
            <h3>⚡ Test 5: Performance</h3>
            <p>Test response times dan concurrent requests</p>
            <button class="button" onclick="testPerformance()">Performance Test</button>
            <button class="button" onclick="testConcurrentRequests()">Concurrent Requests</button>
            <div id="performanceResults" class="results" style="display: none;"></div>
        </div>
    </div>

    <script type="module">
        // Import API helper
        const apiHelper = {
            async apiRequest(url, options = {}) {
                const defaultOptions = {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        ...options.headers
                    },
                    ...options
                }

                const startTime = performance.now()
                try {
                    const response = await fetch(url, defaultOptions)
                    const data = await response.json()
                    const endTime = performance.now()
                    
                    // Update stats
                    updateStats(endTime - startTime)

                    if (!response.ok) {
                        const error = new Error(data.message || `HTTP error! status: ${response.status}`)
                        error.response = response
                        error.data = data
                        error.status = response.status
                        throw error
                    }

                    return { data, response, responseTime: endTime - startTime }
                } catch (error) {
                    const endTime = performance.now()
                    updateStats(endTime - startTime)
                    throw error
                }
            },

            async getEmployees(filters = {}) {
                const params = new URLSearchParams(filters).toString()
                const url = params ? `/api-test/employees?${params}` : '/api-test/employees'
                return this.apiRequest(url)
            },

            async searchEmployees(query) {
                return this.apiRequest(`/api-test/employees-search?q=${encodeURIComponent(query)}`)
            }
        }

        // Global variables
        let apiCallCount = 0
        let totalResponseTime = 0
        let searchTimeout

        // Update stats
        function updateStats(responseTime) {
            apiCallCount++
            totalResponseTime += responseTime
            
            document.getElementById('apiCalls').textContent = apiCallCount
            document.getElementById('responseTime').textContent = 
                Math.round(totalResponseTime / apiCallCount) + 'ms'
        }

        // Test functions
        window.testLoadEmployees = async function() {
            const resultsDiv = document.getElementById('loadResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = '<div class="loading">Loading employees...</div>'

            try {
                const { data, responseTime } = await apiHelper.getEmployees()
                
                // Update stats
                document.getElementById('totalEmployees').textContent = data.data.total || 0
                if (data.props?.locations) {
                    document.getElementById('totalLocations').textContent = data.props.locations.length
                }

                resultsDiv.innerHTML = `
                    <div class="success">✅ Success! Loaded ${data.data.data.length} employees in ${Math.round(responseTime)}ms</div>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `
            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="error">❌ Error: ${error.message}</div>
                    <pre>${JSON.stringify(error, null, 2)}</pre>
                `
            }
        }

        window.testLoadEmployeesWithFilter = async function() {
            const resultsDiv = document.getElementById('loadResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = '<div class="loading">Loading employees with filters...</div>'

            try {
                const { data, responseTime } = await apiHelper.getEmployees({ 
                    status: 'active',
                    search: 'maya'
                })
                
                resultsDiv.innerHTML = `
                    <div class="success">✅ Success! Filtered results in ${Math.round(responseTime)}ms</div>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `
            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="error">❌ Error: ${error.message}</div>
                    <pre>${JSON.stringify(error, null, 2)}</pre>
                `
            }
        }

        window.testSearch = async function() {
            const query = document.getElementById('searchInput').value || 'maya'
            const resultsDiv = document.getElementById('searchResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = '<div class="loading">Searching...</div>'

            try {
                const { data, responseTime } = await apiHelper.searchEmployees(query)
                
                resultsDiv.innerHTML = `
                    <div class="success">✅ Search completed in ${Math.round(responseTime)}ms</div>
                    <div>Found ${data.data.length} employees matching "${query}"</div>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `
            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="error">❌ Search Error: ${error.message}</div>
                    <pre>${JSON.stringify(error, null, 2)}</pre>
                `
            }
        }

        window.debounceSearch = function(value) {
            clearTimeout(searchTimeout)
            searchTimeout = setTimeout(() => {
                if (value.length > 2) {
                    testSearch()
                }
            }, 300)
        }

        window.testCreateEmployee = function() {
            const resultsDiv = document.getElementById('crudResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = `
                <div class="success">✅ Create Employee Simulation</div>
                <div>Data yang akan dikirim:</div>
                <pre>${JSON.stringify({
                    employee_id: 'EMP999',
                    name: 'Test Employee',
                    email: 'test@company.com',
                    position: 'Test Position',
                    department: 'Test Department',
                    hire_date: new Date().toISOString().split('T')[0],
                    status: 'active'
                }, null, 2)}</pre>
                <div style="margin-top: 10px; color: #666;">
                    Note: Create/Update/Delete membutuhkan authentication. 
                    Di production, gunakan route yang memerlukan login.
                </div>
            `
        }

        window.testUpdateEmployee = function() {
            const resultsDiv = document.getElementById('crudResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = `
                <div class="success">✅ Update Employee Simulation</div>
                <div>Data yang akan diupdate:</div>
                <pre>${JSON.stringify({
                    id: 4,
                    name: 'Maya Sari Dewi (Updated)',
                    position: 'Senior HR Specialist',
                    department: 'Human Resources'
                }, null, 2)}</pre>
            `
        }

        window.testDeleteEmployee = function() {
            const resultsDiv = document.getElementById('crudResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = `
                <div class="success">✅ Delete Employee Simulation</div>
                <div>Employee yang akan dihapus: ID 8 (Fitri Handayani)</div>
                <pre>DELETE /employees/8</pre>
            `
        }

        window.testErrorHandling = async function() {
            const resultsDiv = document.getElementById('errorResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = '<div class="loading">Testing 404 error...</div>'

            try {
                await apiHelper.apiRequest('/api-test/non-existent-endpoint')
            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="success">✅ Error handling working correctly!</div>
                    <div class="error">Caught error: ${error.message}</div>
                    <pre>${JSON.stringify(error, null, 2)}</pre>
                `
            }
        }

        window.testNetworkError = function() {
            const resultsDiv = document.getElementById('errorResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = `
                <div class="success">✅ Network Error Simulation</div>
                <div>Network errors akan tertangkap oleh error handler.</div>
                <div>Contoh: fetch ke domain yang tidak ada, koneksi timeout, dll.</div>
            `
        }

        window.testPerformance = async function() {
            const resultsDiv = document.getElementById('performanceResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = '<div class="loading">Running performance test...</div>'

            const startTime = performance.now()
            const promises = []
            
            // Test multiple requests
            for (let i = 0; i < 5; i++) {
                promises.push(apiHelper.getEmployees())
            }

            try {
                const results = await Promise.all(promises)
                const endTime = performance.now()
                const totalTime = endTime - startTime
                const avgTime = totalTime / results.length

                resultsDiv.innerHTML = `
                    <div class="success">✅ Performance Test Completed</div>
                    <div>5 concurrent requests completed in ${Math.round(totalTime)}ms</div>
                    <div>Average response time: ${Math.round(avgTime)}ms</div>
                    <div>All requests successful: ${results.every(r => r.data.success)}</div>
                `
            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="error">❌ Performance Test Failed: ${error.message}</div>
                `
            }
        }

        window.testConcurrentRequests = async function() {
            const resultsDiv = document.getElementById('performanceResults')
            resultsDiv.style.display = 'block'
            resultsDiv.innerHTML = '<div class="loading">Testing concurrent requests...</div>'

            const startTime = performance.now()
            
            try {
                const [employeesResult, searchResult] = await Promise.all([
                    apiHelper.getEmployees(),
                    apiHelper.searchEmployees('maya')
                ])
                
                const endTime = performance.now()

                resultsDiv.innerHTML = `
                    <div class="success">✅ Concurrent Requests Test Completed</div>
                    <div>Both requests completed in ${Math.round(endTime - startTime)}ms</div>
                    <div>Employees loaded: ${employeesResult.data.data.data.length}</div>
                    <div>Search results: ${searchResult.data.data.length}</div>
                `
            } catch (error) {
                resultsDiv.innerHTML = `
                    <div class="error">❌ Concurrent Test Failed: ${error.message}</div>
                `
            }
        }

        // Auto-load employees on page load
        document.addEventListener('DOMContentLoaded', function() {
            testLoadEmployees()
        })
    </script>
</body>
</html>
